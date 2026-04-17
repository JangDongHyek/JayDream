<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use JayDream\Http;

class Freepik {
    public static $api_key;
    public static function init() {
        $config = require __DIR__ . '/config.php';
        self::$api_key = $config['api_key'];
    }

    public static function getResources($term = "") {
        Http::url("https://api.freepik.com/v1/resources");
        Http::query(array(
            "term" => $term,
            "order" => "recent"
        ));
        Http::header("x-freepik-api-key", self::$api_key);
        $response = Http::get();

        return $response;
    }
}
