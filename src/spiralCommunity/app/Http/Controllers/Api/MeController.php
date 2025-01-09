<?php

namespace spiralCommunity\App\Http\Controllers\Api ;

use framework\Http\Request;
use framework\Http\Controller;
use framework\Http\View;
use framework\Support\ServiceProvider;
use spiralCommunity\App\Services\OAuthService;

class MeController extends Controller
{

    public function index(array $vars)
    {
        $oauthService = new OAuthService(config('entrada'));
        $auth = $oauthService->authUser();
        echo json_encode($auth->all(), true);
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
