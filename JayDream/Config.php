<?php
namespace JayDream;

use JayDream\Lib;

class Config {
    public static $DEV = false;
    private static $DEV_IPS = ["121.140.204.65","112.160.220.208"];
    public static $ROOT = "";
    public static $URL = "";


    const HOSTNAME = "localhost";
    const DATABASE = "exam";
    const USERNAME = "exam";
    const PASSWORD = "password";

    const ALERT = "origin"; // origin , swal
    const ENCRYPT = "mb_5";

    const CAPTCHA_PATTERN = "number";
    const FONT = "GowunBatang-Bold.ttf";
    const FONTSIZE = "24";

    const KAKAO_CLIENT_ID = "";
    const NAVER_CLIENT_ID = "";
    const NAVER_CLIENT_SECRET = "";

    const SESSION_TABLE = "";

    public static function init() {
        if(in_array(Lib::getClientIP(),self::$DEV_IPS)) self::$DEV = true;
        self::$ROOT = dirname(__DIR__);

        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 's' : '') . '://';
        $user = str_replace(str_replace(self::$ROOT, '', $_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_NAME']);
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        if(isset($_SERVER['HTTP_HOST']) && preg_match('/:[0-9]+$/', $host))
            $host = preg_replace('/:[0-9]+$/', '', $host);
        self::$URL = $http.$host.$user;
    }
}
