<?php
require_once __DIR__ . '/../../../require.php';
require_once __DIR__ . "/../Kakao.php";
use JayDream\Kakao;
use JayDream\Lib;
use JayDream\Model;
use JayDream\Session;
use JayDream\Config;

Kakao::init();
$token = Kakao::getToken();
$user_response = Kakao::getUser($token);

/*
 * 카카오 고유값 id 10자리 (카카오 앱에서 필수값으로 선택해야 스코프없이 값이 넘어옴 선택사항은 스코프 설정해줘야함)
 * $user = name, email, phone_number
 * 권한 신청 필수
 * phone_number +82 치환 선택
 */
$user = $user_response['kakao_account'];
// 변수명 공용화
$user['phone'] = Lib::formatPhoneNumber($user['phone_number']);
$user['primary'] = $user_response['id'];

Lib::snsLogin($user,"g5_member","kakao");

Lib::goURL("/");
?>