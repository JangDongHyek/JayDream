<?php

return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],
    "table_name" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "테이블명"
    ],
    "table_primary" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => false,
        "comment" => "테이블의 고유컬럼명"
    ],
    "MgtNum" => [
        "type" => "VARCHAR",
        "length" => 24,
        "nullable" => false,
        "comment" => "세금계산서 관리번호"
    ],
    "insert_date" => [
        "type" => "DATETIME",
        "nullable" => false,
        "comment" => "등록일"
    ],
    "update_date" => [
        "type" => "DATETIME",
        "nullable" => false,
        "comment" => "수정일"
    ],
    "primary" => "idx"
];
