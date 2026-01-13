<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use phpbrowscap\Exception;

class BaroBill {
    private static $DEV = true;

    private static $init_check = false;
    private static $CERTKEY;
    private static $info;
    private static $URL;
    private static $error_codes;


    public static function init() {
        $config = require __DIR__ . '/config.php';
        self::$CERTKEY = $config['CERTKEY'];
        self::$info = $config['info'];
        self::$error_codes = $config['error_codes'];

        if(self::$DEV) self::$URL = "https://testws.baroservice.com";
        else self::$URL = "https://ws.baroservice.com";

        self::$init_check = true;
    }

    public static function CheckCorpIsMember($obj) {
        if(!self::$init_check) Lib::error("초기화를 진행해주세요.");

        $url = self::$URL . "/TI.asmx?WSDL";

        $soap = new \SoapClient($url, array(
            'trace' => 'true',
            'encoding' => 'UTF-8' //소스를 ANSI로 사용할 경우 euc-kr로 수정
        ));

        $Result = $soap->CheckCorpIsMember([
            'CERTKEY' => self::$CERTKEY,
            'CorpNum' => self::$info['CorpNum'],
            'CheckCorpNum' => $obj['CheckCorpNum'],
        ])->CheckCorpIsMemberResult;

        if ($Result < 0) { // 호출 실패
            Lib::error(self::getErrorMessage($Result));
        } else { // 호출 성공
            return [
                "success" => true,
                "result" => $Result
            ];
        }
    }

    public static function RegistCorp($obj) {
        if(!self::$init_check) Lib::error("초기화를 진행해주세요.");

        $url = self::$URL . "/TI.asmx?WSDL";

        $soap = new \SoapClient($url, array(
            'trace' => 'true',
            'encoding' => 'UTF-8' //소스를 ANSI로 사용할 경우 euc-kr로 수정
        ));

        $Result = $soap->RegistCorp([
            'CERTKEY' => self::$CERTKEY,
            'CorpNum' => $obj['CorpNum'],
            'CorpName' => $obj['CorpName'],
            'CEOName' => $obj['CEOName'],
            'BizType' => $obj['BizType'],
            'BizClass' => $obj['BizClass'],
            'PostNum' => "",
            'Addr1' => $obj['Addr1'],
            'Addr2' => $obj['Addr2'],
            'MemberName' => $obj['MemberName'],
            'JuminNum' => "",
            'ID' => $obj['ID'],
            'PWD' => $obj['PWD'],
            'Grade' => $obj['Grade'],
            'TEL' => $obj['TEL'],
            'HP' => $obj['HP'],
            'Email' => $obj['Email'],
        ])->RegistCorpResult;

        if ($Result < 0) { // 호출 실패
            Lib::error(self::getErrorMessage($Result));
        } else { // 호출 성공
            return [
                "success" => true,
                "result" => $Result
            ];
        }
    }

    public static function RegistAndIssueTaxInvoice($obj,$order) {
        if(!self::$init_check) Lib::error("초기화를 진행해주세요.");

        $url = self::$URL . "/TI.asmx?WSDL";

        $soap = new \SoapClient($url, array(
            'trace' => 'true',
            'encoding' => 'UTF-8' //소스를 ANSI로 사용할 경우 euc-kr로 수정
        ));

        $info = self::$info;
        $info['MgtNum'] = Lib::generateUniqueId();

        $TaxInvoice = array(
            'InvoicerParty'     => $info,
            'InvoiceeParty'     => $obj,
            'TaxCalcType'       => 1, // [필수] 고정값
            'IssueDirection'    => 1, // [필수] 발급방향 (1: 정발급, 2: 역발행)
            'TaxInvoiceType'    => 1, // [필수] 세금계산서 형태 (1: 세금계산서, 2: 계산서, 4: 위수탁세금계산서, 5: 위수탁계산서)
            'TaxType'           => 1, // [필수] 과세형태 (1: 과세, 2: 영세, 3: 면세)
            'PurposeType'       => 2, // [필수] 영수/청구형태 (1: 영수, 2: 청구)
            'WriteDate'         => '', // [필수] 작성일자 (YYYYMMDD, 미입력 시 기본값은 오늘 날짜)
            'AmountTotal'       => $order['AmountTotal'], // [필수] 공급가액 (숫자만, 소수점X, 컴마X)
            'TaxTotal'          => $order['TaxTotal'], // [필수] 세액 (숫자만, 소수점X, 컴마X)
            'TotalAmount'       => $order['TotalAmount'], // [필수] 합계금액 (숫자만, 소수점X, 컴마X)

            'TaxInvoiceTradeLineItems' => [
                'TaxInvoiceTradeLineItem' => [
                    [
                        'PurchaseExpiry' => '',
                        'Name' => '',
                        'Information' => '',
                        'ChargeableUnit' => '',
                        'UnitPrice' => '',
                        'Amount' => '',
                        'Tax' => '',
                        'Description' => '',
                    ],
                    [
                        'PurchaseExpiry' => '',
                        'Name' => '',
                        'Information' => '',
                        'ChargeableUnit' => '',
                        'UnitPrice' => '',
                        'Amount' => '',
                        'Tax' => '',
                        'Description' => '',
                    ]
                ]
            ]
        );

        try {
            $Result = $soap->RegistAndIssueTaxInvoice([
                'CERTKEY' => self::$CERTKEY,
                'CorpNum' => $info['CorpNum'],
                'Invoice' => $TaxInvoice,
                'SendSMS' => false,         //문자메세지 전송여부
                'ForceIssue' => false,      //가산세 발생이 예상되는 경우에도 발급할지 여부
                'MailTitle' => '',
            ])->RegistAndIssueTaxInvoiceResult;
        }catch (\Exception $e) {
            Lib::error($e->getMessage());
        }



        if ($Result < 0) { // 호출 실패
            Lib::error(self::getErrorMessage($Result));
        } else { // 호출 성공
            return [
                "success" => true,
                "result" => $Result,
                "MgtNum" => $info['MgtNum'],
            ];
        }
    }

    public static function getErrorMessage($code) {
        $code = (string) $code;
        return self::$error_codes[$code] ? self::$error_codes[$code] : "$code 정의되지 않은 오류입니다.";
    }
}