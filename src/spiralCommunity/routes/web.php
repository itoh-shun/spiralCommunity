
<?php
require_once (defined('BASE_PATH') ? BASE_PATH : "") . "framework/autoload_static.php";
require_once "spiralCommunity/autoload_static.php";

use framework\Routing\Router;
use spiralCommunity\App\Http\Controllers\Web\WelcomeController;

/** */

/** components */

//param _method="" を指定すると GET PUT DELETE GET PATCH を区別できる

const VIEW_FILE_ROOT = "spiralCommunity/resources";

/** sample */

Router::middlewares([\spiralCommunity\App\Http\Middleware\RefreshTokenMiddleware::class ,\spiralCommunity\App\Http\Middleware\TenantValidationMiddleware::class], function(){
    Router::map("GET", "api/login/me", [\spiralCommunity\App\Http\Controllers\Api\MeController:: class , "index"]);
    Router::map("GET", "api/users", [\spiralCommunity\App\Http\Controllers\Api\UsersController:: class , "index"]);
    Router::map("GET", "api/users/:userId", [\spiralCommunity\App\Http\Controllers\Api\UsersController:: class , "show"]);
    Router::map("PATCH", "api/users/:userId", [\spiralCommunity\App\Http\Controllers\Api\UsersController:: class , "store"]);
    Router::map("GET", "api/tags", [\spiralCommunity\App\Http\Controllers\Api\TagsController:: class , "index"]);
});



//Router::map("GET", "/:userId", [HogeHogeController:: class , "show"]);
//Router::map("POST", "/user", [HogeHogeController:: class , "create"]);
//Router::map("PATCH", "/:userId", [HogeHogeController:: class , "update"]);
//Router::map("DELETE", "/", [HogeHogeController:: class , "delete"]);


$router = new Router();
//$router->middleware();毎回必ずチェックする場合はこっち
$app = new spiralCommunity\spiralCommunityApplication();
$exceptionHandler = new spiralCommunity\App\Exceptions\ExceptionHandler();
$kernel = new framework\Http\Kernel($app, $router ,$exceptionHandler);
$request = new framework\Http\Request();

$kernel->handle($request);

