<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use JayDream\Http;

class HostingKr {
    public static $api_key;

    public static function init() {
        $config = require __DIR__ . '/config.php';
        self::$api_key = $config['api_key'];
    }

    public static function checkDomain($obj) {
        Http::url("https://resellerapi.hosting.kr/v1/domains/available");
        Http::header("Accept","application/json");
        Http::header("X-Api-Key",self::$api_key);
        Http::query($obj);
        $response = Http::get();

        if(!$response['success']) Lib::error($response['error']);

        return $response['data'];
    }

    public static function getDomain($domain) {
        Http::url("https://resellerapi.hosting.kr/v1/domains/$domain");
        Http::header("Accept","application/json");
        Http::header("X-Api-Key",self::$api_key);

        $response = Http::get();

        if($response['success']) {
            $response['data']['createdAt'] = date('Y-m-d H:i:s',strtotime($response['data']['createdAt']));
            $response['data']['deletedAt'] = date('Y-m-d H:i:s',strtotime($response['data']['deletedAt']));
            $response['data']['transferAwayEligibleAt'] = date('Y-m-d H:i:s',strtotime($response['data']['transferAwayEligibleAt']));
            $response['data']['expires'] = date('Y-m-d H:i:s',strtotime($response['data']['expires']));
            $response['data']['renewDeadline'] = date('Y-m-d H:i:s',strtotime($response['data']['renewDeadline']));
        }
        return $response;
    }

    public static function buyDomain($obj) {
        $requestData = [
            'domain' => $obj['domain'],
            'contactAdmin' => $obj['contactAdmin'],
            'contactRegistrant' => self::toIntlContact($obj['contactRegistrant']),
            'nameServers' => $obj['nameServers'],
            'period' => $obj['period'],
            'privacy' => $obj['privacy'],
            'exposeKrWhois' => $obj['exposeKrWhois'],
            'businessNumber' => null,
        ];

        //var_dump($requestData);

        Http::url("https://resellerapi.hosting.kr/v1/domains/purchase");
        Http::header("Accept","application/json");
        Http::header("Content-Type","application/json");
        Http::header("X-Api-Key",self::$api_key);
        Http::json($requestData);
        $response = Http::post();
        return $response;
    }

    public static function toIntlContact($content) {
        if (isset($content['phone'])) {
            $content['phone'] = '+82-' . str_replace('-', '', substr($content['phone'], 1));
        }

        if (isset($content['mobile'])) {
            $content['mobile'] = '+82-' . str_replace('-', '', substr($content['mobile'], 1));
        }

        return $content;
    }
}