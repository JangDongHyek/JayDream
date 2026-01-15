<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script src="https://cdn.portone.io/v2/browser-sdk.js"></script>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">
        <button @click="pay">결제하기</button>
    </div>

    <div v-if="!load"><div class="loader"></div></div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                primary : {type : String, default : ""},
                payment_id : {type : String, default : crypto.randomUUID()},
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

                    payMethod : "CARD", // CARD,VIRTUAL_ACCOUNT,TRANSFER
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
                async pay() {
                    const response = await PortOne.requestPayment(this.payObject);

                    if (response.code !== undefined) {
                        // 오류 발생
                        return alert(response.message);
                    }

                    console.log(response);

                },
            },
            computed: {
                payObject() {
                    let obj = {
                        storeId: this.info.store_id,
                        channelKey: this.info.channel_key,
                        paymentId: this.payment_id,
                        orderName: "나이키 와플 트레이너 2 SD",
                        totalAmount: 1000,
                        currency: "CURRENCY_KRW",
                        payMethod : this.payMethod,
                    };

                    if(this.payMethod === "VIRTUAL_ACCOUNT") {
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