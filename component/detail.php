<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <div v-if="load">
        <input type="file" @change="$jd.vue.changeFile($event,row,'key_name')" name="names[]">
        <input type="text" @input="$jd.vue.inputNumber"> <!-- 숫자만 입력가능하게 -->

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
                <external-daum-postcode v-model="row" field1="Addr1"
                                        @close="modal.status = false;"></external-daum-postcode>
            </template>


            <template v-slot:footer>

            </template>
        </external-bs-modal>

        <external-summernote :row="row" field="content"></external-summernote>

        <!--  item  -->
        <item-paging :filter="filter" @change="$getsData(filter,rows);"></item-paging>
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
                        id : "", // modal의 id값을 설정합니다 빈값이라면 고유값을 랜덤으로 생성해 지정합니다
                        class_1: "", // modal fade 부분에 클래스를 추가합니다 ex) "one_class two_class"
                        class_2: "", // modal-dialog 부분에 클래스를 추가합니다
                        status: false,
                    },

                    filter: {
                        table: "user",
                        file_db: true, // 연관된 파일들 불러옴

                        page: 1,
                        limit: 1, // 해당 값 수정시 페이지에 노출되는 게시글 갯수가 바뀜
                        count: 0,

                        where: [
                            {
                                column: "",             // join 조건시 user.idx
                                value: ``,              // LIKE일시 %% 필수 || relations일시  $parent.idx
                                logical: "AND",         // AND,OR,AND NOT
                                operator: "=",          // = ,!= >= <=, LIKE,
                                encrypt: false,        // true시 벨류가 암호화된 값으로 들어감
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
                            having: [ // 사용 안할거면 삭제해줘야함
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

                        add_object: [ // 검색되는 모든 데이터에 해당하는 객체를 추가한다 * vue 데이터 선인식때문에
                            {
                                name: "",
                                value: ""
                            },
                        ],

                        relations: [// filter 형식으로 똑같이 넣어주면 하위로 들어간다
                            {
                                table: "",
                                as : "" // 빈값일시 $table 으로 삽입됌
                            }
                        ],

                        blocks: [
                            { // filter 형식으로 넣어주면된다 , 객체 하나당 () 괄호 조건문이 꾸며진다
                                logical: "AND" // 괄호 전 어떤 논리 연사자가 들어갈지
                                where: []
                            },
                        ],

                        fields : [
                            "SUM(column) as sum_column", // 조회하는 부분에 강제 추가한다 조인을 할경우 테이블 명시 필수
                        ]
                    },

                    options: {// 추가,삭제시에만 사용되는 데이터 셋
                        table: "",

                        required: [ // (추가) 필수값 설정
                            // (String)  row.name 값이 빈값일경우 message가 alert으로 노출됌
                            { name: "", message: `` },
                            // (String) min미만 max초과일때  message가 alert으로 노출됌
                            {
                                name: "",
                                message: ``,
                                min: {length: 10, message: ""},
                                max: {length: 30, message: ""}
                            },
                            // (Array) min미만 max초과일때 message가 alert으로 노출됌
                            {
                                name: "",
                                min: {length: 1, message: ""}
                                max: {length: 10, message: ""}
                            },
                        ],

                        href: "", // (추가,삭제) 로직 완료시 이동되는 페이지 빈값이면 새로고침

                        message: "", // (추가,삭제) 추가 : 추가 후 나오는 메세지, 삭제 : 삭제 전 confirm 메세지

                        confirm: { // (추가) 추가전 Confirm 으로 물어보고싶을때
                            message: '',
                            callback: async () => { // 취소 시 실행되는 callback

                            },
                        },

                        hashes: [ // (추가) * 대입방식 row[alias] 값이 암호화되서 row[column]에 대입된다
                            {
                                column: "",
                                alias: "",
                            }
                        ],

                        exists: [ // (추가) 조건에 해당하는 데이터가 있는지 있다면 alert으로 message 노출
                            { // 최상단 filter 방식으로 똑같이 넣어주면된다
                                table: "",
                                message: "",
                            }
                        ],
                    },

                    sessions: {},
                };
            },
            async created() {

            },
            async mounted() {
                this.row = await this.$getData(this.filter);
                await this.$getsData(this.filter, this.rows);

                //session 등록하기
                await this.$jd.lib.ajax("session_set", {
                    name: "exam"
                }, "/JayDream/api.php");

                //session 가져오기
                this.sessions = (await this.$jd.lib.ajax("session_get", {
                    example: "",
                    ss_mb_id: "",
                }, "/JayDream/api.php")).sessions;
            },
            updated() {

            },
            methods: {
                async paySuccess() {
                    let row = {};

                    let options = {}

                    try {
                        let res = await this.$jd.lib.ajax("update", row, "/JayDream/api.php", options);

                        await this.$jd.lib.alert("결제가 완료되었습니다.");
                        this.$jd.lib.href()

                    } catch (e) {
                        await this.$jd.lib.alert(e.message)
                    }
                },
                async restApi() {
                    let row = {};

                    let options = {
                        table: ""
                    }

                    try {
                        let res = await this.$jd.lib.ajax("method", row, "/JayDream/api.php", options);


                    } catch (e) {
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
                    let filter = {
                        table : "",

                        page : this.page,
                        limit : this.limit,
                        count : this.count,
                    }
                    return filter
                },
                pay_core() {
                    return {
                        payMethod: this.payMethod,
                        goodsName: this.order.product_log.name,
                        amt: this.order.price,
                        buyerName: "테스트",
                        buyerTel: this.member.mb_hp.formatOnlyNumber(),
                        buyerEmail: this.member.mb_email,
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