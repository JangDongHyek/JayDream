<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div>
        <div v-if="load">

        </div>

        <div v-if="!load"></div>
    </div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {

            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",

                    filter : {},

                };
            },
            async created() {
                this.component_idx = this.$jd.lib.generateUniqueId();
            },
            async mounted() {
                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {
                async taxInvoice(company,order) {
                    if(!company) {
                        await this.$jd.lib.alert("회사 정보가 없습니다.");
                        return false;
                    }

                    if(!order) {
                        await this.$jd.lib.alert("주문이 없습니다.");
                        return false;
                    }

                    //회사를 바로빌 형식에 맞게 가공해줘야함
                    let baro_company = {
                        CorpNum: company['CorpNum'],                        // [필수] 사업자등록번호 (하이픈 없이 10자리 숫자만)
                        TaxRegID: company['TaxRegID'],                      // [선택] 종사업장번호 (공란 가능, 보통 일반 사업자는 빈 값)
                        CorpName: company['CorpName'],                      // [필수] 회사명
                        CEOName: company['CEOName'],                        // [필수] 대표자명
                        Addr: `${company['Addr1']} ${company['Addr2']}`,    // [필수] 사업장 주소 (기본주소 + 상세주소 조합)
                        BizClass: company['BizClass'],                      // [필수] 업종 (예: 소프트웨어개발)
                        BizType: company['BizType'],                        // [필수] 업태 (예: IT서비스)
                        ContactID: company['ContactID'],                    // [선택] 담당자 ID (회원 ID 등. 내부 시스템용이면 선택사항)
                        ContactName: company['ContactName'],                // [필수] 담당자 이름
                        TEL: company['TEL'],                                // [선택] 대표 전화번호 (예: 051-123-4567)
                        HP: company['HP'],                                  // [필수] 담당자 휴대폰번호
                        Email: company['Email'],                            // [필수] 담당자 이메일
                    }

                    //주문을 바로빌 형식에 맞게 가공해줘야함
                    let TotalAmount = order['amt'];
                    let AmountTotal = Math.floor(TotalAmount / 1.1);
                    let TaxTotal = TotalAmount - AmountTotal;

                    let baro_order = {
                        AmountTotal : AmountTotal,      //공급가액 (숫자만, 소수점X, 컴마X)
                        TaxTotal : TaxTotal,            //세액 (숫자만, 소수점X, 컴마X)
                        TotalAmount : TotalAmount,      //합계금액 (숫자만, 소수점X, 컴마X)
                    };

                    let options = {
                        order : baro_order,
                        table : "jd_plugin_innopay",    //주문 테이블 명
                        primary : order.idx,            //주문데이터의 고유값
                    }

                    try {
                        let res = await this.$jd.lib.ajax("RegistAndIssueTaxInvoice",baro_company,"/JayDream/plugin/barobill/api.php",options);

                        await this.$jd.lib.alert("세금계산서 발행을 신청했습니다.");
                    }catch (e) {
                        await this.$jd.lib.alert(e.message)
                    }
                }
            },
            computed: {

            },
            watch: {

            }
        }});
</script>