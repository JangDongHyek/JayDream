<?php
require_once __DIR__ . '/../../require.php';
require_once __DIR__ . "/Portone.php";
require_once __DIR__ . "/../hosting_kr/HostingKr.php";;

use JayDream\Config;
use JayDream\Model;
use JayDream\Lib;
use JayDream\Portone;
use JayDream\HostingKr;
use JayDream\Mail;



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Portone::init();
    if (!Config::existsTable("jd_provider_portone_noti_v2")) {
        $schema = require __DIR__ . '/../../schema/jd_provider_portone_noti_v2.php';
        Config::createTableFromSchema("jd_provider_portone_noti_v2",$schema);
    }
    if (!Config::existsTable("jd_provider_portone_order")) {
        $schema = require __DIR__ . '/../../schema/jd_provider_portone_order.php';
        Config::createTableFromSchema("jd_provider_portone_order",$schema);
    }

    $noti_model = new Model("jd_provider_portone_noti_v2");
    $order_model = new Model("jd_provider_portone_order");

    // JSON 요청 본문 파싱
    $requestBody = file_get_contents('php://input');
    $data = json_decode($requestBody, true);

    $noti_model->insert($data);

    $payment_id = $data['payment_id'];

    //가상계좌일경우
    if($data['status'] === "VirtualAccountIssued") {
        //$payment_data = Portone::getOrder($payment_id);
    }

    //결제가 완료될경우
    if($data['status'] === "Paid") {
        $my_domain_model = new Model("my_domain");

        //결제완료시 order 테이블에 저장
        $order = $order_model->where("id",$payment_id)->get()['data'][0];
        if(!$order) {
            $payment_data = Portone::getOrder($payment_id);
            $payment_data = Portone::parseOrder($payment_data);
            $order_model->insert($payment_data);
        }else {
            return false;
        }


        $domain = $my_domain_model->where("payment_id",$payment_id)->get()['data'][0];
        $domain['pay_status'] = "true";
        $domain['status'] = "도메인 구매중";
        $my_domain_model->update($domain);

        HostingKr::init();
        $res = HostingKr::buyDomain($domain);

        if(!$res['success']) {
            $domain['status'] = "도메인 구매 오류";
            $domain['status_memo'] = $res['data']['message'];
            $my_domain_model->update($domain);
        }else {
            $res = HostingKr::getDomain($domain['domain']);

            if(!$res['success']) {
                $domain['status'] = "도메인 조회 오류";
                $domain['status_memo'] = "재조회를 해주세요.";
                $my_domain_model->update($domain);
            }else {
                if (!Config::existsTable("jd_provider_hosting_kr")) {
                    $schema = require __DIR__ . '/../../schema/jd_provider_hosting_kr.php';
                    Config::createTableFromSchema("jd_provider_hosting_kr",$schema);
                }

                $jd_provider_hosting_kr = new Model("jd_provider_hosting_kr");
                $jd_provider_hosting_kr->insert($res['data']);


                //bind 존 파일추가
                $named_sub_contents = "";
                $named_sub_contents .= PHP_EOL.sprintf('zone "%s" { type master; file "/etc/bind/db/db.%s"; allow-query { any; }; };', $domain['domain'], $domain['domain']);
                $zone_file_contents = sprintf('$TTL    604800
@       IN      SOA     ns.isweb.co.kr. root.isweb.co.kr. (	%s	86400	86400	2419200	86400 )
@       IN      NS      ns.isweb.co.kr.
@       IN      NS      ns1.isweb.co.kr.
@       IN      A       211.252.85.56', date('Ymd'));
                $zone_file = fopen(sprintf("/etc/bind/db/db.%s", $domain['domain']), 'w+') or die('cant`t open file');
                fwrite($zone_file, $zone_file_contents);
                fclose($zone_file);
                $named_sub_file = fopen("/etc/bind/sub/named_sub", 'a+') or die('cant`t open file');
                fwrite($named_sub_file, $named_sub_contents);
                fclose($named_sub_file);
                exec('/var/named_restart');

                //서버설정은 외부업체가 맡고있기떄문에 메일로 요청
                Mail::from("support@isweb.co.kr");
                Mail::to("system@ncloud24.com");
                Mail::cc("support@isweb.co.kr")->cc("jangdonghyek@gmail.com")->cc("fove102@naver.com");
                Mail::title("[위멘토] 신규 도메인 WAF 장비 등록 요청, 등록해야 할 도메인 : http://'.{$domain['domain']}.', http://*.'.{$domain['domain']}");
                Mail::html("안녕하세요. 위멘토입니다.<br>
                            http://".$domain['domain']."과 http://*.".$domain['domain']."을 WAF에 추가 하여 주시기 바랍니다.<br><br>감사합니다.");
                $result = Mail::send();

                $domain['status'] = "활성화";
                $domain['status_memo'] = "";
                $my_domain_model->update($domain);
            }
        }
    }

    if($data['status'] === "Cancelled") {
        //결제취소시 order 업데이트
        $order = $order_model->where("id",$payment_id)->get()['data'][0];
        $payment_data = Portone::getOrder($payment_id);
        $payment_data = Portone::parseOrder($payment_data);
        if(!$order) {
            $order_model->insert($payment_data);
        }else {
            $payment_data['primary'] = $order['primary'];
            $order_model->update($payment_data);
        }
    }
}