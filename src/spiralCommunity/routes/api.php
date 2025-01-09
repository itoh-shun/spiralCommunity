
<?php
require_once (defined('BASE_PATH') ? BASE_PATH : "") . "framework/autoload_static.php";
require_once "spiralCommunity/autoload_static.php";

use framework\Routing\Router;

/** */

/** components */

//param _method="" を指定すると GET PUT DELETE GET PATCH を区別できる

const VIEW_FILE_ROOT = "";

/** sample */

//Router::map("GET", "/users", [UserController:: class , "index"]);

//Router::map("GET", "/:userId", [UserController:: class , "show"]);

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

