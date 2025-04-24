<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use phpbrowscap\Exception;

class BaroBill {
    private static $DEV = true;

    private static $init_check = false;
    private static $CERTKEY;
    private static $CorpNum;
    private static $URL;
    private static $error_codes;


    public static function init() {
        $config = require __DIR__ . '/config.php';
        self::$CERTKEY = $config['CERTKEY'];
        self::$CorpNum = $config['CorpNum'];
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
            'CorpNum' => self::$CorpNum,
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

    public static function getErrorMessage($code) {
        $code = (string) $code;
        return self::$error_codes[$code] ? self::$error_codes[$code] : "$code 정의되지 않은 오류입니다.";
    }
}