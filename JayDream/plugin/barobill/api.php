<?php
require_once __DIR__ . '/../../require.php';
require_once __DIR__ . "/BaroBill.php";

use JayDream\Lib;
use JayDream\BaroBill;

if (!isset($_COOKIE['jd_jwt_token'])) Lib::error("jwt 토큰이 존재하지않습니다.");
$jwt = Lib::jwtDecode($_COOKIE['jd_jwt_token']);


$method = $_POST['_method'];

$response = array(
    "success" => false,
    "message" => "_method가 존재하지않습니다."
);


$obj = Lib::jsonDecode($_POST['obj'],false);
$options = Lib::jsonDecode($_POST['options'],false);
BaroBill::init();

switch ($method) {
    case "CheckCorpIsMember" :
        $response = BaroBill::CheckCorpIsMember($obj);
        break;

    case "RegistCorp" :
        $response = BaroBill::RegistCorp($obj);
        break;
}

echo Lib::jsonEncode($response);