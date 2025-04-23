<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <div>
        <div v-if="load">
            <div id="container">
                <div id="cashReceipt1">
                    <h3 class="first">현금영수증 발급하기</h3>
                    <div>
                        <div class="chk">
                            <input type="radio" id="ch_1" v-model="form.receiptType" value="1"><label for="ch_1"><div></div>소득공제</label>
                        </div>
                        <div class="chk">
                            <input type="radio" id="ch_2" v-model="form.receiptType" value="2"><label for="ch_2"><div></div>지출증빙</label>
                        </div>
                    </div>
                    <div class="infoBox">
                        <label>{{form.receiptType == '1' ? '휴대폰번호' : '사업자번호'}}</label>
                        <input type="text" class="frm_input" v-model="form.receiptIdentity">

                        <h5>구매정보</h5>
                        <ul>
                            <li>
                                <div class="left">구매자</div>
                                <div class="right">{{row.buyer.name}}</div>
                            </li>
                            <li>
                                <div class="left">구매상품</div>
                                <div class="right">{{row.goods.name}} {{row.goods.cnt}}개</div>
                            </li>
                            <li>
                                <div class="left">지불수단</div>
                                <div class="right">{{getPayType()}}</div>
                            </li>
                            <li>
                                <div class="left">주문번호</div>
                                <div class="right">{{row.moid}}</div>
                            </li>
                            <li>
                                <div class="left">승인번호</div>
                                <div class="right">{{row.authNum}}</div>
                            </li>
                            <li>
                                <div class="left">승인일시</div>
                                <div class="right">{{row.approvedAt}}</div>
                            </li>
                        </ul>

                        <h5>금액</h5>
                        <ul>
                            <li>
                                <div class="left">공급가액</div>
                                <div class="right">{{(Math.floor(row.amt / 1.1)).format()}}원</div>
                            </li>
                            <li>
                                <div class="left">부가세</div>
                                <div class="right">{{(row.amt - Math.floor(row.amt / 1.1)).format()}}원</div>
                            </li>
                            <li class="total">
                                <div class="left">합계</div>
                                <div class="right">{{row.amt.format()}}원</div>
                            </li>
                        </ul>
                    </div>
                    <div class="btnWrap">
                        <button class="regi" @click="cashRequest()">발급하기</button>
                        <button class="clos">닫기</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!load"></div>
    </div>
</script>

<script>
    JayDream_components.push({
        name: "<?=$componentName?>", object: {
            template: "#<?=$componentName?>-template",
            props: {
                tid : {type: String, default: ""},
            },
            data: function () {
                return {
                    load: false,
                    component_name: "<?=$componentName?>",
                    component_idx: "",

                    form : {
                        receiptType : "1",
                        receiptIdentity : "",
                    },

                    row: {},
                    rows: [],

                    filter: {
                        table: "jd_plugin_innopay",

                        where: [
                            {
                                column: "tid",            // join 조건시 user.idx
                                value: this.tid ,             // LIKE일시 %% 필수 || relations일시  $parent.idx
                                logical: "AND",        // AND,OR,AND NOT
                                operator: "=",         // = ,!= >= <=, LIKE,
                            },
                        ],
                    },

                    cash : {
                        userId : this.user_id,
                        svcPrdtCd : "01",
                    }
                };
            },
            async created() {
                this.component_idx = this.$jd.lib.generateUniqueId();
                if(!this.tid) {
                    await this.$jd.lib.alert("주문번호가 존재하지않습니다.");
                    history.back();
                }
            },
            async mounted() {
                this.row = await this.$getData(this.filter);
                if(!this.row) {
                    await this.$jd.lib.alert("주문데이터가 존재하지않습니다.");
                    history.back();
                }
                if(this.row.cashReceipt) {
                    await this.$jd.lib.alert("이미 현금 영수증을 발행한 주문데이터입니다.");
                    history.back();
                }

                await this.init();

                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {
                async cashRequest() {
                    if(!this.form.receiptIdentity) {
                        let type = this.form.receiptType == '1' ? "휴대폰번호" : "사업자번호"
                        await this.$jd.lib.alert(`${type}를 입력해주세요.`);
                        return false;
                    }

                    let obj = Object.assign({},this.cash,this.form);
                    obj.moid = this.$jd.lib.generateUniqueId();
                    obj.goodsName = this.row['goods'].name;
                    obj.amt = this.row.amt;
                    obj.dutyFreeAmt = this.row.taxFreeAmt;
                    obj.buyerName = this.row['buyer'].name;
                    obj.goodsCnt = this.row['goods'].cnt;
                    obj.buyerTel = this.row['buyer'].tel;

                    console.log(obj);

                    try {
                        const response = await fetch("https://api.innopay.co.kr/api/cashPayApi", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify(obj),
                        });
                        const data = await response.json();

                        if (data.resultCode == "0000") {
                            // 이노페이 현금영수증 한것처럼 데이터셋 맞추기
                            let cash_data = {
                                receiptType : obj.receiptType,
                                receiptTypeNo : obj.receiptIdentity,
                                supplyAmt : Math.floor(obj.amt / 1.1),
                                vat : obj.amt - Math.floor(obj.amt / 1.1),
                                serviceAmt : obj.dutyFreeAmt,
                                orgTid : this.row.tid,
                                tid : data.tid,
                            }

                            this.row.cashReceipt = cash_data;

                            await this.$postData(this.row,{table : "jd_plugin_innopay", return : true});

                            await this.$jd.lib.alert("완료되었습니다.");
                            history.back();
                        } else {
                            await this.$jd.lib.alert("에러 발생: " + data.resultMsg);
                        }
                    } catch (e) {
                        console.error(e);
                        alert("통신 에러");
                    }
                },
                async init() {
                    try {
                        // 설정값
                        let res = await this.$jd.lib.ajax("innopay",{},"/JayDream/api.php",{});
                        this.cash.mid = res.mid;

                    }catch (e) {
                        await this.$jd.lib.alert(e.message)
                    }
                },
                getPayType() {
                    switch (this.row.payMethod) {
                        case "BANK" : return "계좌이체"
                        case "VBANK" : return "가상계좌"
                    }
                }
            },
            computed: {},
            watch: {}
        }
    });
</script>