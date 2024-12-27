<?php

namespace spiralCommunity\App\Http\Controllers\Auth;


use framework\Http\Controller;
use framework\Http\Request;
use framework\Http\Session\Session;
use framework\Routing\Router;
use framework\Support\ServiceProvider;
use SiLibrary\SiDateTime;
use SiLibrary\SpiralConnecter\SpiralWeb;
use spiralCommunity\App\Services\OAuthService;

class EntraController extends Controller
{
    protected $oauthService;

    public function __construct(Request $request, ?ServiceProvider $serviceProvider)
    {
        $this->oauthService = new OAuthService(config('entrada'));
        parent::__construct($request, $serviceProvider);
    }

    public function index(){
        echo view('html/login');
    }

    // Entra IDへのリダイレクト
    public function redirectToProvider()
    {
        $state = csrf_token();
        Session::put('oauth_state', $state);

        $authorizationUrl = $this->oauthService->getAuthorizationUrl($state);

        return Router::urlRedirect($authorizationUrl);
    }

    // コールバックの処理
    public function handleProviderCallback()
    {
        // stateの検証
        $state = $this->request->get('state');
        if ($state !== Session::get('oauth_state')) {
            throw new \Exception('Invalid state' , 403);
        }

        $code = $this->request->get('code');

        if (!$code) {
            throw new \Exception('Authorization code not found', 403);
        }

        try {
            $responseData = $this->oauthService->getAccessToken($code);
            if (isset($responseData['access_token'])) {
                $accessToken = $responseData['access_token'];
                $refreshToken = $responseData['refresh_token'];
                $tokenExpiresAt = SiDateTime::now()->addSecond($responseData['expires_in']);
            } else {
                throw new \Exception('Access token not found in response.');
            }
            $sub = $this->oauthService->getSubFromIdToken($accessToken);
            $userInfo = $this->oauthService->getUserInfo($accessToken);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }

        $crypt   = spiral()->getSpiralCryptOpenSsl();

        if(! in_array((new OAuthService(config('entrada')))->getTidFromIdToken($accessToken), config('entrada.arrow_tenant_ids'),true) ){
            throw new \Exception('許可されていないテナントです', 500);
        }

        // ユーザーの登録またはログイン
        \SpiralDB::title('users')->upsert(
            'provider_id',
            [
                'email' => $userInfo['mail'],
                'provider' => 'entra',
                'provider_id' => $sub ?? null,
                'access_token' => $crypt->encrypt($accessToken, config('crypt.key')),
                'refresh_token' => $crypt->encrypt($refreshToken, config('crypt.key')),
                'token_expires_at' => $tokenExpiresAt->format('Y-m-d H:i:s'),
            ]
        );

        // ログインAPIを呼び出す
        try {
            $redirectUrl = $this->callLoginApi($sub);
        } catch (\Exception $e) {
            // エラーハンドリング（例：ログを記録し、ユーザーにエラーメッセージを表示）
            //\Log::error('Login API 呼び出しエラー: ' . $e->getMessage());
            //session 'error', 'ログインAPIの呼び出しに失敗しました。
            Router::urlRedirect(config('login.url'));
            exit;
        }

        // 取得したURLにリダイレクト
        Router::urlRedirect($redirectUrl);
    }

    /**
     * ログインAPIを呼び出し、リダイレクトURLを取得する
     *
     * @param string $loginId
     * @return string
     * @throws \Exception
     */
    protected function callLoginApi($loginId)
    {
        $res = SpiralWeb::auth(config('login.title'))->login($loginId);
        setcookie("JSESSIONID", $res['jsessionid'], time() + 3600, '/area', '', true, true);
        /*
        $client = new \HttpRequest();
        $client->setUrl(config('services.login_api.url'));
        $client->setHeader([
            'Authorization' => 'Bearer ' . config('services.login_api.key'), // APIキーが必要な場合
            'Accept' => 'application/json',
        ]);
        $param = new \HttpRequestParameter();
        $param->set('user_id', $userId);

        $data = $client->post($param);

        if (!isset($data['url'])) {
            throw new \Exception('ログインAPIのレスポンスにURLが含まれていません。');
        }

        return $data['url'];
        */
        //SPIRAL のログインAPI
        return $res['url'];
    }
}