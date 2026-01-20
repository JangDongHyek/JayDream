<?php
return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],

    "authCode" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "인증 코드"
    ],

    "contactAdmin" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "관리자 정보 (JSON)"
    ],

    "contactRegistrant" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "소유자 정보 (JSON)"
    ],

    "domain" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "도메인 명"
    ],

    "domainId" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => false,
        "comment" => "도메인 아이디"
    ],

    "status" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => false,
        "comment" => "상태 (Pending, Success, TransferOut, Deleted)"
    ],

    "createdAt" => [
        "type" => "DATETIME",
        "nullable" => false,
        "comment" => "등록일"
    ],

    "deletedAt" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "삭제일"
    ],

    "expires" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "만료일"
    ],

    "renewDeadline" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "도메인 기간 연장 가능 유효일"
    ],

    "transferAwayEligibleAt" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "기관 이전 가능 유효일"
    ],

    "expirationProtected" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => false,
        "comment" => "도메인 만료 보호 설정 여부"
    ],

    "holdRegistrar" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => false,
        "comment" => "레지스트라 잠금 설정 여부"
    ],

    "locked" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => false,
        "comment" => "도메인 잠금 설정 여부"
    ],

    "privacy" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => false,
        "comment" => "개인정보보호 설정 여부"
    ],

    "transferProtected" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => false,
        "comment" => "기관 이전 보호 설정 여부"
    ],

    "exposeKrWhois" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "도메인 정보 공개 여부 (.KR 도메인만 해당)"
    ],

    "nameServers" => [
        "type" => "TEXT",
        "nullable" => false,
        "comment" => "네임서버 목록 (JSON array)"
    ],

    "businessNumber" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => true,
        "comment" => "사업자 등록 번호"
    ],

    "primary" => "idx"
];