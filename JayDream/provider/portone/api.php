<?php
require_once __DIR__ . '/../../require.php';
require_once __DIR__ . "/Portone.php";

use JayDream\Lib;
use JayDream\Service;
use JayDream\Config;
use JayDream\Portone;

if (!isset($_COOKIE['jd_jwt_token'])) Lib::error("jwt 토큰이 존재하지않습니다.\n새로고침을 해주세요.");
$jwt = Lib::jwtDecode($_COOKIE['jd_jwt_token']);

$method = $_POST['_method'];

$response = array(
    "success" => false,
    "message" => "_method가 존재하지않습니다."
);



$obj = Lib::jsonDecode($_POST['obj'],false);
$options = Lib::jsonDecode($_POST['options'],false);
Portone::init();

switch ($method) {
    case "init" :
        $info = array(
            "mid" => Portone::$mid,
            "store_id" => Portone::$store_id,
            "secret_key" => Portone::$secret_key,
            "channel_key" => Portone::$channel_key,
            "v1_code" => Portone::$v1_code,
            "v1_api_key" => Portone::$v1_api_key,
            "v1_api_secret" => Portone::$v1_api_secret,
        );

        $response['info'] = $info;
        $response['success'] = true;
        $response['message'] = "";
        break;

}
if(!Config::$DEV) $response = Lib::encryptAPI($response);
echo Lib::jsonEncode($response);

exit();
