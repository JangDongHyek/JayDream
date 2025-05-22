<?php

return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],
    "table_name" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => false,
        "comment" => "테이블명"
    ],
    "table_primary" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => false,
        "comment" => "테이블의 고유값"
    ],
    "keyword" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "키워드"
    ],
    "name" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "파일명"
    ],
    "size" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "파일사이즈(Byte)"
    ],
    "ext" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => false,
        "comment" => "파일확장자"
    ],
    "src" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "src사용시 사용하는 필드"
    ],
    "path" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "저장된 파일 경로"
    ],
    "rename" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "변경된 파일 명"
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