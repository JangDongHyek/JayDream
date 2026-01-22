<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use JayDream\Http;

class Cloudflare {
    public static $api_key;
    public static $account_id;

    public static function init() {
        $config = require __DIR__ . '/config.php';
        self::$api_key = $config['api_key'];
        self::$account_id = $config['account_id'];
    }

    public static function saveImage($file) {
        Http::url("https://api.cloudflare.com/client/v4/accounts/" . self::$account_id . "/images/v1");
        Http::header("Authorization", "Bearer ".self::$api_key);
        Http::file($file);
        $response = Http::post();

        return $response;
    }

    public static function deleteImage($imageId) {
        $url = "https://api.cloudflare.com/client/v4/accounts/" . self::$account_id . "/images/v1/" . $imageId;

        Http::url($url);
        Http::header("Authorization", "Bearer " . self::$api_key);
        $response = Http::delete();

        return $response;
    }
}