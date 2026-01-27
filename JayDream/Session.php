<?php
namespace JayDream;

class Session {
    public static $model;
    public static function init() {
        if (Config::$framework == "gnuboard") {
            session_save_path(Config::$ROOT."/data/session");
        } else if (Config::$framework == "legacy") {
            if (!session_save_path()) Lib::error("session_save_path가 없습니다.");
        } else if(Config::$framework == "ci3") {
            if(Config::$ci3_config['sess_driver'] == "database") {
                self::$model = new Model(Config::$ci3_config['sess_save_path']);
            }else {

            }
        }

        if (!session_id() && !in_array(Config::$framework, ["ci3", "ci4"])) {
            session_start();
        }
    }

    public static function get($key) {
        if (Config::$framework == "ci3") {
            $all = self::getAll();
            return $all[$key] ?? null;
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
            $all = self::getAll();
            $all[$key] = $value;
            $serialized = self::ci3_pack($all);
            $result = self::$model->where("id", $_COOKIE[Config::$ci3_config['sess_cookie_name']])->get();
            if($result['count'] == 0) Lib::error("CI3 SESSION set() : cookie값이 잘못되었습니다.");
            $session = $result['data'][0];
            $session['data'] = $serialized;
            self::$model->update($session);
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
            $all = self::getAll();
            unset($all[$key]);
            $serialized = self::ci3_pack($all);
            $result = self::$model->where("id", $_COOKIE[Config::$ci3_config['sess_cookie_name']])->get();
            if($result['count'] == 0) Lib::error("CI3 SESSION remove() : cookie값이 잘못되었습니다.");
            $session = $result['data'][0];
            $session['data'] = $serialized;
            self::$model->update($session);
        } else if (Config::$framework == "ci4") {
            \CodeIgniter\Config\Services::session()->remove($key);
        } else if (in_array(Config::$framework, ["gnuboard", "legacy"])) {
            unset($_SESSION[$key]);
        } else {
            Lib::error("세션 프레임워크 미지원");
        }
    }

    public static function getAll() {
        if (Config::$framework == "ci3") {
            if(Config::$ci3_config['sess_driver'] == "database") {
                $session_data = self::$model->where("id", $_COOKIE[Config::$ci3_config['sess_cookie_name']])->get()['data'][0];
                return self::ci3_unpack($session_data['data']);
            } else {
                return $_SESSION;
            }
        } else if (Config::$framework == "ci4") {
            return \CodeIgniter\Config\Services::session()->get();
        } else if (in_array(Config::$framework, ["gnuboard", "legacy"])) {
            return $_SESSION;
        } else {
            Lib::error("세션 프레임워크 미지원");
        }
    }

    // CI3 세션 언팩
    private static function ci3_unpack($data) {
        $result = [];
        $offset = 0;

        while ($offset < strlen($data)) {
            if (!preg_match('/(\w+)\|/', substr($data, $offset), $matches, PREG_OFFSET_CAPTURE)) {
                break;
            }

            $key = $matches[1][0];
            $offset += strlen($matches[0][0]);

            $value = unserialize(substr($data, $offset));
            $result[$key] = $value;

            $offset += strlen(serialize($value));
        }

        return $result;
    }

    // CI3 세션 팩
    private static function ci3_pack($data) {
        $serialized = '';
        foreach ($data as $key => $value) {
            $serialized .= $key . '|' . serialize($value);
        }
        return $serialized;
    }
}