<?php
require_once __DIR__ . '/require.php';

use JayDream\Lib;
use JayDream\Service;
use JayDream\Session;
use JayDream\Config;

if (!isset($_COOKIE['jd_jwt_token'])) Lib::error("jwt 토큰이 존재하지않습니다.\n새로고침을 해주세요.");
$jwt = Lib::jwtDecode($_COOKIE['jd_jwt_token']);

$method = $_POST['_method'];

$response = array(
    "success" => false,
    "message" => "_method가 존재하지않습니다."
);



$obj = Lib::jsonDecode($_POST['obj'],false);
$options = Lib::jsonDecode($_POST['options'],false);

switch ($method) {
    case "point" :
        $result = insert_point($obj['mb_id'],$obj['point'],$obj['content'],$obj['po_rel_table'],$obj['po_rel_id'],$obj['po_rel_action']);
        $response['success'] = true;
        $response['message'] = "";
        break;
}

if(!Config::$DEV) $response = Lib::encryptAPI($response);
echo Lib::jsonEncode($response);

exit();
