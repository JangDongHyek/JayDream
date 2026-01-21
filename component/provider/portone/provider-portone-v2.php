<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script src="https://cdn.portone.io/v2/browser-sdk.js"></script>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">
        <!--<button @click="pay">결제하기</button>-->
    </div>

    <div v-if="!load"><div class="loader"></div></div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                primary : {type : String, default : ""},
                pay_info : {type : Object, default : {}},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",

                    table : null,
                    row: {},
                    rows : [],

                    info : {},

                    BANK_KO : {
                        BANK_OF_KOREA: "한국은행",
                        KDB: "산업은행",
                        IBK: "기업은행",
                        KOOKMIN: "국민은행",
                        SUHYUP: "수협은행",
                        KEXIM: "수출입은행",
                        NONGHYUP: "NH농협은행",
                        LOCAL_NONGHYUP: "지역농축협",
                        WOORI: "우리은행",
                        STANDARD_CHARTERED: "SC제일은행",
                        CITI: "한국씨티은행",
                        SUHYUP_FEDERATION: "수협중앙회",
                        DAEGU: "아이엠뱅크",
                        BUSAN: "부산은행",
                        KWANGJU: "광주은행",
                        JEJU: "제주은행",
                        JEONBUK: "전북은행",
                        KYONGNAM: "경남은행",
                        KFCC: "새마을금고",
                        SHINHYUP: "신협",
                        SAVINGS_BANK: "저축은행",
                        MORGAN_STANLEY: "모간스탠리은행",
                        HSBC: "HSBC은행",
                        DEUTSCHE: "도이치은행",
                        JPMC: "제이피모간체이스은행",
                        MIZUHO: "미즈호은행",
                        MUFG: "엠유에프지은행",
                        BANK_OF_AMERICA: "BOA은행",
                        BNP_PARIBAS: "비엔피파리바은행",
                        ICBC: "중국공상은행",
                        BANK_OF_CHINA: "중국은행",
                        NFCF: "산림조합중앙회",
                        UOB: "대화은행",
                        BOCOM: "교통은행",
                        CCB: "중국건설은행",
                        POST: "우체국",
                        KODIT: "신용보증기금",
                        KIBO: "기술보증기금",
                        HANA: "하나은행",
                        SHINHAN: "신한은행",
                        K_BANK: "케이뱅크",
                        KAKAO: "카카오뱅크",
                        TOSS: "토스뱅크",
                        MISC_FOREIGN: "기타 외국계은행(중국 농업은행 등)",
                        SGI: "서울보증보험",
                        KCIS: "한국신용정보원",
                        YUANTA_SECURITIES: "유안타증권",
                        KB_SECURITIES: "KB증권",
                        SANGSANGIN_SECURITIES: "상상인증권",
                        HANYANG_SECURITIES: "한양증권",
                        LEADING_SECURITIES: "리딩투자증권",
                        BNK_SECURITIES: "BNK투자증권",
                        IBK_SECURITIES: "IBK투자증권",
                        DAOL_SECURITIES: "다올투자증권",
                        MIRAE_ASSET_SECURITIES: "미래에셋증권",
                        SAMSUNG_SECURITIES: "삼성증권",
                        KOREA_SECURITIES: "한국투자증권",
                        NH_SECURITIES: "NH투자증권",
                        KYOBO_SECURITIES: "교보증권",
                        HI_SECURITIES: "하이투자증권",
                        HYUNDAI_MOTOR_SECURITIES: "현대차증권",
                        KIWOOM_SECURITIES: "키움증권",
                        EBEST_SECURITIES: "LS증권",
                        SK_SECURITIES: "SK증권",
                        DAISHIN_SECURITIES: "대신증권",
                        HANHWA_SECURITIES: "한화투자증권",
                        HANA_SECURITIES: "하나증권",
                        TOSS_SECURITIES: "토스증권",
                        SHINHAN_SECURITIES: "신한투자증권",
                        DB_SECURITIES: "DB금융투자",
                        EUGENE_SECURITIES: "유진투자증권",
                        MERITZ_SECURITIES: "메리츠증권",
                        KAKAO_PAY_SECURITIES: "카카오페이증권",
                        BOOKOOK_SECURITIES: "부국증권",
                        SHINYOUNG_SECURITIES: "신영증권",
                        CAPE_SECURITIES: "케이프투자증권",
                        KOREA_SECURITIES_FINANCE: "한국증권금융",
                        KOREA_FOSS_SECURITIES: "한국포스증권",
                        WOORI_INVESTMENT_BANK: "우리종합금융"
                    },
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
                this.api.component_name = this.component_name
            },
            async mounted() {
                // this.table = await this.api.table("exam");
                // await this.table.get(this.rows,{paging : 10})

                await this.getInit();
                this.load = true;

                this.$nextTick(async () => {
                    // 해당부분에 퍼블리싱 라이브러리,플러그인 선언부분 하시면 됩니다 ex) swiper
                });
            },
            updated() {

            },
            methods: {

                async getInit() {
                    let res = await this.lib.ajax("init",{},"/JayDream/provider/portone/api.php")
                    this.info = res.info;
                },
                async checkVirtual(payment_id) {
                    let res = await this.lib.ajax("order",{payment_id : payment_id},"/JayDream/provider/portone/api.php")
                    console.log(res);
                    if(res.data.status === "FAILED") {
                        await this.lib.alert("사용자 결제 취소된 가상계좌 입니다. 다시 결제 신청해주세요.")
                    }else {
                        let bank = this.BANK_KO[res.data.method.bank];
                        let account = res.data.method.accountNumber

                        await this.lib.alert(`은행 : ${bank}\n계좌 : ${account}\n금액 : ${this.prototype.format(res.data.amount.total)}`)
                    }
                },
                async pay() {
                    const response = await PortOne.requestPayment(this.payObject);

                    if (response.code !== undefined) {
                        // 오류 발생
                        return alert(response.message);
                    }

                    if(this.pay_info.payMethod === "VIRTUAL_ACCOUNT") {
                        await this.lib.alert("목록에서 입금정보를 확인해주세요. 입금이 확인되면 자동으로 데이터가 업데이트됩니다.");
                        window.location.reload();
                    }else {
                        await this.lib.alert("결제가 확인되면 자동으로 데이터가 업데이트됩니다.");
                        window.location.reload();
                    }

                },
            },
            computed: {
                payObject() {
                    let obj = {
                        storeId: this.info.store_id,
                        channelKey: this.info.channel_key,
                        paymentId: this.pay_info.paymentId,
                        orderName: this.pay_info.orderName,
                        totalAmount: this.pay_info.totalAmount,
                        currency: "CURRENCY_KRW",
                        payMethod : this.pay_info.payMethod, // CARD,VIRTUAL_ACCOUNT,TRANSFER
                    };

                    if(this.pay_info.customer) {
                        /*
                        customer? Object 구매자 정보
                            fullName? String 구매자 이름
                         */
                        Object.assign(obj, {
                            customer: this.pay_info.customer
                        });
                    }

                    if(this.pay_info.customData) {
                        //customData? Object 임의이 데이터 저장 마음껏 꾸며도된다
                        Object.assign(obj, {
                            customData: this.pay_info.customData
                        });
                    }

                    if(this.pay_info.payMethod === "VIRTUAL_ACCOUNT") {
                        obj.virtualAccount = {
                            accountExpiry: {
                                validHours: 1,
                            },
                        };
                    }
                    return obj;
                }
            },
            watch: {

            }
        }});
</script>