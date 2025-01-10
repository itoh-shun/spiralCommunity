<?php
header("Content-Type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // CORSのためのヘッダー設定（必要に応じて制限）

$env = require ".env.php";

$config = require "./src/spiralCommunity/config/app.php";
const TEST_ID = 'UtqofgmGNGcl_6525tCmEIvP-5FjcZHIrpvtkvxkpnI';

function getUrl()
{
    global $env;
    global $config;
    if (!empty($_SESSION['api_url'])) {
        return $_SESSION['api_url'];
    }
    $locator = "https://www.pi-pe.co.jp/api/locator";

    // スパイラルの操作画面で発行したトークンを設定します。
    $TOKEN = $env['deploy']['dev']['token'];

    // API用のHTTPヘッダ
    $api_headers = array(
        "X-SPIRAL-API: locator/apiserver/request",
        "Content-Type: application/json; charset=UTF-8",
    );

    // リクエストデータを作成
    $parameters = array();
    $parameters["spiral_api_token"] = $TOKEN; //トークン

    // JSON形式にエンコードします。
    $json = json_encode($parameters);

    // POSTで送信します。
    $stream = stream_context_create(
        array('http' => array(
            'method' => 'POST',
            'protocol_version' => '1.0',
            'header' => $api_headers,
            'content' => $json
        ))
    );

    // レスポンスデータ読み込み
    $response = file_get_contents($locator, false, $stream);
    $response_json = json_decode($response, true);

    $api_headers = array(
        "X-SPIRAL-API: area/login/request",
        "Content-Type: application/json; charset=UTF-8",
    );

    // リクエストデータを作成
    $parameters = array();
    $parameters["spiral_api_token"] = $TOKEN; //トークン
    $parameters["passkey"] = time(); //エポック秒
    $parameters["my_area_title"] = $config['login']['title'];
    $parameters["id"] = TEST_ID;
    $parameters["url_type"] = "1";

    // 署名を付けます
    $key = $parameters["spiral_api_token"] . "&" . $parameters["passkey"];
    $parameters["signature"] = hash_hmac('sha1', $key, $env['deploy']['dev']['secret'], false);

    // JSON形式にエンコードします。
    $json = json_encode(array_merge($parameters, $_GET, $_POST));
    // POSTで送信します。
    $stream = stream_context_create(
        array('http' => array(
            'method' => 'POST',
            'protocol_version' => '1.0',
            'header' => $api_headers,
            'content' => $json
        ))
    );

    // レスポンスデータ読み込み
    $response = file_get_contents($response_json['location'], false, $stream);
    $response_json = json_decode($response, true);

    // セッションID
    $jsessionid = $response_json['jsessionid'];
    $_SESSION['api_url'] = $response_json['url'];
    return $response_json['url'];
}

$url = getUrl();

$parameters = array();
if ($_GET['_method'] !== 'GET') {
    $parameters = array_map(function ($data) {
        return rawurlencode($data);
    }, $_POST);
}
$parameters["_path"] = $_GET['_path']; // トークン
$parameters["_method"] = $_GET['_method']; // トークン

$queryString = http_build_query($parameters);
// ストリームコンテキストの設定

$stream = stream_context_create(
    array('http' => array(
        'method' => 'POST', // POSTメソッドを指定
        'header' => "Content-Type: application/x-www-form-urlencoded; \r\n" .
            "Content-Length: " . strlen($queryString) . "\r\n",
        'content' => $queryString
    ))
);

// レスポンスデータ読み込み
$response = file_get_contents($url, false, $stream);
echo $response;