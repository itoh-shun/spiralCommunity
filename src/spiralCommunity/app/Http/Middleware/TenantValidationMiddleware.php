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

class TenantValidationMiddleware extends Middleware implements
    MiddlewareInterface
{
    public function process(array $vars)
    {
        $oauthService = new OAuthService(config('entrada'));
        $auth = $oauthService->authUser();
        return in_array($oauthService->getTidFromIdToken($auth->access_token), config('entrada.arrow_tenant_ids'));
    }
}
