<?php
require_once __DIR__ . '/../../require.php';

use JayDream\Lib;

/**
 * Google OAuth2 - 최초 1회 브라우저 인증 후 토큰 저장
 * 접속: https://isweb.co.kr/JayDream/provider/google/oauth2.php
 */

//폴더 권한체크 없으면 json 저장안됌
$token_dir = __DIR__;
if (!is_writable($token_dir)) {
    Lib::error("token.json 저장 실패: {$token_dir} 폴더 쓰기 권한 없음. chmod 777 로 변경해주세요.");
}

$config = require __DIR__ . '/config.php';

define('GOOGLE_CLIENT_ID',     $config['client_id']);
define('GOOGLE_CLIENT_SECRET', $config['client_secret']);
define('GOOGLE_REDIRECT_URI',  $config['redirect_uri']);
define('GOOGLE_TOKEN_FILE',    __DIR__ . '/token.json');

// 인증 코드 받은 경우 → 토큰 발급
if (isset($_GET['code'])) {
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
    ]));
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['refresh_token'])) {
        $response['created_at'] = time();
        file_put_contents(GOOGLE_TOKEN_FILE, json_encode($response));
        echo "✅ 토큰 발급 완료. token.json 저장됨.<br>";
        echo "refresh_token: " . $response['refresh_token'];
    } else {
        echo "❌ 토큰 발급 실패:<br>";
        echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    }
    exit;
}

// 토큰 없는 경우 → 구글 인증 URL로 이동
$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'https://www.googleapis.com/auth/gmail.send',
        'access_type'   => 'offline',
        'prompt'        => 'consent',
    ]);

header('Location: ' . $auth_url);
exit;