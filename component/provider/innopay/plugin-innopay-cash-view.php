<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <div>
        <div v-if="load">
            <div id="container">
                <div id="cashReceipt_view">
                    <ul>
                        <li>
                            <div class="left">현금영수증 금액</div>
                            <div class="right">{{row.amt.format()}}원</div>
                        </li>
                        <li>
                            <div class="left">현금영수증 종류</div>
                            <div class="right">{{row.cashReceipt == 1 ? '소득공제' : '지출증빙'}}</div>
                        </li>
                        <li>
                            <div class="left">발행정보</div>
                            <div class="right">{{row.cashReceipt.receiptTypeNo}}</div>
                        </li>
                        <li>
                            <div class="left">현금영수증 주문번호</div>
                            <div class="right">{{row.cashReceipt.tid}}</div>
                        </li>
                    </ul>
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
                tid: {type: String, default: ""},
            },
            data: function () {
                return {
                    load: false,
                    component_name: "<?=$componentName?>",
                    component_idx: "",

                    row: {},
                    rows: [],

                    filter: {
                        table: "jd_plugin_innopay",

                        where: [
                            {
                                column: "tid",            // join 조건시 user.idx
                                value: this.tid,             // LIKE일시 %% 필수 || relations일시  $parent.idx
                                logical: "AND",        // AND,OR,AND NOT
                                operator: "=",         // = ,!= >= <=, LIKE,
                            },
                        ],
                    },
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
                if (!this.tid) {
                    await this.lib.alert("주문번호가 존재하지않습니다.");
                    history.back();
                    return false;
                }
            },
            async mounted() {
                this.row = await this.$getData(this.filter);

                if (!this.row) {
                    await this.lib.alert("주문데이터가 존재하지않습니다.");
                    history.back();
                    return false;
                }

                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {},
            computed: {},
            watch: {}
        }
    });
</script>