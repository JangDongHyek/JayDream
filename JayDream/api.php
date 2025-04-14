<?php
require_once __DIR__ . '/require.php';

use JayDream\Lib;
use JayDream\Service;

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
    case "get" :
        if(!$obj['table']) Lib::error("obj에 테이블이 없습니다.");
        $response = Service::get($obj);
        break;

    case "insert" :
        if(!$options['table']) Lib::error("options에 테이블이 없습니다.");
        if(isset($options['exists'])) Service::exists($options['exists']);
        if(isset($options['hashes'])) Service::hashes($options['hashes'],$obj);
        $response = Service::insert($obj,$options);
        break;

    case "update" :
        if(!$options['table']) Lib::error("options에 테이블이 없습니다.");
        if(isset($options['exists'])) Service::exists($options['exists']);
        if(isset($options['hashes'])) Service::hashes($options['hashes'],$obj);
        $response = Service::update($obj,$options);
        break;

    case "delete" :
    case "remove" :
        if(!$options['table']) Lib::error("options에 테이블이 없습니다.");
        $response = Service::delete($obj,$options);
        break;
}

echo Lib::jsonEncode($response);