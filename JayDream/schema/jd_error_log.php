<?php
return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],

    "message" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "에러 메시지"
    ],

    "file" => [
        "type" => "VARCHAR",
        "length" => 500,
        "nullable" => true,
        "comment" => "에러 발생 파일 경로"
    ],

    "line" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "에러 발생 라인"
    ],

    "url" => [
        "type" => "VARCHAR",
        "length" => 500,
        "nullable" => true,
        "comment" => "에러 발생 URL"
    ],

    "ip" => [
        "type" => "VARCHAR",
        "length" => 45,
        "nullable" => true,
        "comment" => "접속 IP"
    ],

    "session" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "세션 정보 (JSON)"
    ],

    "insert_date" => [
        "type" => "DATETIME",
        "nullable" => false,
        "comment" => "발생일시"
    ],

    "primary" => "idx"
];