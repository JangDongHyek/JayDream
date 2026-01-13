<?php
namespace App\Controllers\jaydream\kakao;

use App\Controllers\BaseController;

use JayDream\Lib;
use JayDream\Config;
use JayDream\Kakao;

include_once(APPPATH . 'Libraries/JayDream/require.php');
include_once(APPPATH . 'Libraries/JayDream/provider/kakao/Kakao.php');

class JayDreamKakaoController extends BaseController
{
    public function __construct() {

    }

    public function method() {
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
        }


        if(!Config::$DEV) $response = Lib::encryptAPI($response);
        echo Lib::jsonEncode($response);

        exit();
    }

    public function index() {
        Kakao::init();
        $token = Kakao::getToken();
        $user_response = Kakao::getUser($token);
        $user = $user_response['kakao_account'];

        // 변수명 공용화
        $user['mb_hp'] = Lib::formatPhoneNumber($user['phone_number']);
        $user['primary'] = $user_response['id'];

        Lib::snsLogin($user,"df_member","kakao");

        Lib::goURL("/");
        exit();
    }

}