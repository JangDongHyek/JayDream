<?php
namespace App\Controllers;

use App\Controllers\BaseController;

use JayDream\Lib;
use JayDream\Service;
use JayDream\Session;
use JayDream\Config;

include_once(APPPATH . 'Libraries/JayDream/require.php');

class JayDreamControllerCi4 extends BaseController
{
    private $service;

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

            case "where_update" :
                if(!$options['table']) Lib::error("options에 테이블이 없습니다.");
                if(isset($options['exists'])) Service::exists($options['exists']);
                if(isset($options['hashes'])) Service::hashes($options['hashes'],$obj);
                $response = Service::whereUpdate($obj,$options);
                break;

            case "delete" :
            case "remove" :
                if(!$options['table']) Lib::error("options에 테이블이 없습니다.");
                $response = Service::delete($obj,$options);
                break;

            case "file_save" :
                $response = Service::fileSave($obj,$options);
                break;

            case "session_set" :
                foreach ($obj as $key => $value) {
                    Session::set($key, $value);
                }
                $response['success'] = true;
                break;

            case "session_get" :
                foreach ($obj as $key => $value) {
                    $obj[$key] = Session::get($key);
                }
                $response['sessions'] = $obj;
                $response['success'] = true;
                $response['message'] = "";
                break;

        }


        if(!Config::$DEV) $response = Lib::encryptAPI($response);
        echo Lib::jsonEncode($response);

        exit();
    }

}