<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Naver {
    private static $client_id;
    private static $client_secret;
    private static $code;
    public static function init() {
        $config = require __DIR__ . '/config.php';

        if(!$config['client_id']) Lib::error("client_id 값이 없습니다.");
        if(!$config['client_secret']) Lib::error("client_secret 값이 없습니다.");
        self::$client_id = $config['client_id'];
        self::$client_secret = $config['client_secret'];
        self::$code = $_GET['code'];
    }

    public static function redirectUri() {
        return Config::$URL . "/JayDream/plugin/naver/oauth/index.php";
    }

    public static function createUri() {
        $url = "https://nid.naver.com/oauth2.0/authorize?client_id=" . self::$client_id . "&redirect_uri=" . self::redirectUri();
        $url .= "&response_type=code";

        return $url;
    }

    public static function getToken() {
        if(!self::$code) Lib::error("code 가 존재하지않습니다.");

        $data = array(
            "grant_type" => "authorization_code",
            "client_id" => self::$client_id,
            "client_secret" => self::$client_secret,
            "redirect_uri" => self::redirectUri(),
            "code" => self::$code
        );

        $options = array(
            "data" => $data,
            "http_build" => true,
            "content_type" => "content-type: application/x-www-form-urlencoded",

        );

        return Lib::curlRequest("https://nid.naver.com/oauth2.0/token","POST",$options);
    }

    public static function getUser($token) {
        $options = array(
            "content_type" => "content-type: application/x-www-form-urlencoded",
            "authorization" => "Authorization: Bearer {$token['access_token']}"
        );

        return Lib::curlRequest("https://openapi.naver.com/v1/nid/me","POST",$options);
    }
}