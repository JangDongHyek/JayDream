<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Session {
    public static function init() {
        if(Config::$framework == "gnuboard") {
            //session_name('G5PHPSESSID'); // 안쓰는곳도있으므로 common 확인후 설정
            session_save_path(Config::$ROOT."/data/session");
        }else if(Config::$framework == "legacy") {
            if(!session_save_path()) Lib::error("session_save_path가 없습니다.");
        }

        if (!session_id()) {
            session_start();
        }
    }

    public static function get($key) {
        if(Config::$framework == "gnuboard" || Config::$framework == "legacy") {
            return $_SESSION[$key] ? $_SESSION[$key] : null;
        }else {
            Lib::error("개발해야함");
        }
    }

    public static function set($key,$value) {
        if(Config::$framework == "gnuboard" || Config::$framework == "legacy") {
            $_SESSION[$key] = $value;
        }else {
            Lib::error("개발해야함");
        }
    }

    public static function remove($key) {
        if(Config::$framework == "gnuboard" || Config::$framework == "legacy") {
            unset($_SESSION[$key]);
        }else {
            Lib::error("개발해야함");
        }
    }
}