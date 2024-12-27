<?php
// app/Http/Middleware/RefreshTokenMiddleware.php

namespace spiralCommunity\App\Http\Middleware;

use framework\Http\Middleware\Middleware;
use framework\Http\Middleware\MiddlewareInterface;
use framework\Routing\Router;
use SiLibrary\Collection;
use SiLibrary\SiDateTime;
use SiLibrary\SpiralConnecter\SpiralWeb;
use spiralCommunity\App\Services\OAuthService;

class RefreshTokenMiddleware extends Middleware implements
    MiddlewareInterface
{
    public function process(array $vars)
    {
        $oauthService = new OAuthService(config('entrada'));
        $user = $oauthService->authUser();
        if ($user) {
            $crypt   = spiral()->getSpiralCryptOpenSsl();
            $expiresAt = new SiDateTime($user->token_expires_at);
            $bufferTime = 300; // 5分前にトークンをリフレッシュ

            if ($expiresAt && SiDateTime::now()->addSeconds($bufferTime)->isAfter($expiresAt)){
                $refreshToken = $user->refresh_token ?: null;

                if ($refreshToken) {

                    try {
                        $newTokens = $oauthService->refreshAccessToken($refreshToken);

                        // 新しいトークンを保存
                        \SpiralDB::title('users')->upsert(
                            'provider_id',
                            [
                                'access_token' => $crypt->encrypt($newTokens['access_token'], config('crypt.key')),
                                'refresh_token' => isset($newTokens['refresh_token']) ? $crypt->encrypt($newTokens['refresh_token'],config('crypt.key')) : $user->refresh_token,
                                'token_expires_at' => SiDateTime::now()->addSeconds($newTokens['expires_in'])->format('Y-m-d H:i:s'),
                            ]
                        );

                        return true;

                    } catch (\Exception $e) {
                        //Log::error("アクセストークンの更新に失敗しました。ユーザーID: {$user->id}, エラー: {$e->getMessage()}");
                        // 必要に応じてユーザーをログアウトさせるなどの処理を行う
                        //Auth::logout();
                        $res = SpiralWeb::auth(config('login.title'))->logout();
                        Router::urlRedirect($res['url']);
                        exit;
                        //return redirect()->route('login')->with('error', 'セッションの更新に失敗しました。再度ログインしてください。');
                    }
                } else {
                    //Log::warning("リフレッシュトークンが存在しません。ユーザーID: {$user->id}");
                    //Auth::logout();
                    $res = SpiralWeb::auth(config('login.title'))->logout();
                    Router::urlRedirect($res['url']);
                    exit;
                    //return redirect()->route('login')->with('error', 'セッションが無効です。再度ログインしてください。');
                }
            }
            return true;
        }
        $res = SpiralWeb::auth(config('login.title'))->logout();
        Router::urlRedirect($res['url']);
        exit;
    }
}
