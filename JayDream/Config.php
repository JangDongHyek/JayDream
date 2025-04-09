<?php

namespace JayDream;

use JayDream\Lib;
use JayDream\Model;

class Config
{
    public static $DEV = false;
    private static $DEV_IPS = ["121.140.204.65", "112.160.220.208"];
    public static $ROOT = "";
    public static $URL = "";
    public static $connect = null;


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

    public static function init()
    {
        // DB 체크
        self::initConnect();

        // 개발환경체크
        if (in_array(Lib::getClientIP(), self::$DEV_IPS)) self::$DEV = true;

        // 루트 및 URL 설정
        self::$ROOT = dirname(__DIR__);
        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
        $user = str_replace(str_replace(self::$ROOT, '', $_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_NAME']);
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/:[0-9]+$/', $host))
            $host = preg_replace('/:[0-9]+$/', '', $host);
        self::$URL = $http . $host . $user;

        if(Lib::getPermission(self::$ROOT."/JayDream") != "777") Lib::error("JayDream 폴더가 777이 아닙니다.");

        // 파일관련 테이블 생성
        if (!self::existsTable("jd_file")) {
            $schema = require __DIR__ . '/schema/jd_file.php';
            self::createTableFromSchema("jd_file",$schema);
        }
    }

    public static function resourcePath()
    {
        return self::$ROOT . '/JayDream/resource';
    }

    private static function initConnect()
    {
        if (self::DATABASE == "exam") Lib::error("DB 정보를 입력해주세요.");
        if (self::USERNAME == "exam") Lib::error("DB 정보를 입력해주세요.");
        if (self::PASSWORD == "password") Lib::error("DB 정보를 입력해주세요.");

        if (!self::$connect) {
            self::$connect = new \mysqli(
                self::HOSTNAME,
                self::USERNAME,
                self::PASSWORD,
                self::DATABASE
            );

            if (self::$connect->connect_error) {
                Lib::error("❌ DB 연결 실패: " . self::$connect->connect_error);
            }
        }
    }

    private static function existsTable($tableName)
    {
        $escapedTable = self::$connect->real_escape_string($tableName);
        $result = self::$connect->query("SHOW TABLES LIKE '{$escapedTable}'");

        return $result && $result->num_rows > 0;
    }

    private static function createTableFromSchema($tableName, $schema)
    {
        $columns = [];
        $primaryKey = '';

        foreach ($schema as $name => $info) {
            if ($name === 'primary') {
                $primaryKey = $info;
                continue;
            }

            $line = "`{$name}` {$info['type']}";

            // 길이 적용
            if (isset($info['length']) && in_array(strtoupper($info['type']), ['VARCHAR', 'CHAR'])) {
                $line .= "({$info['length']})";
            }

            // NULL 여부
            $line .= (isset($info['nullable']) && $info['nullable'] === false) ? " NOT NULL" : " NULL";

            // 기본값 처리
            if (isset($info['default'])) {
                $default = is_numeric($info['default']) ? $info['default'] : "'" . self::$connect->real_escape_string($info['default']) . "'";
                $line .= " DEFAULT {$default}";
            }

            // AUTO_INCREMENT 붙이기 (프라이머리 + INT 타입)
            if (
                isset($schema['primary']) &&
                $schema['primary'] === $name &&
                strtoupper($info['type']) === 'INT'
            ) {
                $line .= " AUTO_INCREMENT";
            }

            // 주석 처리
            if (isset($info['comment'])) {
                $comment = self::$connect->real_escape_string($info['comment']);
                $line .= " COMMENT '{$comment}'";
            }

            $columns[] = $line;
        }

        // 프라이머리 키
        if (!empty($primaryKey)) {
            $columns[] = "PRIMARY KEY (`{$primaryKey}`)";
        }

        $columnsSQL = implode(",\n    ", $columns);
        $createSQL = "CREATE TABLE `{$tableName}` (\n    {$columnsSQL}\n) DEFAULT CHARSET=utf8mb4;";

        if (!self::$connect->query($createSQL)) {
            Lib::error("{$tableName} 테이블 생성에 실패했습니다 : " . self::$connect->error);
        }
    }
}

