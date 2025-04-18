<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <div v-if="load">
        <input type="file" @change="$jd.vue.changeFile($event,row,'key_name')">
        <input type="text" @input="$jd.vue.onInput"> <!-- 숫자만 입력가능하게 -->

        <button @click="$postData(row,options)">테스트</button>
        <button @click="$deleteData(row,options)">삭제</button>

        <!-- plugin -->
        <plugin-innopay ref="innopay" :pay_core="pay_core">
            <template v-slot:default>
                <button @click="$refs.innopay.pay()">결제</button>
            </template>
        </plugin-innopay>
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
                        load: false,
                        primary: "",
                        data: {},
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
                                column: "",            // join 조건시 user.idx
                                value: "",             // LIKE일시 %% 필수 || relations일시  $parent.idx
                                logical: "AND",        // AND,OR,AND NOT
                                operator: "=",         // = ,!= >= <=, LIKE,
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
                                base: "idx",               // 갑 테이블의 연결 key
                                foreign: "idx",            // 을 테이블의 연결 key
                                type: "INNER",             // INNER, LEFT, RIGHT
                                select_column: ["column"], // 조회할 컬럼 $payment__column 식으로 as되서 들어간다
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

                        exists: [ // post * 필터방식
                            {
                                table : "",
                                message: "",
                            }
                        ],
                    },
                };
            },
            async created() {

            },
            async mounted() {
                this.row = await this.$getData(this.filter);
                await this.$getsData(this.filter, this.rows);
            },
            updated() {

            },
            methods: {},
            computed: {
                options() {
                    let options =

                    return options
                },
                filter() {
                    let filter =
                    return filter
                },
            },
            watch: {
                async "modal.status"(value, old_value) {
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