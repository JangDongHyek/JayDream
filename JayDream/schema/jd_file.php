<?php

return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],
    "user_idx" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => true,
        "comment" => "유저 고유값"
    ],
    "save_position" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => true,
        "comment" => "유저 고유값"
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
    "height" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "이미지의 높이"
    ],
    "width" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "이미지의 너비"
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
    "cloudflare_image_id" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => true,
        "comment" => "클라우드플레어 이미지저장 서비스 고유 아이디"
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