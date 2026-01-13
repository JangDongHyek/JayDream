<?php

return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],
    "mid" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "가맹점 ID"
    ],
    "tid" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "거래 식별 번호"
    ],
    "otid" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "원거래 ID"
    ],
    "aid" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "가맹점 AID"
    ],
    "gid" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "가맹점 GID"
    ],
    "moid" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "상품 주문 번호"
    ],
    "payMethod" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "지불 수단"
    ],
    "amt" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "거래금액(과세금액)"
    ],
    "taxFreeAmt" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "면세 금액"
    ],
    "status" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "거래 상태"
    ],
    "authNum" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "승인 번호"
    ],
    "approvedAt" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "승인 날짜"
    ],
    "transactionAt" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "거래 처리 된 날짜"
    ],
    "receiptUrl" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "영수증 URL"
    ],
    "etc" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "여분 필드 정보"
    ],
    "goods" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "상품 정보"
    ],
    "buyer" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "구매자 정보"
    ],
    "card" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "카드 결제일 경우, 카드 관련 데이터"
    ],
    "epay" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "간편 결제일 경우, 간편 결제 관련 데이터"
    ],
    "bank" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "계좌 이체일 경우, 계좌 이체 관련 데이터"
    ],
    "easyBank" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "계좌 간편결제일 경우, 계좌 간편 결제 관련 데이터"
    ],
    "virtualAccount" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "가상계좌 일 경우, 가상게좌 관련 데이터"
    ],
    "cashReceipt" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "현금 영수증 발행 체크할 경우, 현금 영수증 관련 데이터"
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
