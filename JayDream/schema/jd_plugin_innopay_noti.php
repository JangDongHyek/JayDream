<?php

return [
    "idx" => [
        "type" => "INT",
        "nullable" => false,
        "comment" => "고유값"
    ],
    "shopCode" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "이노페이 상점아이디"
    ],
    "transSeq" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => true,
        "comment" => "거래번호"
    ],
    "userId" => [
        "type" => "VARCHAR",
        "length" => 60,
        "nullable" => true,
        "comment" => "사용자아이디"
    ],
    "userName" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => true,
        "comment" => "사용자이름"
    ],
    "userPhoneNo" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "사용자휴대폰번호"
    ],
    "moid" => [
        "type" => "VARCHAR",
        "length" => 60,
        "nullable" => true,
        "comment" => "주문번호"
    ],
    "goodsName" => [
        "type" => "VARCHAR",
        "length" => 75,
        "nullable" => true,
        "comment" => "상품명"
    ],
    "goodsAmt" => [
        "type" => "INT",
        "length" => null,
        "nullable" => true,
        "comment" => "상품금액"
    ],
    "buyerCode" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "구매자코드"
    ],
    "buyerName" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => true,
        "comment" => "구매자명"
    ],
    "buyerPhoneNo" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "구매자휴대폰번호"
    ],
    "pgCode" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "PG 코드"
    ],
    "pgName" => [
        "type" => "VARCHAR",
        "length" => 75,
        "nullable" => true,
        "comment" => "PG 명"
    ],
    "payMethod" => [
        "type" => "VARCHAR",
        "length" => 6,
        "nullable" => true,
        "comment" => "결제수단"
    ],
    "payMethodName" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => true,
        "comment" => "결제수단명"
    ],
    "pgMid" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "PG 아이디"
    ],
    "pgSid" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "PG 서비스아이디"
    ],
    "status" => [
        "type" => "VARCHAR",
        "length" => 2,
        "nullable" => true,
        "comment" => "거래상태"
    ],
    "statusName" => [
        "type" => "VARCHAR",
        "length" => 2,
        "nullable" => true,
        "comment" => "거래상태명"
    ],
    "pgResultCode" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "PG 결과코드"
    ],
    "pgResultMsg" => [
        "type" => "VARCHAR",
        "length" => 150,
        "nullable" => true,
        "comment" => "PG 결과메세지"
    ],
    "pgAppDate" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "PG 승인일자"
    ],
    "pgAppTime" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "PG 승인시간"
    ],
    "pgTid" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => true,
        "comment" => "PG 거래번호"
    ],
    "approvalAmt" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "승인금액"
    ],
    "approvalNo" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "승인번호"
    ],
    "stateCd" => [
        "type" => "VARCHAR",
        "length" => 1,
        "nullable" => true,
        "comment" => "거래상태값"
    ],
    "mallUserId" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "회원사고객 ID"
    ],
    "mallReserved" => [
        "type" => "VARCHAR",
        "length" => 100,
        "nullable" => true,
        "comment" => "상점예비정보"
    ],
    "svcPrdtCd" => [
        "type" => "VARCHAR",
        "length" => 2,
        "nullable" => true,
        "comment" => "결제구분"
    ],
    "offCatId" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "CATID"
    ],
    "currency" => [
        "type" => "VARCHAR",
        "length" => 3,
        "nullable" => true,
        "comment" => "통화코드"
    ],
    "cashReceiptType" => [
        "type" => "VARCHAR",
        "length" => 1,
        "nullable" => true,
        "comment" => "증빙구분"
    ],
    "cashReceiptNo" => [
        "type" => "INT",
        "length" => null,
        "nullable" => true,
        "comment" => "식별번호"
    ],
    "cashReceiptTypeName" => [
        "type" => "VARCHAR",
        "length" => 75,
        "nullable" => true,
        "comment" => "증빙구분명"
    ],
    "cashReceiptSupplyAmt" => [
        "type" => "INT",
        "length" => null,
        "nullable" => true,
        "comment" => "공급가"
    ],
    "cashReceiptVat" => [
        "type" => "INT",
        "length" => null,
        "nullable" => true,
        "comment" => "부가세"
    ],
    "cardNo" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "카드번호"
    ],
    "cardQuota" => [
        "type" => "VARCHAR",
        "length" => 2,
        "nullable" => true,
        "comment" => "할부개월"
    ],
    "cardIssueCode" => [
        "type" => "VARCHAR",
        "length" => 2,
        "nullable" => true,
        "comment" => "발급사코드"
    ],
    "cardIssueName" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "발급사명"
    ],
    "cardAcquireCode" => [
        "type" => "VARCHAR",
        "length" => 2,
        "nullable" => true,
        "comment" => "매입사코드"
    ],
    "cardAcquireName" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "매입사명"
    ],
    "cancelAmt" => [
        "type" => "INT",
        "length" => null,
        "nullable" => true,
        "comment" => "취소요청금액"
    ],
    "cancelMsg" => [
        "type" => "VARCHAR",
        "length" => 150,
        "nullable" => true,
        "comment" => "취소요청메세지"
    ],
    "cancelResultCode" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "취소결과코드"
    ],
    "cancelResultMsg" => [
        "type" => "VARCHAR",
        "length" => 150,
        "nullable" => true,
        "comment" => "취소결과메세지"
    ],
    "cancelAppDate" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "취소승인일자"
    ],
    "cancelAppTime" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "취소승인시간"
    ],
    "cancelPgTid" => [
        "type" => "VARCHAR",
        "length" => 30,
        "nullable" => true,
        "comment" => "PG 거래번호"
    ],
    "cancelApprovalAmt" => [
        "type" => "INT",
        "length" => null,
        "nullable" => true,
        "comment" => "승인금액"
    ],
    "cancelApprovalNo" => [
        "type" => "VARCHAR",
        "length" => 10,
        "nullable" => true,
        "comment" => "승인번호"
    ],
    "vacctNo" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "가상계좌번호"
    ],
    "vbankBankCd" => [
        "type" => "VARCHAR",
        "length" => 4,
        "nullable" => true,
        "comment" => "가상계좌은행코드"
    ],
    "vbankAcctNm" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "송금자명"
    ],
    "vbankRefundAcctNo" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "환불계좌번호"
    ],
    "vbankRefundBankCd" => [
        "type" => "VARCHAR",
        "length" => 4,
        "nullable" => true,
        "comment" => "환불은행코드"
    ],
    "vbankRefundAcctNm" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "환불계좌주명"
    ],
    "bankCd" => [
        "type" => "VARCHAR",
        "length" => 20,
        "nullable" => true,
        "comment" => "계좌번호"
    ],
    "accntNo" => [
        "type" => "VARCHAR",
        "length" => 4,
        "nullable" => true,
        "comment" => "은행코드"
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
    "primary" => "idx",
];