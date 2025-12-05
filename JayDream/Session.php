<?php
namespace JayDream;

class Session {
    public static function init() {
        if (Config::$framework == "gnuboard") {
            session_save_path(Config::$ROOT."/data/session");
        } else if (Config::$framework == "legacy") {
            if (!session_save_path()) Lib::error("session_save_path가 없습니다.");
        }

        if (!session_id() && !in_array(Config::$framework, ["ci3", "ci4"])) {
            session_start();
        }
    }

    public static function get($key) {
        if (Config::$framework == "ci3") {
            $CI =& get_instance();
            return $CI->session->userdata($key);
        } else if (Config::$framework == "ci4") {
            return \CodeIgniter\Config\Services::session()->get($key);
        } else if (in_array(Config::$framework, ["gnuboard", "legacy"])) {
            return $_SESSION[$key] ?? null;
        } else {
            Lib::error("세션 프레임워크 미지원");
        }
    }

    public static function set($key, $value) {
        if (Config::$framework == "ci3") {
            $CI =& get_instance();
            $CI->session->set_userdata($key, $value);
        } else if (Config::$framework == "ci4") {
            \CodeIgniter\Config\Services::session()->set($key, $value);
        } else if (in_array(Config::$framework, ["gnuboard", "legacy"])) {
            $_SESSION[$key] = $value;
        } else {
            Lib::error("세션 프레임워크 미지원");
        }
    }

    public static function remove($key) {
        if (Config::$framework == "ci3") {
            $CI =& get_instance();
            $CI->session->unset_userdata($key);
        } else if (Config::$framework == "ci4") {
            \CodeIgniter\Config\Services::session()->remove($key);
        } else if (in_array(Config::$framework, ["gnuboard", "legacy"])) {
            unset($_SESSION[$key]);
        } else {
            Lib::error("세션 프레임워크 미지원");
        }
    }
}