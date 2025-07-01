<?php
require_once __DIR__ . '/../../require.php';
require_once __DIR__ . "/Kakao.php";

use JayDream\Lib;
use JayDream\Service;
use JayDream\Config;
use JayDream\Kakao;

if (!isset($_COOKIE['jd_jwt_token'])) Lib::error("jwt 토큰이 존재하지않습니다.");
$jwt = Lib::jwtDecode($_COOKIE['jd_jwt_token']);


$method = $_POST['_method'];

$response = array(
    "success" => false,
    "message" => "_method가 존재하지않습니다."
);


$obj = Lib::jsonDecode($_POST['obj'],false);
$options = Lib::jsonDecode($_POST['options'],false);
Kakao::init();
switch ($method) {
    case "login_uri" :
        $response['success'] = true;
        $response['message'] = "";
        $response['uri'] = Kakao::createUri();
        break;

    case "info" :
        $response['success'] = true;
        $response['message'] = "";
        $response['info'] = Kakao::getInfo();
        break;

    case "redirect_uri" :
        $response['success'] = true;
        $response['message'] = "";
        $response['uri'] = Kakao::redirectUri();
        break;
}

if(!Config::$DEV) $response = Lib::encryptAPI($response);
echo Lib::jsonEncode($response);