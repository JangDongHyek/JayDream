<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use JayDream\Lib;
use JayDream\Service;
use JayDream\Session;
use JayDream\Config;


class JayDreamController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        include_once(APPPATH . 'libraries/JayDream/require.php');

    }

    public function method()
    {
        // JWT 체크
        if (!isset($_COOKIE['jd_jwt_token'])) {
            Lib::error("jwt 토큰이 존재하지않습니다.");
        }

        // JWT 파싱
        $jwt = Lib::jwtDecode($_COOKIE['jd_jwt_token']);

        // _method 체크
        if (!isset($_POST['_method'])) {
            Lib::error("_method가 존재하지않습니다.");
        }

        $method = $_POST['_method'];
        $response = [
            "success" => false,
            "message" => "_method가 존재하지않습니다."
        ];

        // obj, options 파싱
        $obj     = isset($_POST['obj']) ? Lib::jsonDecode($_POST['obj'], false) : [];
        $options = isset($_POST['options']) ? Lib::jsonDecode($_POST['options'], false) : [];

        switch ($method) {
            case "get":
                if (!$obj['table']) Lib::error("obj에 테이블이 없습니다.");
                $response = Service::get($obj);
                break;

            case "insert":
                if (!$options['table']) Lib::error("options에 테이블이 없습니다.");
                if (isset($options['exists'])) Service::exists($options['exists']);
                if (isset($options['hashes'])) Service::hashes($options['hashes'], $obj);
                $response = Service::insert($obj, $options);
                break;

            case "update":
                if (!$options['table']) Lib::error("options에 테이블이 없습니다.");
                if (isset($options['exists'])) Service::exists($options['exists']);
                if (isset($options['hashes'])) Service::hashes($options['hashes'], $obj);
                $response = Service::update($obj, $options);
                break;

            case "where_update":
                if (!$options['table']) Lib::error("options에 테이블이 없습니다.");
                if (isset($options['exists'])) Service::exists($options['exists']);
                if (isset($options['hashes'])) Service::hashes($options['hashes'], $obj);
                $response = Service::whereUpdate($obj, $options);
                break;

            case "delete":
            case "remove":
                if (!$options['table']) Lib::error("options에 테이블이 없습니다.");
                $response = Service::delete($obj, $options);
                break;

            case "where_delete":
                if (!$obj['table']) Lib::error("obj에 테이블이 없습니다.");
                $response = Service::whereDelete($obj);
                break;

            case "file_save":
                $response = Service::fileSave($obj, $options);
                break;

            case "session_set":
                foreach ($obj as $key => $value) {
                    Session::set($key, $value);
                }
                $response['success'] = true;
                break;

            case "session_get":
                foreach ($obj as $key => $value) {
                    $obj[$key] = Session::get($key);
                }
                $response['sessions'] = $obj;
                $response['success'] = true;
                $response['message'] = "";
                break;

            default:
                Lib::error("지원하지 않는 method 입니다: " . $method);
        }

        // API 암호화 (운영모드)
        if (!Config::$DEV) {
            $response = Lib::encryptAPI($response);
        }

        // 출력
        echo Lib::jsonEncode($response);
        exit();
    }
}