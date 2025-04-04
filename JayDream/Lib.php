<?php
namespace JayDream;

use JayDream\Config;

class Lib {
    public static function error($msg) {
        $trace = debug_backtrace();
        $trace = array_reverse($trace);
        $er = array(
            "success" => false,
            "message" => $msg
        );

        if(Config::$DEV) {
            foreach($trace as $index => $t) {
                $er['file_'.$index] = $t['file'];
                $er['line_'.$index] = $t['line'];
            }
        }

        header('Content-Type: application/json');
        echo self::jsonEncode($er);
        die();
        //throw new \Exception($msg);
    }

    //jsonEncode 한글깨짐 방지설정넣은
    public static function jsonEncode($data) {
        $value = json_encode($data,JSON_UNESCAPED_UNICODE);

        return str_replace('\\/', '/', $value);

    }

    //상황에 필요한 로직들을 넣은 Jsondecode 함수
    public static function jsonDecode($origin_json,$encode = true) {
        $str_json = str_replace('\\n', '###NEWLINE###', $origin_json); // textarea 값 그대로 저장하기위한 변경
        $str_json = stripslashes($str_json);
        $str_json = str_replace('###NEWLINE###', '\\n', $str_json);

        $obj = json_decode($str_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $json = str_replace('\\n', '###NEWLINE###', $origin_json); // textarea 값 그대로 저장하기위한 변경
            $json = str_replace('\"', '###NEWQUOTATION###', $json);
            $json = str_replace('\\', '', $json);
            $json = str_replace('\\\\', '', $json);
            $json = str_replace('###NEWLINE###', '\\n', $json);
            $json = str_replace('###NEWQUOTATION###', '\"', $json);

            $obj = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $msg = "Jl jsonDecode()";

                self::error("Jl jsonDecode()\norigin : ".$origin_json."\nreplace : $json");
            }
        }

        // 오브젝트 비교할때가있어 파라미터가 false값일땐 모든값 decode
        if($encode) {
            // PHP 버전에 따라 decode가 다르게 먹히므로 PHP단에서 Object,Array,Boolean encode처리
            foreach ($obj as $key => $value) {
                if (is_array($obj[$key])) $obj[$key] = self::jsonEncode($obj[$key]);
                if (is_object($obj[$key])) $obj[$key] = self::jsonEncode($obj[$key]);
            }
        }

        return $obj;
    }

    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    // 문자열을 배열로 반환하는함수 ,는 나눠서 반환한다
    public static function convertToArray($array) {
        if (is_string($array)) {
            if (strpos($array, ',') !== false) {
                return explode(',', $array);
            } else {
                return [$array];
            }
        }

        if (is_array($array)) {
            return $array;
        }

        return [];
    }

    // 해당 폴더의 파일들만 include하는 함수
    public static function includeDir($dir_name) {
        $files = self::getDir($dir_name);

        foreach ($files as $file) include_once($file);
    }

    /**
     * 특정 디렉토리 내부의 파일 또는 디렉토리 목록을 가져오는 함수
     *
     * @param string  $dir_name   탐색할 디렉토리 경로 (상대 또는 절대)
     * @param bool    $dirs       true일 경우 모든 항목을 포함, false일 경우 .php 파일만 포함
     * @param bool    $root_path  true일 경우 $this->ROOT 경로를 앞에 자동으로 붙임
     * @return array|null         경로 문자열 배열 (파일/디렉토리), 항목이 없으면 null 반환
     */
    public static function getDir($dir_name, $dirs = false, $root_path = true)
    {
        $dir = $dir_name;
        if (strpos($dir_name, Config::$ROOT) === false) $dir =Config::$ROOT . $dir_name;
        $ffs = scandir($dir);
        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);
        if (count($ffs) < 1) return;

        $result = array();
        foreach ($ffs as $ff) {
            if (!$dirs && !strpos($ff, ".php")) continue;

            if ($root_path) $filename = $dir;
            $filename .= "/".$ff;


            array_push($result,$filename);
        }

        return $result;
    }

    public static function generateUniqueId() {
        return 'P-' . uniqid() . str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT);
    }
}
