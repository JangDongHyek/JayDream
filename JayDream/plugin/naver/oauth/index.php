<?php
require_once __DIR__ . '/../../../require.php';
require_once __DIR__ . "/../Naver.php";
use JayDream\Naver;
use JayDream\Lib;
use JayDream\Model;
use JayDream\Session;
use JayDream\Config;

Naver::init();
$token = Naver::getToken();
$user_response = Naver::getUser($token);

/*
 * 네이버 고유값 id 43자리 추측이지만 아마 맥스멈 50자리일듯함
 * $user = name, email, mobile
 * 권한 신청 필요없으나 검수를통과해야 실사용가능
 */
$user = $user_response['response'];
// 변수명 공용화
$user['phone'] = $user['mobile'];
$user['primary'] = $user['id'];

Lib::snsLogin($user,"g5_member","naver");

Lib::goURL("/");
?>