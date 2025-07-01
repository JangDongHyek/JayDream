<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Kakao {
    private static $client_id;
    private static $code;
    public static function init() {
        $config = require __DIR__ . '/config.php';

        if(!$config['client_id']) Lib::error("client_id 값이 없습니다.");
        self::$client_id = $config['client_id'];
        self::$code = $_GET['code'];
    }

    public static function getInfo() {
        return array("client_id" => self::$client_id);
    }

    public static function redirectUri() {
        return Lib::normalizeUrl(Config::$URL . "/JayDream/plugin/kakao/oauth/index.php");
    }

    public static function createUri() {
        $url = "https://kauth.kakao.com/oauth/authorize?client_id=" . self::$client_id . "&redirect_uri=" . self::redirectUri();
        $url .= "&response_type=code";

        return $url;
    }

    public static function getToken() {
        if(!self::$code) Lib::error("code가 존재하지않습니다.");
        $data = array(
            "grant_type" => "authorization_code",
            "client_id" => self::$client_id,
            "redirect_uri" => self::redirectUri(),
            "code" => self::$code
        );

        $options = array(
            "data" => $data,
            "http_build" => true,
            "content_type" => "content-type: application/x-www-form-urlencoded",

        );

        return Lib::curlRequest("https://kauth.kakao.com/oauth/token","POST",$options);
    }

    public static function getUser($token) {
        $options = array(
            "content_type" => "content-type: application/x-www-form-urlencoded",
            "authorization" => "Authorization: Bearer {$token['access_token']}"
        );

        return Lib::curlRequest("https://kapi.kakao.com/v2/user/me","POST",$options);
    }
}