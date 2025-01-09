<?php
// app/Services/OAuthService.php
namespace spiralCommunity\App\Services;

use SiLibrary\SiDateTime;

class OAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $authUrl;
    protected $tokenUrl;
    protected $userInfoUrl;
    protected $userImageUrl;
    protected $scopes;
    protected $tenantId;

    public function __construct($config)
    {
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect'];
        $this->authUrl = $config['auth_url'];
        $this->tokenUrl = $config['token_url'];
        $this->userInfoUrl = $config['user_info_url'];
        $this->userImageUrl = $config['user_image_url'];
        $this->scopes = $config['scopes'];
        $this->tenantId = $config['tenant_id'] ?? null;
    }

    public function authUser() {
        $auth = \SpiralDB::title('users')->changeLabelFields(['permission'])->value(
            [   'id',
                'email',
                'provider',
                'provider_id',
                'access_token',
                'refresh_token',
                'token_expires_at',
                'display_name',
                'userImage',
                'token_expires_at',
                'office_location',
                'permission'
            ])->find(spiral()->getContextByFieldTitle('id'));

        if(!$auth) { return $auth; }

        $crypt   = spiral()->getSpiralCryptOpenSsl();
        $auth->access_token = $crypt->decrypt($auth->access_token, config('crypt.key'));
        $auth->refresh_token = $crypt->decrypt($auth->refresh_token, config('crypt.key'));

        return $auth;

    }

    public function getAuthorizationUrl($state)
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes),
            'state' => $state,
        ]);

        return $this->authUrl . '?' . $query;
    }

    public function getAccessToken($code)
    {
        // トークンリクエストデータの準備
        $postFields = http_build_query([
            'client_id' => $this->clientId,
            'scope' => implode(' ', $this->scopes),
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'client_secret' => $this->clientSecret,
        ]);

        // cURLセッションの初期化
        $ch = curl_init();

        // cURLオプションの設定
        curl_setopt($ch, CURLOPT_URL, $this->tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        // リクエストの実行
        $response = curl_exec($ch);

        // エラーチェック
        if (curl_errno($ch)) {
            $errorMsg = 'cURL Error: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($errorMsg);
        }

        // HTTPステータスコードの取得
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // cURLセッションのクローズ
        curl_close($ch);

        // レスポンスの処理
        if ($httpStatus === 200) {
            return json_decode($response, true);
        } else {
            throw new \Exception("Failed to obtain access token. HTTP Status Code: {$httpStatus}. Response: {$response}");
        }
    }

    public function getUserInfo($token)
    {
        // cURLセッションの初期化
        $ch = curl_init();

        // cURLオプションの設定
        curl_setopt($ch, CURLOPT_URL, $this->userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
            'Accept: application/json',
        ]);

        // リクエストの実行
        $response = curl_exec($ch);

        // エラーチェック
        if (curl_errno($ch)) {
            $errorMsg = 'cURL Error: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($errorMsg);
        }

        // HTTPステータスコードの取得
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // cURLセッションのクローズ
        curl_close($ch);

        // レスポンスの処理
        if ($httpStatus === 200) {
            $responseData = json_decode($response, true);
            return $responseData;
        } else {
            throw new \Exception("Failed to obtain user information. HTTP Status Code: {$httpStatus}. Response: {$response}");
        }
    }


    public function getUserImage($token)
    {
        // cURLセッションの初期化
        $ch = curl_init();

        // cURLオプションの設定
        curl_setopt($ch, CURLOPT_URL, $this->userImageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
        ]);

        // リクエストの実行
        $response = curl_exec($ch);

        // エラーチェック
        if (curl_errno($ch)) {
            $errorMsg = 'cURL Error: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($errorMsg);
        }

        // HTTPステータスコードの取得
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // cURLセッションのクローズ
        curl_close($ch);

        // レスポンスの処理
        if ($httpStatus === 200) {
            return $response;
        } else {
            throw new \Exception("Failed to obtain user information. HTTP Status Code: {$httpStatus}. Response: {$response}");
        }
    }

    private function getFromIdToken($idToken){

        // トークンをピリオドで分割
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid ID token format.');
        }

        list($header, $payload, $signature) = $parts;

        // Base64URLデコード関数
        $decode = function ($data) {
            $remainder = strlen($data) % 4;
            if ($remainder) {
                $padLength = 4 - $remainder;
                $data .= str_repeat('=', $padLength);
            }
            return base64_decode(strtr($data, '-_', '+/'));
        };

        // ペイロードをデコード
        $decodedPayload = $decode($payload);
        if ($decodedPayload === false) {
            throw new \Exception('Failed to decode payload.');
        }

        // JSONデコード
        $payloadData = json_decode($decodedPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode JSON payload: ' . json_last_error_msg());
        }

        return $payloadData;
    }

    public function getSubFromIdToken($idToken)
    {
        return $this->getFromIdToken($idToken)['sub'] ?? null;
    }


    public function getTidFromIdToken($idToken)
    {
        return $this->getFromIdToken($idToken)['tid'] ?? null;
    }


    /**
     * リフレッシュトークンを使用して新しいアクセストークンを取得する
     *
     * @param string $refreshToken
     * @return array
     * @throws \Exception
     */
    public function refreshAccessToken($refreshToken)
    {
        $postFields = http_build_query([
            'client_id' => $this->clientId,
            'scope' => implode(' ', $this->scopes),
            'refresh_token' => $refreshToken,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'refresh_token',
            'client_secret' => $this->clientSecret,
        ]);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $errorMsg = 'cURL Error: ' . curl_error($ch);
            //Log::error($errorMsg);
            curl_close($ch);
            throw new \Exception($errorMsg);
        }

        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpStatus === 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['access_token'])) {
                return $responseData;
            } else {
                //Log::error('Access token not found in response.', ['response' => $response]);
                throw new \Exception('Access token not found in response.');
            }
        } else {
            //Log::error("Failed to refresh access token. HTTP Status Code: {$httpStatus}. Response: {$response}");
            throw new \Exception("Failed to refresh access token. HTTP Status Code: {$httpStatus}. Response: {$response}");
        }
    }

    public function save($providerId , $accessToken , $refreshToken , $tokenExpiresAt) {

        $crypt   = spiral()->getSpiralCryptOpenSsl();

        $userInfo = $this->getUserInfo($accessToken);
        \SpiralDB::title('users')->upsert(
            'provider_id',
            [
                'email' => $userInfo['mail'],
                'provider' => 'entra',
                'provider_id' => $providerId,
                'access_token' => $crypt->encrypt($accessToken, config('crypt.key')),
                'refresh_token' => $crypt->encrypt($refreshToken, config('crypt.key')),
                'token_expires_at' => $tokenExpiresAt,
                'display_name' => $userInfo['displayName'],
                'office_location' => $userInfo['officeLocation'],
            ],
        );

        $user = \SpiralDB::title('users')->where('provider_id', $providerId)->value(
            ['id', 'display_name', 'userImage', 'email', 'permission'])->dataFormat('userImage', 'original_image_url')->get();
        $userImage = base64_encode($this->getUserImage($accessToken));

        $image = null;

        if($user->first()->userImage) {
            $curl = curl_init();
            curl_setopt(
                $curl,
                CURLOPT_URL,
                $user->first()->userImage
            );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $image = curl_exec($curl);
        }

        if(base64_encode($image) !== $userImage) {
            // ユーザーの登録またはログイン
            \SpiralDB::title('users')->where('provider_id', $providerId)->update(
                [
                    'userImage' => $userImage,
                ],
                [
                    'userImage' => [
                        'img_name' => 'profile.jpg',
                        'img_content_type' => 'image/jpeg'
                    ]
                ]
            );
        }

        return true;
    }
}