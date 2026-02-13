<?php
return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],
    "status" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => false,
        "comment" => "결제 상태 (PAID, CANCELLED, READY 등)"
    ],
    "id" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => false,
        "comment" => "포트원 결제 ID"
    ],
    "transaction_id" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => false,
        "comment" => "거래 고유 ID (tx_id)"
    ],
    "merchant_id" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => false,
        "comment" => "상점 ID"
    ],
    "store_id" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => false,
        "comment" => "스토어 ID"
    ],
    "method" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "결제 수단 상세 (JSON)"
    ],
    "channel" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "채널 상세 (JSON)"
    ],
    "version" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "API 버전 (JSON)"
    ],
    "webhooks" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "웹훅 내역 (JSON)"
    ],
    "customer" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "고객 정보 (JSON)"
    ],
    "requested_at" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "결제 요청 시각"
    ],
    "updated_at" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "최종 업데이트 시각"
    ],
    "status_changed_at" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "상태 변경 시각"
    ],
    "order_name" => [
        "type" => "VARCHAR",
        "length" => 255,
        "nullable" => true,
        "comment" => "주문명"
    ],
    "product" => [
        "type" => "VARCHAR",
        "length" => 100,
        "nullable" => true,
        "comment" => "결제 상품 구분"
    ],
    // amount
    "amount_total" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "총 결제 금액"
    ],
    "amount_tax_free" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "면세 금액"
    ],
    "amount_vat" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "부가세"
    ],
    "amount_supply" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "공급가액"
    ],
    "amount_discount" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "할인 금액"
    ],
    "amount_paid" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "실 결제 금액"
    ],
    "amount_cancelled" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "취소 금액"
    ],
    "amount_cancelled_tax_free" => [
        "type" => "INT",
        "nullable" => true,
        "comment" => "취소 면세 금액"
    ],
    "currency" => [
        "type" => "VARCHAR",
        "length" => 5,
        "nullable" => true,
        "comment" => "통화 (KRW)"
    ],
    "promotion_id" => [
        "type" => "VARCHAR",
        "length" => 50,
        "nullable" => true,
        "comment" => "프로모션 ID"
    ],
    "is_cultural_expense" => [
        "type" => "TINYINT",
        "length" => 1,
        "nullable" => true,
        "comment" => "문화비 지출 여부 (0/1)"
    ],
    "paid_at" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "결제 완료 시각"
    ],
    "pg_tx_id" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => true,
        "comment" => "PG 거래 번호"
    ],
    "pg_response" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "PG 응답 원본 (JSON)"
    ],
    "receipt_url" => [
        "type" => "VARCHAR",
        "length" => 500,
        "nullable" => true,
        "comment" => "영수증 URL"
    ],
    "cancellations" => [
        "type" => "TEXT",
        "nullable" => true,
        "comment" => "취소 내역 (JSON)"
    ],
    "cancelled_at" => [
        "type" => "DATETIME",
        "nullable" => true,
        "comment" => "최종 취소 시각"
    ],
    "insert_date" => [
        "type" => "DATETIME",
        "nullable" => false,
        "comment" => "등록일"
    ],
    "update_date" => [
        "type" => "DATETIME",
        "nullable" => false,
        "comment" => "등록일"
    ],
    "primary" => "idx"
];