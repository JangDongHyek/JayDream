<?php
require_once __DIR__ . '/../../require.php';
require_once __DIR__ . "/BaroBill.php";

use JayDream\Lib;
use JayDream\BaroBill;
use JayDream\model;
use JayDream\Config;

if (!isset($_COOKIE['jd_jwt_token'])) Lib::error("jwt 토큰이 존재하지않습니다.");
$jwt = Lib::jwtDecode($_COOKIE['jd_jwt_token']);


$method = $_POST['_method'];

$response = array(
    "success" => false,
    "message" => "_method가 존재하지않습니다."
);


$obj = Lib::jsonDecode($_POST['obj'],false);
$options = Lib::jsonDecode($_POST['options'],false);
BaroBill::init();

switch ($method) {
    case "CheckCorpIsMember" :
        $response = BaroBill::CheckCorpIsMember($obj);
        break;

    case "RegistCorp" :
        $response = BaroBill::RegistCorp($obj);
        break;

    case "RegistAndIssueTaxInvoice" :
        if (!Config::existsTable("jd_provider_barobill_tax_invoice")) {
            $schema = require __DIR__ . '/../../schema/jd_provider_barobill_tax_invoice.php';
            Config::createTableFromSchema("jd_provider_barobill_tax_invoice",$schema);
        }

        $model = new Model("jd_provider_barobill_tax_invoice");
        $row = $model->where("table_primary",$options['primary'])->get();
        if($row['count']) Lib::error("이미 신청한 주문건입니다.");

        $response = BaroBill::RegistAndIssueTaxInvoice($obj,$options['order']);
        if($response['result'] == 1) $model->insert(array(
            "table_name" => $options['table'],
            "table_primary" => $options['primary'],
            "MgtNum" => $response['MgtNum'],
        ));
        break;
}

if(!Config::$DEV) $response = Lib::encryptAPI($response);
echo Lib::jsonEncode($response);