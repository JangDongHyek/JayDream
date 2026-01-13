<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Portone {
    public static $store_id;
    public static $channel_key;
    public static function init() {
        $config = require __DIR__ . '/config.php';

        if(!$config['store_id']) Lib::error("store_id 값이 없습니다.");
        if(!$config['channel_key']) Lib::error("channel_key 값이 없습니다.");

        self::$store_id = $config['store_id'];
        self::$channel_key = $config['channel_key'];
    }
}