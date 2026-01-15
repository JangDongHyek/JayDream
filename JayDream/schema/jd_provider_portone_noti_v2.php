<?php
return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],
    "tx_id" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "거래 식별 번호"
    ],
    "payment_id" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "주문 번호"
    ],
    "status" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "거래 상태값"
    ],
    "insert_date" => [
        "type" => "DATETIME",
        "nullable" => false,
        "comment" => "등록일"
    ],
    "primary" => "idx"
];