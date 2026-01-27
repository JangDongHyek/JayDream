<?php

namespace JayDream;

use JayDream\Lib;

class Config
{
    public static $VERSION = "";
    public static $DEV = false;
    public static $ROOT = "";
    public static $URL = "";
    public static $DOMAIN = "";
    public static $connect = null;
    public static $framework = "";

    //_env.php 에 설정하는 변수
    public static $DEV_IPS;
    public static $HOSTNAME;
    public static $DATABASE;
    public static $USERNAME;
    public static $PASSWORD;
    public static $COOKIE_TIME;
    public static $ALERT;
    public static $ENCRYPT;
    public static $Cloudflare_image_server;
    public static $JS_image_resizing;

    public static $ci3_config;


    public static function init()
    {
        // 사용자 입력 변수 할당
        $envFile = __DIR__ . '/_env.php';
        if (!file_exists($envFile)) Lib::error("_env.php 파일이 존재하지않습니다.");
        $env = require $envFile;
        self::$VERSION = $env['VERSION'];
        self::$DEV_IPS = $env['DEV_IPS'];
        self::$HOSTNAME = $env['HOSTNAME'];
        self::$DATABASE = $env['DATABASE'];
        self::$USERNAME = $env['USERNAME'];
        self::$PASSWORD = $env['PASSWORD'];
        self::$COOKIE_TIME = $env['COOKIE_TIME'];
        self::$ALERT = $env['ALERT'];
        self::$ENCRYPT = $env['ENCRYPT'];
        self::$Cloudflare_image_server = $env['Cloudflare_image_server'];
        self::$JS_image_resizing = $env['JS_image_resizing'];


        // 개발환경체크
        if (in_array(Lib::getClientIP(), self::$DEV_IPS)) self::$DEV = true;

        // DB 체크
        self::initConnect();

        self::$ROOT = dirname(__DIR__);
        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/:[0-9]+$/', $host))
            $host = preg_replace('/:[0-9]+$/', '', $host);
        self::$URL = $http . $host . '/';

        // 도메인 구하기
        self::$DOMAIN = preg_replace('/^www\./i', '', parse_url(self::$URL, PHP_URL_HOST));

        // 프레임워크 환경 체크
        self::getFramework();



        //폴더 권한체크
        //if(Lib::getPermission(self::$ROOT."/JayDream") != "777") Lib::error("JayDream 폴더가 777이 아닙니다.");

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
        if (self::$DATABASE == "exam") Lib::error("DB 정보를 입력해주세요.");
        if (self::$USERNAME == "exam") Lib::error("DB 정보를 입력해주세요.");
        if (self::$PASSWORD == "password") Lib::error("DB 정보를 입력해주세요.");

        if (!self::$connect) {
            self::$connect = new \mysqli(
                self::$HOSTNAME,
                self::$USERNAME,
                self::$PASSWORD,
                self::$DATABASE
            );

            if (self::$connect->connect_error) {
                Lib::error("❌ DB 연결 실패: " . self::$connect->connect_error);
            }

            $support_utf8mb4 = false;
            $result = self::$connect->query("SHOW CHARACTER SET LIKE 'utf8mb4'");
            if ($result && $result->num_rows > 0) {
                $support_utf8mb4 = true;
            }

            if ($support_utf8mb4) {
                $charset = "utf8mb4";
                $collation = "utf8mb4_general_ci";
            } else {
                $charset = "utf8";
                $collation = "utf8_general_ci";
            }

            self::$connect->set_charset($charset);

            self::$connect->query("SET NAMES {$charset} COLLATE {$collation}");
            self::$connect->query("SET CHARACTER SET {$charset}");
            self::$connect->query("SET collation_connection = '{$collation}'");
        }
    }

    public static function existsTable($tableName)
    {
        $escapedTable = self::$connect->real_escape_string($tableName);
        $result = self::$connect->query("SHOW TABLES LIKE '{$escapedTable}'");

        return $result && $result->num_rows > 0;
    }

    public static function createTableFromSchema($tableName, $schema)
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
        $createSQL = "CREATE TABLE `{$tableName}` (\n    {$columnsSQL}\n) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

        if (!self::$connect->query($createSQL)) {
            Lib::error("{$tableName} 테이블 생성에 실패했습니다 : " . self::$connect->error);
        }
    }

    public static function getFramework()
    {
        if (self::$framework) return self::$framework;

        // CodeIgniter 4 감지 (폴더 구조)
        if (
            file_exists(self::$ROOT . '/app/Config/App.php') ||
            (is_dir(self::$ROOT . '/app') && file_exists(self::$ROOT . '/spark'))
        ) {
            self::$framework = 'ci4';
        }

        // CodeIgniter 3 감지 (application 폴더 구조로 확인)
        elseif (
            file_exists(self::$ROOT . '/application/config/config.php') ||
            (is_dir(self::$ROOT . '/application') && file_exists(self::$ROOT . '/index.php'))
        ) {
            self::$framework = 'ci3';
            self::loadCI3Config(); // 추가
        }

        // GNUBoard 감지 (common.php 없이도 구조만 보고 판단)
        elseif (
            file_exists(self::$ROOT . '/common.php') &&
            file_exists(self::$ROOT . '/bbs/board.php')
        ) {
            self::$framework = 'gnuboard';
        }

        // 기본 레거시 환경
        else {
            self::$framework = 'legacy';
        }

        return self::$framework;
    }

    private static function loadCI3Config()
    {
        $config_path = self::$ROOT . '/application/config/config.php';
        if (file_exists($config_path)) {
            // BASEPATH 정의해서 CI3 config 파일 접근 허용
            if (!defined('BASEPATH')) {
                define('BASEPATH', self::$ROOT . '/system/');
            }

            $config = [];
            include $config_path;
            self::$ci3_config = $config;
        }
    }
}

