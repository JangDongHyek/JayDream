<?php
namespace App\Controllers\jaydream\naver;

use App\Controllers\BaseController;

use JayDream\Lib;
use JayDream\Config;
use JayDream\Naver;

include_once(APPPATH . 'Libraries/JayDream/require.php');
include_once(APPPATH . 'Libraries/JayDream/provider/naver/Naver.php');

class JayDreamNaverController extends BaseController
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
        Naver::init();
        switch ($method) {
            case "login_uri" :
                $response['success'] = true;
                $response['message'] = "";
                $response['uri'] = Naver::createUri();
                break;
        }


        if(!Config::$DEV) $response = Lib::encryptAPI($response);
        echo Lib::jsonEncode($response);

        exit();
    }

    public function index() {
        Naver::init();
        $token = Naver::getToken();
        $user_response = Naver::getUser($token);

        $user = $user_response['response'];
        // 변수명 공용화
        $user['mb_hp'] = $user['mobile'];
        $user['primary'] = $user['id'];

        Lib::snsLogin($user,"df_member","naver");

        Lib::goURL("/");
        exit();
    }

}