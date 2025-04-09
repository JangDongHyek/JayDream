<?php
require_once __DIR__ . '/init.php';

use JayDream\Lib;
use JayDream\Service;

$method = $_POST['_method'];

$response = array(
    "success" => false,
    "message" => "_method가 존재하지않습니다."
);

$obj = Lib::jsonDecode($_POST['obj'],false);
if(!$obj['table']) Lib::error("obj에 테이블이 없습니다.");

if($method == "get") {
    $response = Service::get($obj);
}else if($method == "insert") {
    $response = Service::insert($obj);
}else if($method == "delete" || $method == "remove") {
    $response = Service::delete($obj);
}
echo Lib::jsonEncode($response);