<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script src="https://pg.innopay.co.kr/tpay/js/v1/innopay.js"></script>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">
        <slot></slot>
    </div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                primary : {type : String, default : ""},
                mb_no : {type : String, default : ""},
                redirect_url : {type : String, default : ""},
                pay_core : {type : Object, default : {
                        payMethod : "",
                        goodsName : "",
                        amt : "",
                        buyerName : "",
                        buyerTel : "",
                        buyerEmail : "",
                    }},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",

                    merchantKey : "",

                    row: {
                        // JayDream/plugin/innopay/noti.php 파일 사이트에서 노티 설정해줘야함
                        //필수//
                        // 지불수단 (간편결제,계좌간편,가상계좌,계좌이체,NONE,신용카드(일반),해외카드결제)
                        //         (EPAY   ,EBANK  ,VBANK  ,BANK   ,NONE,CARD         ,OPCARD)
                        payMethod : "",
                        mid : "", // 상점 아이디
                        moid : this.$jd.lib.generateUniqueId(), // 주문 번호 6~40 소문자, 대문자, 숫자, -, _ 값 만으로 충분히 랜덤한 값을 만들어주세요.
                        goodsName : "", // 상품명
                        amt : "", // 거래금액 (과세금액) 면세금액은 taxFreeAmt에 넣어 주세요. ※ 총 결제금액 = amt + taxFreeAmt
                        buyerName : "", // 구매자 이름
                        buyerTel : "", // 구매자 연락처 * 숫자만 입력
                        buyerEmail : "", // 구매자 이메일
                        returnUrl : window.location.href, // 가맹점 인증 완료 페이지 주소 #jd 고정값
                        currency : "KRW", // 결제 통화 (KRW,USD)
                        //선택//
                        taxFreeAmt : "", // 면세 금액
                        goodsCnt : "", // 상품 개수
                        appScheme : "", // 앱스킴
                        logoUrl : "", // 로고 URL
                        mallReserved : "", // 여분 필드
                        offeringPeriod : "", // 제공 기간
                        mallIp : "", // 가맹점 IP
                        mallUserId : "", // 가맹점 유저 ID
                        userIp : "", // 구매자 IP
                        userId : "", // 가맹점 영업사원 ID

                    },
                    rows : [],
                };
            },
            async created() {
                this.component_idx = this.$jd.lib.generateUniqueId();
            },
            async mounted() {
                this.init();



                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {
                async payRequest() {
                    const url = new URL(window.location.href)

                    const paymentToken = url.searchParams.get("paymentToken")
                    const tid = url.searchParams.get("tid")
                    const mid = url.searchParams.get("mid")
                    const amt = url.searchParams.get("amt")
                    const taxFreeAmt = url.searchParams.get("taxFreeAmt")
                    const moid = url.searchParams.get("moid")

                    // 결제요청을 한 상태라면 결제승인요청보내기
                    if(paymentToken) {
                        this.load = false;
                        const resp = await fetch(`https://api.innopay.co.kr/v1/transactions/pay`, {
                            method: "POST",
                            headers: {
                                "Payment-Token": paymentToken,
                                "Merchant-Key": this.merchantKey,
                                "Content-Type": "application/json; charset=utf-8"
                            },
                            body: JSON.stringify({tid, mid, amt, taxFreeAmt, moid})
                        })

                        const result = await resp.json()
                        this.load = true;
                        if(result.success) {
                            // 결과값 로그테이블에 저장
                            await this.$postData(result.data,{table:'jd_plugin_innopay',return : true});

                            if(result.data.payMethod == "VBANK") {
                                await this.$jd.lib.alert(`
                                    은행 : ${result.data.virtualAccount.bankName}\n
                                    계좌 : ${result.data.virtualAccount.accountNumber}\n
                                    금액 : ${item.$jd_plugin_innopay__amt.format()}원\n
                                    입금시 자동으로 결제완료로 변경됩니다\n
                                    `);

                                this.$jd.lib.href(this.redirect_url);
                            }else {
                                //결제성공시 로직
                                this.$emit('paySuccess',result.data);
                            }

                        }else {
                            await this.$jd.lib.alert(result.error.message)
                        }
                    }
                },
                async init() {
                    try {
                        // 설정값
                        let res = await this.$jd.lib.ajax("innopay",{},"/JayDream/plugin/innopay/api.php",{});
                        this.row.mid = res.mid;
                        this.merchantKey = res.merchantKey;

                        await this.payRequest();
                    }catch (e) {
                        await this.$jd.lib.alert(e.message)
                    }
                },
                async pay() {
                    if(!this.pay_data.payMethod) {
                        await this.$jd.lib.alert("결제 타입이 없습니다.");
                        return false;
                    }
                    if(!this.pay_data.goodsName) {
                        await this.$jd.lib.alert("상품명이 없습니다");
                        return false;
                    }
                    if(!this.pay_data.amt) {
                        await this.$jd.lib.alert("상품금액이 없습니다.");
                        return false;
                    }
                    if(!this.pay_data.buyerName) {
                        await this.$jd.lib.alert("구매자명이 없습니다.");
                        return false;
                    }
                    if(!this.pay_data.buyerTel) {
                        await this.$jd.lib.alert("구매자연락처가 없습니다.");
                        return false;
                    }
                    if(!this.pay_data.buyerEmail) {
                        await this.$jd.lib.alert("구매자이메일이 없습니다");
                        return false;
                    }

                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(this.pay_data.buyerEmail)) {
                        await this.$jd.lib.alert("올바른 이메일 형식이 아닙니다.");
                        return false;
                    }


                    innopay.goPay(this.pay_data);
                }
            },
            computed: {
                pay_data() {
                    return {
                        ...this.row,
                        ...this.pay_core // 부모로부터 전달된 값으로 덮어쓰기
                    };
                }
            },
            watch: {

            }
        }});
</script>