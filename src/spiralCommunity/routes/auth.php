
<?php
require_once (defined('BASE_PATH') ? BASE_PATH : "") . "framework/autoload_static.php";
require_once "spiralCommunity/autoload_static.php";

use framework\Routing\Router;

/** */

/** components */

//param _method="" を指定すると GET PUT DELETE GET PATCH を区別できる

const VIEW_FILE_ROOT = "spiralCommunity/resources";

/** sample */

Router::map("GET", "/", [\spiralCommunity\App\Http\Controllers\Auth\EntraController:: class , "index"]);
Router::map("GET", 'auth/entra', [\spiralCommunity\App\Http\Controllers\Auth\EntraController::class, 'redirectToProvider'])->name('auth.entra');
Router::map("GET", 'auth/entra/callback', [\spiralCommunity\App\Http\Controllers\Auth\EntraController::class, 'handleProviderCallback']);

$router = new Router();
//$router->middleware();毎回必ずチェックする場合はこっち
$app = new spiralCommunity\spiralCommunityApplication();
$exceptionHandler = new spiralCommunity\App\Exceptions\ExceptionHandler();
$kernel = new \framework\Http\Kernel($app, $router ,$exceptionHandler);
$request = new \framework\Http\Request();

$kernel->handle($request);

