<?php
require_once __DIR__ . '/../../../require.php';
require_once __DIR__ . "/../Google.php";
use JayDream\Google;
use JayDream\Lib;
use JayDream\Model;
use JayDream\Session;
use JayDream\Config;

Google::init();

if(Config::$framework === 'ci3') {
    $query = array();
    foreach(array('code', 'state', 'error', 'error_description') as $key) {
        if(isset($_GET[$key])) {
            $query[$key] = $_GET[$key];
        }
    }

    $url = '/login/google_callback';
    if(!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    Lib::goURL($url);
}

$token = Google::getToken();
$user_response = Google::getUser($token);

$user = array(
    'primary' => isset($user_response['sub']) ? $user_response['sub'] : '',
    'email' => isset($user_response['email']) ? $user_response['email'] : '',
    'nickname' => isset($user_response['name']) ? $user_response['name'] : '',
    'phone' => '',
);

if(isset($user_response['email_verified']) && !$user_response['email_verified']) {
    Lib::alert('구글 계정 이메일 인증이 필요합니다.', '/');
}

$result = Lib::snsLogin($user, "member", "google");
if(empty($result['success'])) {
    Lib::alert(isset($result['message']) ? $result['message'] : '구글 로그인 처리 중 오류가 발생했습니다.', '/');
}
Lib::userLogin($result['user']);

Lib::goURL("/");
?>
