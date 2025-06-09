<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <div v-if="load">
        <input type="file" @change="$jd.vue.changeFile($event,row,'key_name')" name="names[]">
        <input type="text" @input="$jd.vue.onInput"> <!-- 숫자만 입력가능하게 -->

        <button @click="$postData(row,options)">테스트</button>
        <button @click="$deleteData(row,options)">삭제</button>

        <!-- plugin -->
        <plugin-innopay ref="pluginInnopay" :pay_core="pay_core" @paySuccess="paySuccess()" redirect_url="/index.php">
            <template v-slot:default>
                <button @click="$refs.pluginInnopay.pay()">결제</button>
            </template>
        </plugin-innopay>

        <plugin-kakao-login ref="pluginKakaoLogin">
            <template v-slot:default>
                <button @click="$refs.pluginKakaoLogin.goUri();"></button>
            </template>
        </plugin-kakao-login>

        <plugin-naver-login ref="pluginNaverLogin">
            <template v-slot:default>
                <button @click="$refs.pluginNaverLogin.goUri();"></button>
            </template>
        </plugin-naver-login>

        <!-- ref로 접근해 taxInvoice()함수 실행해야함 -->
        <plugin-barobill-tax-invoice ref="pluginBarobillTaxInvocue"></plugin-barobill-tax-invoice>

        <!-- external -->
        <external-bs-modal v-model="modal">
            <template v-slot:header>

            </template>

            <!-- body -->
            <template v-slot:default>
                <external-daum-postcode v-model="row" field1="Addr1" @close="modal.status = false;"></external-daum-postcode>
            </template>


            <template v-slot:footer>

            </template>
        </external-bs-modal>

        <external-summernote :row="row" field="content"></external-summernote>
    </div>
</script>

<script>
    JayDream_components.push({
        name: "<?=$componentName?>", object: {
            template: "#<?=$componentName?>-template",
            props: {
                primary: {type: String, default: ""}
            },
            data: function () {
                return {
                    row: {},
                    rows: [],

                    modal: {
                        status: false,
                        class_1: "",
                        class_2: "",
                    },

                    filter : {
                        table: "user",
                        file_db: true, // 연관된 파일들 불러옴

                        page: 1,
                        limit: 1,
                        count: 0,

                        where: [
                            {
                                column: "",             // join 조건시 user.idx
                                value: "",              // LIKE일시 %% 필수 || relations일시  $parent.idx
                                logical: "AND",         // AND,OR,AND NOT
                                operator: "=",          // = ,!= >= <=, LIKE,
                                encrypt : false,        // true시 벨류가 암호화된 값으로 들어감
                            },
                        ],

                        between: [
                            {
                                column: "CURDATE()",   // 컬럼 || 함수
                                start: "column",       // 시간 || 컬럼
                                end: "column",          // 시간 || 컬럼
                                logical: "AND",
                            },
                        ],

                        in: [
                            {
                                column: "",
                                value: [],
                                logical: "AND"
                            },
                        ],

                        joins: [
                            {
                                table: "payment",
                                base: "idx",               // filter 테이블의 연결 key
                                foreign: "idx",            // join 테이블의 연결 key
                                type: "INNER",             // INNER, LEFT, RIGHT
                                select_column: ["column"], // 조회할 컬럼 $payment__column 식으로 as되서 들어간다 || "*"
                                on: [ // 안할거면 삭제해줘야함
                                    {
                                        column: "",        // 해당하는 테이블의 컬럼만 사용해야함
                                        value: "",
                                        logical: "AND",
                                        operator: "=",
                                    },
                                ]
                            },
                        ],

                        group_bys: {
                            by: ['user.idx'], // 그룹화 할 컬럼 * 앞에 테이블명시는 필수
                            selects: [
                                {
                                    type: "SUM", // 집계함수
                                    column: "idx", // 집계함수 할 컬럼
                                    as: "total_sum", // 필수값
                                },
                            ],
                            having: [ // 안할거면 삭제해줘야함
                                {
                                    column: "",//as 사용가능, 컬럼으로 사용시 앞에 테이블명시 필수
                                    value: "",
                                    logical: "AND",
                                    operator: "=",
                                },
                            ]
                        },

                        order_by: [
                            {
                                column: "idx",
                                value: "DESC"
                            },
                        ],

                        add_object: [
                            {
                                name: "",
                                value: ""
                            },
                        ],

                        relations: [// filter 형식으로 똑같이 넣어주면 하위로 들어간다
                            {
                                table: "",
                            }
                        ],

                        blocks : [
                            { // filter 형식으로 넣어주면된다 , 객체 하나당 () 괄호 조건문이 꾸며진다
                                logical : "AND" // 괄호 전 어떤 논리 연사자가 들어갈지
                                where : []
                            },
                        ]
                    },

                    options : {
                        table: "", // post || delete

                        required: [ // post
                            {name: "", message: ``}, //simple
                            {//String
                                name: "",
                                message: ``,
                                min: {length: 10, message: ""},
                                max: {length: 30, message: ""}
                            },
                            {//Array
                                name: "",
                                min: {length: 1, message: ""}
                                max: {length: 10, message: ""}
                            },
                        ],

                        href: "", // post || delete
                        message : "", // delete

                        confirm: { // post
                            message: '',
                            callback: async () => { // false 시 실행되는 callback

                            },
                        },

                        hashes : [ // post * 대입방식 row[alias] 값이 암호화되서 row[column]에 대입된다
                            {
                                column : "",
                                alias : "",
                            }
                        ],

                        exists: [ // post
                            { // 필터방식
                                table : "",
                                message: "",
                            }
                        ],
                    },

                    sessions : {},
                };
            },
            async created() {

            },
            async mounted() {
                this.row = await this.$getData(this.filter);
                await this.$getsData(this.filter, this.rows);

                //session 등록하기
                await this.$jd.lib.ajax("session_set",{
                    name : "exam"
                },"/JayDream/api.php");

                //session 가져오기
                this.sessions = (await this.$jd.lib.ajax("session_get",{
                    example : "",
                    ss_mb_id : "",
                },"/JayDream/api.php")).sessions;
            },
            updated() {

            },
            methods: {
                async paySuccess() {
                    let row = {};

                    let options = {}

                    try {
                        let res = await this.$jd.lib.ajax("update",row,"/JayDream/api.php",options);

                        await this.$jd.lib.alert("결제가 완료되었습니다.");
                        this.$jd.lib.href()

                    }catch (e) {
                        await this.$jd.lib.alert(e.message)
                    }
                },
                async restApi() {
                    let row = {

                    };

                    let options = {
                        table : ""
                    }

                    try {
                        let res = await this.$jd.lib.ajax("method",row,"/JayDream/api.php",options);


                    }catch (e) {
                        await this.$jd.lib.alert(e.message)
                    }
                }
            },
            computed: {
                options() {
                    let options =

                    return options
                },
                filter() {
                    let filter =
                    return filter
                },
                pay_core() {
                    return {
                        payMethod : this.payMethod,
                        goodsName : this.order.product_log.name,
                        amt : this.order.price,
                        buyerName : "테스트",
                        buyerTel : this.member.mb_hp.formatOnlyNumber(),
                        buyerEmail : this.member.mb_email,
                    }
                }
            },
            watch: {
                async "object.key"(value, old_value) {
                    if (value) {
                        this.modal.load = true;
                    } else {
                        this.modal.load = false;
                        this.modal.data = {};
                    }
                }
            }
        }
    });

</script>

<style>

</style>