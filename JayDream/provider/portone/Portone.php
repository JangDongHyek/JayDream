<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class Portone {
    public static $dev;
    public static $store_id;
    public static $secret_key;
    public static $channel_key;
    public static $mid;
    public static $v1_code;
    public static $v1_api_key;
    public static $v1_api_secret;
    public static function init() {
        $config = require __DIR__ . '/config.php';

        self::$dev = $config['dev'];
        self::$store_id = $config['store_id'];
        self::$secret_key = $config['secret_key'];
        self::$channel_key = self::$dev ? $config['test_channel_key'] : $config['channel_key'];
        self::$mid = self::$dev ? $config['test_mid'] : $config['mid'];
        self::$v1_code = $config['v1_code'];
        self::$v1_api_key = $config['v1_api_key'];
        self::$v1_api_secret = $config['v1_api_secret'];

        if(!self::$channel_key) Lib::error("channel_key 값이 없습니다.");
        if(!self::$mid) Lib::error("mid 값이 없습니다.");

        if($config['version'] === 'v1') {
            if(!self::$v1_code) Lib::error("v1_code 값이 없습니다.");
            if(!self::$v1_api_key) Lib::error("v1_api_key 값이 없습니다.");
            if(!self::$v1_api_secret) Lib::error("v1_api_secret 값이 없습니다.");
        }else if($config['version'] === 'v2') {
            if(!self::$store_id) Lib::error("store_id 값이 없습니다.");
            if(!self::$secret_key) Lib::error("secret_key 값이 없습니다.");
        }

    }

    public static function getOrder($payment_id) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.portone.io/payments/' . urlencode($payment_id));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: PortOne ' . self::$secret_key,
            'Content-Type: application/json'
        ]);

        $paymentResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Lib::error('paymentResponse: ' . $paymentResponse);
        }

        $payment = json_decode($paymentResponse, true);

        return $payment;
    }

    public static function parseOrder($payment) {
        return [
            "status"            => $payment['status'] ?? '',
            "id"                => $payment['id'] ?? '',
            "transaction_id"    => $payment['transactionId'] ?? '',
            "merchant_id"       => $payment['merchantId'] ?? '',
            "store_id"          => $payment['storeId'] ?? '',
            "method"            => isset($payment['method']) ? json_encode($payment['method'], JSON_UNESCAPED_UNICODE) : null,
            "channel"           => isset($payment['channel']) ? json_encode($payment['channel'], JSON_UNESCAPED_UNICODE) : null,
            "version"           => isset($payment['version']) ? json_encode($payment['version'], JSON_UNESCAPED_UNICODE) : null,
            "webhooks"          => isset($payment['webhooks']) ? json_encode($payment['webhooks'], JSON_UNESCAPED_UNICODE) : null,
            "customer"          => isset($payment['customer']) ? json_encode($payment['customer'], JSON_UNESCAPED_UNICODE) : null,
            "requested_at"      => self::toDatetime($payment['requestedAt'] ?? null),
            "updated_at"        => self::toDatetime($payment['updatedAt'] ?? null),
            "status_changed_at" => self::toDatetime($payment['statusChangedAt'] ?? null),
            "order_name"        => $payment['orderName'] ?? null,
            "product"           => null, // 호출 시 별도 세팅
            "amount_total"              => $payment['amount']['total'] ?? 0,
            "amount_tax_free"           => $payment['amount']['taxFree'] ?? null,
            "amount_vat"                => $payment['amount']['vat'] ?? null,
            "amount_supply"             => $payment['amount']['supply'] ?? null,
            "amount_discount"           => $payment['amount']['discount'] ?? null,
            "amount_paid"               => $payment['amount']['paid'] ?? null,
            "amount_cancelled"          => $payment['amount']['cancelled'] ?? null,
            "amount_cancelled_tax_free" => $payment['amount']['cancelledTaxFree'] ?? null,
            "currency"          => $payment['currency'] ?? null,
            "promotion_id"      => $payment['promotionId'] ?? null,
            "is_cultural_expense" => isset($payment['isCulturalExpense'])
                ? ($payment['isCulturalExpense'] ? 1 : 0) : null,
            "paid_at"           => self::toDatetime($payment['paidAt'] ?? null),
            "pg_tx_id"          => $payment['pgTxId'] ?? null,
            "pg_response"       => $payment['pgResponse'] ?? null,
            "receipt_url"       => $payment['receiptUrl'] ?? null,
            "cancellations"     => !empty($payment['cancellations'])
                ? json_encode($payment['cancellations'], JSON_UNESCAPED_UNICODE) : null,
            "cancelled_at"      => self::toDatetime($payment['cancelledAt'] ?? null),
        ];
    }

    private static function toDatetime($iso) {
        if (!$iso) return null;
        try {
            return (new \DateTime($iso))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}