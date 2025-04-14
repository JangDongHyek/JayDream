<?php
require_once __DIR__ . '/../../require.php';

use JayDream\Config;

/*******************************************************************************
 * FILE NAME : InnopayPgNoti_PHP.php
 * DATE : 2015.03.18
 *******************************************************************************/

@extract($_GET);
@extract($_POST);
@extract($_SERVER);

/**********************************************************************************/
//이부분에 로그파일 경로를 수정해주세요.
$LogPath = Config::resourcePath()."/plugin/innopay/log";
// 디렉토리 없으면 생성
if (!is_dir($LogPath)) {
    mkdir($LogPath, 0755, true);
}
/**********************************************************************************/


$TEMP_IP = getenv("REMOTE_ADDR");
$PG_IP  = substr($TEMP_IP,0, 13);


/*******************************************************************************
 * 변수명           한글명
 *--------------------------------------------------------------------------------
 ********************************************************************************
 * 공통
 ********************************************************************************
 * transSeq			거래번호
 * userId			사용자아이디
 * userName			사용자이름
 * userPhoneNo		사용자휴대폰번호
 * moid				주문번호
 * goodsName		상품명
 * goodsAmt			상품금액
 * buyerName		구매자명
 * buyerPhoneNo		구매자휴대폰번호
 * pgCode			PG코드 ( 01:NICE / 02:KICC / 03:INFINISOFT / 04:KSNET / 05:KCP / 06:SMATRO )
 * pgName			PG명
 * payMethod		결제수단( 01:현금결제 / 02:신용카드 / 03:신용카드ARS )
 * payMethodName	결제수단명
 * pgMid			PG아이디
 * pgSid			PG서비스아이디
 * status			거래상태 ( 25:결제완료 / 85:결제취소 )
 * statusName		거래상태명
 * pgResultCode		PG결과코드
 * pgResultMsg		PG결과메세지
 * pgAppDate		PG승인일자
 * pgAppTime		PG승인시간
 * pgTid			PG거래번호
 * approvalAmt		승인금액
 * approvalNo		승인번호
 ********************************************************************************
 * 현글결제(현금영수증)
 ********************************************************************************
 * cashReceiptType			증빙구분 ( 1:소득공제 / 2:지출증빙 )
 * cashReceiptTypeName		증빙구분명
 * cashReceiptSupplyAmt		공급가
 * cashReceiptVat			부가세
 ********************************************************************************
 * 신용카드결제
 ********************************************************************************
 * cardNo					카드번호
 * cardQuota				할부개월
 * cardIssueCode			발급사코드 ( 메뉴얼참조 )
 * cardIssueName			발급사명
 * cardAcquireCode			매입사코드 ( 메뉴얼참조 )
 * cardAcquireName			매입사명
 ********************************************************************************
 * 결제취소
 ********************************************************************************
 * cancelAmt				취소요청금액
 * cancelMsg				취소요청메세지
 * cancelResultCode			취소결과코드
 * cancelResultMsg			취소결과메세지
 * cancelAppDate			취소승인일자
 * cancelAppTime			취소승인시간
 * cancelPgTid				PG거래번호
 * cancelApprovalAmt		승인금액
 * cancelApprovalNo			승인번호
 ********************************************************************************
 * 자동결제
 ********************************************************************************
 * billKey					발급받은 빌키
 *******************************************************************************/

$transSeq      	= $transSeq;
$userId        	= $userId;
$userName      	= $userName;
$userPhoneNo   	= $userPhoneNo;
$moid          	= $moid;
$goodsName     	= $goodsName;
$goodsAmt      	= $goodsAmt;
$buyerName     	= $buyerName;
$buyerPhoneNo  	= $buyerPhoneNo;
$pgCode        	= $pgCode;
$pgName        	= $pgName;
$payMethod     	= $payMethod;
$payMethodName 	= $payMethodName;
$pgMid         	= $pgMid;
$pgSid         	= $pgSid;
$status        	= $status;
$statusName    	= $statusName;
$pgResultCode  	= $pgResultCode;
$pgResultMsg   	= $pgResultMsg;
$pgAppDate     	= $pgAppDate;
$pgAppTime     	= $pgAppTime;
$pgTid         	= $pgTid;
$approvalAmt   	= $approvalAmt;
$approvalNo    	= $approvalNo;


if($payMethod == '01'){
    //현금결제(현금영수증)
    $cashReceiptType		= $cashReceiptType;
    $cashReceiptTypeName	= $cashReceiptTypeName;
    $cashReceiptSupplyAmt	= $cashReceiptSupplyAmt;
    $cashReceiptVat			= $cashReceiptVat;

}else if($payMethod == '02' || $payMethod == '03'){
    //신용카드 & 신용카드ARS
    $cardNo				= $cardNo;
    $cardQuota			= $cardQuota;
    $cardIssueCode		= $cardIssueCode;
    $cardIssueName		= $cardIssueName;
    $cardAcquireCode	= $cardAcquireCode;
    $cardAcquireName	= $cardAcquireName;
}else if($payMethod == '09'){
    //자동결제
    $billKey			= $billKey;
}


if($status == '85'){
    //결제취소
    $cancelAmt			= $cancelAmt;
    $cancelMsg			= $cancelMsg;
    $cancelResultCode	= $cancelResultCode;
    $cancelResultMsg	= $cancelResultMsg;
    $cancelAppDate		= $cancelAppDate;
    $cancelAppTime		= $cancelAppTime;
    $cancelPgTid		= $cancelPgTid;
    $cancelApprovalAmt	= $cancelApprovalAmt;
    $cancelApprovalNo	= $cancelApprovalNo;
}

//상품 정보가 추가될 경우 (주석제거)
//$goodsSize			= $goodsSize;
//$goodsCodeArray		= $goodsCodeArray;
//$goodsNameArray		= $goodsNameArray;
//$goodsAmtArray		= $goodsAmtArray;
//$goodsCntArray		= $goodsCntArray;
//$totalAmtArray		= $totalAmtArray;

//배송지 정보가 추가될 경우 (주석제거)
//$zoneCode				= $zoneCode;
//$address				= $address;
//$addressDetail		= $addressDetail;
//$recipientName		= $recipientName;
//$recipientPhoneNo		= $recipientPhoneNo;
//$comment				= $comment;


$PageCall = date("Y-m-d [H:i:s]",time());
$logfile = fopen( $LogPath . "/innopay_receive.log", "a+" );

fwrite( $logfile,"************************************************\r\n");
fwrite( $logfile,"PageCall time : ".$PageCall."\r\n");
fwrite( $logfile,"transSeq      : ".$transSeq."\r\n");
fwrite( $logfile,"userId        : ".$userId."\r\n");
fwrite( $logfile,"userName      : ".$userName."\r\n");
fwrite( $logfile,"userPhoneNo   : ".$userPhoneNo."\r\n");
fwrite( $logfile,"moid          : ".$moid."\r\n");
fwrite( $logfile,"goodsName     : ".$goodsName."\r\n");
fwrite( $logfile,"goodsAmt      : ".$goodsAmt."\r\n");
fwrite( $logfile,"buyerName     : ".$buyerName."\r\n");
fwrite( $logfile,"buyerPhoneNo  : ".$buyerPhoneNo."\r\n");
fwrite( $logfile,"pgCode        : ".$pgCode."\r\n");
fwrite( $logfile,"pgName        : ".$pgName."\r\n");
fwrite( $logfile,"payMethod     : ".$payMethod."\r\n");
fwrite( $logfile,"payMethodName : ".$payMethodName."\r\n");
fwrite( $logfile,"pgMid         : ".$pgMid."\r\n");
fwrite( $logfile,"pgSid         : ".$pgSid."\r\n");
fwrite( $logfile,"status        : ".$status."\r\n");
fwrite( $logfile,"statusName    : ".$statusName."\r\n");
fwrite( $logfile,"pgResultCode  : ".$pgResultCode."\r\n");
fwrite( $logfile,"pgResultMsg   : ".$pgResultMsg."\r\n");
fwrite( $logfile,"pgAppDate     : ".$pgAppDate."\r\n");
fwrite( $logfile,"pgAppTime     : ".$pgAppTime."\r\n");
fwrite( $logfile,"pgTid         : ".$pgTid."\r\n");
fwrite( $logfile,"approvalAmt   : ".$approvalAmt."\r\n");
fwrite( $logfile,"approvalNo    : ".$approvalNo."\r\n");

if($payMethod == '01'){
    fwrite( $logfile,"cashReceiptType       : ".$cashReceiptType."\r\n");
    fwrite( $logfile,"cashReceiptTypeName   : ".$cashReceiptTypeName."\r\n");
    fwrite( $logfile,"cashReceiptSupplyAmt  : ".$cashReceiptSupplyAmt."\r\n");
    fwrite( $logfile,"cashReceiptVat        : ".$cashReceiptVat."\r\n");
} else if($payMethod == '02' || $payMethod == '03'){
    fwrite( $logfile,"cardNo          : ".$cardNo."\r\n");
    fwrite( $logfile,"cardQuota       : ".$cardQuota."\r\n");
    fwrite( $logfile,"cardIssueCode   : ".$cardIssueCode."\r\n");
    fwrite( $logfile,"cardIssueName   : ".$cardIssueName."\r\n");
    fwrite( $logfile,"cardAcquireCode : ".$cardAcquireCode."\r\n");
    fwrite( $logfile,"cardAcquireName : ".$cardAcquireName."\r\n");
} else if($payMethod == '09'){
    fwrite( $logfile,"billKey          : ".$billKey."\r\n");
}

if($status == '85'){
    fwrite( $logfile,"cancelAmt         : ".$cancelAmt."\r\n");
    fwrite( $logfile,"cancelMsg         : ".$cancelMsg."\r\n");
    fwrite( $logfile,"cancelResultCode  : ".$cancelResultCode."\r\n");
    fwrite( $logfile,"cancelResultMsg   : ".$cancelResultMsg."\r\n");
    fwrite( $logfile,"cancelAppDate     : ".$cancelAppDate."\r\n");
    fwrite( $logfile,"cancelAppTime     : ".$cancelAppTime."\r\n");
    fwrite( $logfile,"cancelPgTid       : ".$cancelPgTid."\r\n");
    fwrite( $logfile,"cancelApprovalAmt : ".$cancelApprovalAmt."\r\n");
    fwrite( $logfile,"cancelApprovalNo  : ".$cancelApprovalNo."\r\n");

}

//상품 정보가 추가될 경우 (주석제거)
//fwrite( $logfile,"goodsSize  		: ".$goodsSize."\r\n");
//fwrite( $logfile,"goodsCodeArray  : ".$goodsCodeArray."\r\n");
//fwrite( $logfile,"goodsNameArray  : ".$goodsNameArray."\r\n");
//fwrite( $logfile,"goodsAmtArray  	: ".$goodsAmtArray."\r\n");
//fwrite( $logfile,"goodsCntArray  	: ".$goodsCntArray."\r\n");
//fwrite( $logfile,"totalAmtArray  	: ".$totalAmtArray."\r\n");

//배송지 정보가 추가될 경우 (주석제거)
//fwrite( $logfile,"zoneCode  		: ".$zoneCode."\r\n");
//fwrite( $logfile,"address  		: ".$address."\r\n");
//fwrite( $logfile,"addressDetail  	: ".$addressDetail."\r\n");
//fwrite( $logfile,"recipientName  	: ".$recipientName."\r\n");
//fwrite( $logfile,"recipientPhoneNo: ".$recipientPhoneNo."\r\n");
//fwrite( $logfile,"comment  		: ".$comment."\r\n");

fwrite( $logfile,"************************************************");
fclose( $logfile );

//************************************************************************************

//위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 "0000"를 인피니로
//리턴하셔야합니다. 아래 조건에 데이터베이스 성공시 받는 FLAG 변수를 넣으세요
//(주의) "0000"를 리턴하지 않으시면 인피니 지불 서버는 "0000"를 수신할때까지 계속 재전송(최대지정횟수)을 시도합니다
//기타 다른 형태의 PRINT( echo )는 하지 않으시기 바랍니다

//      if (데이터베이스 등록 성공 유무 조건변수 = true)
//      {

echo "0000";                        // 절대로 지우지마세요

//      }

//*************************************************************************************

?>















