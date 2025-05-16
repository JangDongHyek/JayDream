<?php
namespace JayDream;

use JayDream\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use JayDream\Model;
use JayDream\Session;

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

        if(Config::$DEV) echo self::jsonEncode($er);
        else echo self::jsonEncode(self::encryptAPI($er));
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

    public static function jwtDecode($token) {
        try {
            $jwt = JWT::decode($token, Config::PASSWORD, array('HS256'));
            if ($jwt->iss !== Config::$URL) {
                setcookie("jd_jwt_token", "", time() - (Config::COOKIE_TIME +100), "/");
                Lib::error("JWT 발급자가 동일하지않습니다.");
            }
            return $jwt;
        }catch (ExpiredException $e) {
            setcookie("jd_jwt_token", "", time() - (Config::COOKIE_TIME +100), "/");
            Lib::error("JWT 만료됨");
        } catch (SignatureInvalidException $e) {
            setcookie("jd_jwt_token", "", time() - (Config::COOKIE_TIME +100), "/");
            Lib::error("JWT 서명 오류");
        } catch (BeforeValidException $e) {
            setcookie("jd_jwt_token", "", time() - (Config::COOKIE_TIME +100), "/");
            Lib::error("JWT 사용 가능 시간 전");
        } catch (\Exception $e) {
            setcookie("jd_jwt_token", "", time() - (Config::COOKIE_TIME +100), "/");
            Lib::error("JWT 디코딩 오류: " . $e->getMessage());
        }
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
     * @param bool    $root_path  true일 경우 Config::$ROOT 경로를 앞에 자동으로 붙임
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

    public static function deleteDir($path) {
        if($path == "") {
            Lib::error("Jl deleteDir() : 삭제 할려는 폴더가 빈값입니다.");
        }
        if($path == Config::$ROOT) {
            Lib::error("Jl deleteDir() : 삭제 할려는 폴더가 루트 디렉토리입니다.");
        }
        if(strpos($path,Config::$ROOT) !== false) $dir = $path;
        else $dir = Config::$ROOT.$path;


        if (!file_exists($dir)) {
            Lib::error("Jl deleteDir() : 삭제 할려는 폴더가 존재하지 않습니다.");
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $filePath = $dir."/".$file;

            if (is_dir($filePath)) {
                //$this->deleteDir($filePath); // 해당부분은 너무 위험해서 주석처리
                Lib::error("Jl deleteDir() : 삭제 할려는 폴더안에 폴더가 또 있습니다 폴더부터 지운후 진행해주세요.");
            } else {
                unlink($filePath);
            }
        }
        rmdir($dir);
    }

    public static function generateUniqueId() {
        return 'P-' . uniqid() . str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT);
    }

    public static function getPermission($path) {
        if (strpos($path, Config::$ROOT) === false) {
            $path = Config::$ROOT . $path;
        }

        $permissions = fileperms($path);

        if ($permissions === false) {
            Lib::error("getPermission() : 권한을 확인할 수 없습니다. 경로가 올바른지 확인하세요.");
        }

        // 권한 비트를 추출하여 8진수 문자열로 변환
        return substr(sprintf('%o', $permissions & 0777), -4); // 4자리 8진수 문자열 반환
    }

    public static function encrypt($value) {
        switch (Config::ENCRYPT) {
            case 'sha256':
                return hash('sha256', $value);
            case 'sha512':
                return hash('sha512', $value);
            case 'hmac':
                $secret = Config::PASSWORD;
                return hash_hmac('sha256', $value, $secret);
            case 'md5' :
                return md5($value);
            case 'gnuboard' :
                return get_encrypt_string($value);
            default:
                Lib::error("ENCRYPT 설정이 없습니다");
        }
    }

    public static function encryptAPI($value) {
        $key = substr(hash('sha256', Config::USERNAME), 0, 32); // 32바이트 = AES-256 키
        $iv  = substr(hash('sha256', Config::PASSWORD), 0, 16); // 16바이트 = IV

        return base64_encode(openssl_encrypt(Lib::jsonEncode($value), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv));
    }

    public static function js_obfuscate($code) {
        $encoded = '';
        foreach (str_split($code) as $c) {
            $encoded .= 'String.fromCharCode('.ord($c).')+';
        }
        return 'eval(' . rtrim($encoded, '+') . ');';
    }

    public static function curlRequest($url, $method = 'GET', $options = array()) {
        $ch = curl_init();

        // 옵션 기본값 설정
        $data = isset($options['data']) ? $options['data'] : null;
        $timeout = isset($options['timeout']) ? $options['timeout'] : 10;
        $http_build = isset($options['http_build']) ? $options['http_build'] : false;
        $content_type = isset($options['content_type']) ? $options['content_type'] : 'Content-Type: application/json';
        $accept = isset($options['accept']) ? $options['accept'] : 'Accept: application/json';

        // Content-Type 헤더 설정
        $headers = array($content_type,$accept);
        if($options['authorization']) array_push($headers,$options['authorization']);

        // 요청 메서드 설정
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        // 데이터 설정
        if ($data !== null) {
            $postData = $http_build ? http_build_query($data) : json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        // URL 설정
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // 요청 실행
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if($httpCode != "200") {
            self::error("curl 통신 실패($httpCode) : \nerror : $error\nreponse : $response");
        }

        return json_decode($response,true);
    }

    public static function formatPhoneNumber($phone) {
        // 숫자만 남기기 (+, -, 공백 등 제거)
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // +82 국가 코드 처리
        if (preg_match('/^82(10|1[1-9])/', $phone)) {
            $phone = '0' . substr($phone, 2); // 8210XXXXYYYY -> 010XXXXYYYY
        }

        // 010-XXXX-XXXX 형식으로 변환 (010, 011, 016, 017, 018, 019 지원)
        if (preg_match('/^(01[016789])(\d{4})(\d{4})$/', $phone, $matches)) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }

        return $phone; // 변환되지 않는 경우 원본 유지
    }

    public static function goURL($url)
    {
        // &amp; 를 & 로 변경
        $url = str_replace("&amp;", "&", $url);

        // 헤더가 전송되지 않았다면 HTTP 리다이렉트
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        }

        // 헤더가 이미 전송된 경우 JavaScript & meta refresh 사용
        echo '<script>';
        echo 'window.location.href = "'.$url.'";';
        echo '</script>';

        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
        exit;
    }

    public static function snsLogin($user,$table = "") {
        if(!$table) Lib::error("snsLogin() : 테이블명이 없습니다.");
        $model = new Model($table);
        $row = $model->where("sns_code",$user['primary'])->get();

        if(!$row['count']) {
            $data = array(
                "mb_id" => self::generateUniqueId(),
                "mb_password" => self::encrypt(self::generateUniqueId()),
                "mb_name" => $user['name'],
                "mb_email" => $user['email'],
                "mb_level" => 2,
                "mb_hp" => $user['phone'],
                "mb_datetime" => "now()",
                "sns_code" => $user['primary'],
            );

            $model->insert($data);

            $row = $model->where("sns_code",$user['primary'])->get();
        }

        self::userLogin($row['data'][0]);
    }

    public static function userLogin($user) {
        Session::set('ss_mb_id', $user['mb_id']);
        Session::set('ss_mb_key', md5($user['mb_datetime'] . self::getClientIP() . $_SERVER['HTTP_USER_AGENT']));


    }

}
