<?php
require_once __DIR__ . '/../../require.php';

use JayDream\Lib;
use JayDream\Service;
use JayDream\Config;

if (!isset($_COOKIE['jd_jwt_token'])) Lib::error("jwt 토큰이 존재하지않습니다.");
$jwt = Lib::jwtDecode($_COOKIE['jd_jwt_token']);


$method = $_POST['_method'];

$response = array(
    "success" => false,
    "message" => "_method가 존재하지않습니다."
);


$obj = Lib::jsonDecode($_POST['obj'],false);
$options = Lib::jsonDecode($_POST['options'],false);

switch ($method) {
    case "innopay" :
        if (!Config::existsTable("jd_plugin_innopay")) {
            $schema = require __DIR__ . '/../../schema/jd_plugin_innopay.php';
            Config::createTableFromSchema("jd_plugin_innopay",$schema);
        }
        $response = require __DIR__ . '/../../plugin/innopay/config.php';
        break;
}

$response = Lib::encryptAPI($response);
echo Lib::jsonEncode($response);