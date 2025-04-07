<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">

    </div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {

            },
            data: function () {
                return {
                    row: {},
                    rows : [],

                    filter : {
                        table : "user",

                        page : 1,
                        limit : 1,
                        count : 0,

                        where : [
                            {
                                column : "",            // join 조건시 user.idx
                                value : "",             // LIKE일시 %% 필수 || relations일시  $parent.idx
                                logical : "AND",        // AND,OR,AND NOT
                                operator : "=",         // = ,!= >= <=, LIKE,
                            }
                        ],

                        between : [
                            {
                                column : "CURDATE()",   // 컬럼 || 함수
                                start : "column",       // 시간 || 컬럼
                                end: "column",          // 시간 || 컬럼
                                logical : "AND",
                            }
                        ],

                        in : [
                            {
                                column : "",
                                value : [],
                                logical : "AND"
                            }
                        ],

                        joins : [
                            {
                                table : "payment",
                                base : "idx",               // user 테이블의 idx
                                foreign : "user_idx",       // payment 테이블의 user_idx
                                type : "INNER",             // INNER, LEFT, RIGHT
                                select_column : ["column"], // 조회할 컬럼 payment__column 식으로 as되서 들어간다
                                on : [ // 안할거면 삭제해줘야함
                                    {
                                        column : "",        // 해당하는 테이블의 컬럼만 사용해야함
                                        value : "",
                                        logical : "AND",
                                        operator : "=",
                                    }
                                ]
                            }
                        ],

                        order_by : [
                            {
                                column : "idx" ,
                                value : "DESC"
                            },
                        ],

                        add_object : [
                            {
                                name : "",
                                value : ""
                            },
                        ]

                        relations : [
                            {} // filter 형식으로 똑같이 넣어주면 하위로 들어간다
                        ]
                    },

                    post_options : {
                        table : "",

                        required : [
                            {name : "",message : ``}, //simple
                            {//String
                                name : "",
                                message : ``,
                                min : {length : 10, message : ""},
                                max : {length : 30, message : ""}
                            },
                            {//Array
                                name : "",
                                min : {length : 1, message : ""}
                                max : {length : 10, message : ""}
                            },
                        ],

                        href : "",

                        confirm : {
                            message : '',
                            callback : async () => { // false 시 실행되는 callback

                            },
                        },

                        exists : [
                            {// filter 형식으로 똑같이 넣어주면 하위로 들어간다
                                message : "",
                            }
                        ],
                    },

                    modal : {
                        status : false,
                        load : false,
                        primary : "",
                        data : {},
                        class_1 : "",
                        class_2 : "",
                    },
                };
            },
            async created() {

            },
            async mounted() {
                //await this.row = this.$getData(this.filter);
                //await this.$getsData(this.filter,this.rows);
            },
            updated() {

            },
            methods: {

            },
            computed: {

            },
            watch: {
                async "modal.status"(value,old_value) {
                    if(value) {
                        this.modal.load = true;
                    }else {
                        this.modal.load = false;
                        this.modal.data = {};
                    }
                }
            }
        }});

</script>

<style>

</style>