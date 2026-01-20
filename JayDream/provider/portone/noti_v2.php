<?php
require_once __DIR__ . '/../../require.php';
require_once __DIR__ . "/Portone.php";

use JayDream\Config;
use JayDream\Model;
use JayDream\Lib;
use JayDream\Portone;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Portone::init();
    if (!Config::existsTable("jd_provider_portone_noti_v2")) {
        $schema = require __DIR__ . '/../../schema/jd_provider_portone_noti_v2.php';
        Config::createTableFromSchema("jd_provider_portone_noti_v2",$schema);
    }

    $noti_model = new Model("jd_provider_portone_noti_v2");

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

        $domain = $my_domain_model->where("payment_id",$payment_id)->get()['data'][0];
        $domain['pay_status'] = "true";
        $domain['status'] = "도메인 구매중";
        $my_domain_model->update($domain);

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

                $domain['status'] = "활성화";
                $domain['status_memo'] = "";
                $my_domain_model->update($domain);
            }
        }
    }
}
