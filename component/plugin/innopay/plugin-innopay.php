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
                pay_core : {type : Object, default : {}},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",

                    row: {
                        // JayDream/plugin/innopay/noti.php 파일 사이트에서 노티 설정해줘야함
                        //필수//
                        // 지불수단 (간편결제,계좌간편,가상계좌,계좌이체,NONE,신용카드(일반),해외카드결제)
                        //         (EPAY   ,EBANK  ,VBANK  ,BANK   ,NONE,CARD         ,OPCARD)
                        payMethod : "",
                        mid : "testpay01m", // 상점 아이디
                        moid : this.$jd.lib.generateUniqueId(), // 주문 번호 6~40 소문자, 대문자, 숫자, -, _ 값 만으로 충분히 랜덤한 값을 만들어주세요.
                        goodsName : "", // 상품명
                        amt : "", // 거래금액 (과세금액) 면세금액은 taxFreeAmt에 넣어 주세요. ※ 총 결제금액 = amt + taxFreeAmt
                        buyerName : "", // 구매자 이름
                        buyerTel : "", // 구매자 연락처 * 숫자만 입력
                        buyerEmail : "", // 구매자 이메일
                        returnUrl : `${this.$jd.url}/`, // 가맹점 인증 완료 페이지 주소
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
                //this.row = await this.$getData(this.filter);
                //await this.$getsData(this.filter,this.rows);

                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {
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