<?php

namespace spiralCommunity\App\Http\Controllers\Web ;

use framework\Http\Request;
use framework\Http\Controller;
use framework\Http\View;
use framework\Support\ServiceProvider;
use spiralCommunity\App\Services\OAuthService;

class WelcomeController extends Controller
{

    public function index(array $vars)
    {
        $oauthService = new OAuthService(config('entrada'));
        $auth = $oauthService->authUser();
        $userInfo = collect($oauthService->getUserInfo($auth->access_token));

        echo $userInfo->displayName;
        echo view("html/welcome")->render();
    }

    public function create(array $vars)
    {
        //
    }

    public function store(array $vars)
    {
        //
    }

    public function show(array $vars)
    {
    }

    public function edit(array $vars)
    {
        //
    }

    public function update(array $vars)
    {
        //
    }

    public function destroy(array $vars)
    {
        //
    }
}
