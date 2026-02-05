<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <div v-if="load">
        <input type="file" @change="vue.changeFile($event,row,'key_name')" name="names[]">
        <input type="text" v-number v-price v-phone v-where="{table,column : 'name'}" v-enter="{table,rows}">
        <select v-where="{table,column : 'name'}"></select>

        <button @click="api.post(row,options)">테스트</button>
        <button @click="api.delete(row,options)">삭제</button>

        <!-- plugin -->
        <plugin-innopay ref="pluginInnopay" :pay_core="pay_core" @paySuccess="paySuccess" redirect_url="/index.php">
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
                <external-daum-postcode v-model="row" @close="modal.status = false;"></external-daum-postcode>
            </template>


            <template v-slot:footer>

            </template>
        </external-bs-modal>

        <external-summernote :row field="content"></external-summernote>

        <!--  item  -->
        <item-paging :rows :table></item-paging>

        <!-- Vue -->
        <draggable v-model="ArrayObject" item-key="primary" @end="(e) => onDragEnd(e,ArrayObject)" tag="ul">
            <template #item="{ element : item, index }">
                <li>
                    <p>{{ item.name }}</p>
                    <a @click="deleteContent(index)" style="float:right;">&times;</a>
                </li>
            </template>
        </draggable>
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
                    table : null,
                    row: {
                        // jd_file_primary_{$keyword} : 해당하는 키워드 파일의 primary 값으로 해당 컬럼 자동으로 업데이트
                        // jd_file_primary : 파일의 primary 값들을 배열 형태로 해당 컬럼 자동으로 업데이트
                        exam_file_idx : ""
                    },
                    rows: [],

                    modal: {
                        id : "", // modal의 id값을 설정합니다 빈값이라면 고유값을 랜덤으로 생성해 지정합니다
                        class_1: "", // modal fade 부분에 클래스를 추가합니다 ex) "one_class two_class"
                        class_2: "", // modal-dialog 부분에 클래스를 추가합니다
                        status: false,
                        table : "",
                        primary : "",
                        row : null,
                    },

                    filter: {
                        table: "user",
                        file_db: true, // 연관된 파일들 불러옴
                        file_keywords : [""], // 해당 키워드별로 따로 가져옴

                        where: [
                            {column: "",value: ``},
                        ],

                        where: [
                            {
                                column: "",             // join 조건시 user.idx
                                value: ``,              // LIKE일시 %% 필수 || relations일시  $parent.idx , 공백일경우 __null__ , null 값인경우 null
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
                                table: "table",
                                base: "idx",               // filter 테이블의 연결 key
                                foreign: "",            // join 테이블의 연결 key 빈값이면 연결 테이블의 primary로 자동으로 들어간다
                                type: "LEFT",             // INNER, LEFT, RIGHT
                                select_column: ["column"], // 조회할 컬럼 $table__column 식으로 as되서 들어간다 || "*"
                                as : "", // 값이 있을경우 $as__column 해당방식으로 들어감
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
                            { column: "idx", value: "DESC" },
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
                                as : "", // 빈값일시 $table 으로 삽입됌
                            },
                        ],

                        blocks: [
                            { // filter 형식으로 넣어주면된다 , 객체 하나당 () 괄호 조건문이 꾸며진다
                                keyword : "",   // api 에서 사용하는 키값 사용안해두 문제없음
                                logical: "AND", // 괄호 전 어떤 논리 연사자가 들어갈지
                                where: [],
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

                        confirm: { // (추가,삭제) 로직전 Confirm 으로 물어보고싶을때
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
                        
                        return : false, // true 시 결과값 리턴후 아무것도안함
                    },

                    sessions: {},
                };
            },
            async created() {

            },
            async mounted() {
                this.table = await this.api.table("theme");
                await this.table.get(this.rows,{paging : 10})

                // 그룹 조건
                // this.table.blockWhere("main_search","name",value);
                // this.table.blockWhere("main_search","sub_url",$event.target.value,"OR","LIKE");
                this.table.blockStart("main_search")
                this.table.where("name",$event.target.value,"OR","LIKE")
                this.table.where("sub_url",$event.target.value,"OR","LIKE")
                this.table.blockEnd()

                //session 등록하기
                await this.session.set("exam",'value') // string,array,object

                //session 가져오기
                this.exam = await this.session.get("exam") // string,array,object
                this.exam = await this.session.all()

                //조건 삭제
                this.api.whereDelete({
                    where: [
                        {
                            column: "",             // join 조건시 user.idx
                            value: ``,              // LIKE일시 %% 필수 || relations일시  $parent.idx , 공백일경우 __null__ , null 값인경우 null
                            logical: "AND",         // AND,OR,AND NOT
                            operator: "=",          // = ,!= >= <=, LIKE,
                            encrypt: false,        // true시 벨류가 암호화된 값으로 들어감
                        },
                    ],
                },{
                    table : "",
                })
            },
            updated() {

            },
            methods: {
                exam(...args) {
                    let defaults = {
                        name : "",
                        age : 10,
                    }

                    let {name,age} = this.lib.args(defaults,...args);
                },
                async onDragEnd(evt,array) {
                    if (evt.oldIndex === evt.newIndex) return;

                    const movedItem = array[evt.newIndex];

                    const start = Math.min(evt.oldIndex, evt.newIndex);
                    const end = Math.max(evt.oldIndex, evt.newIndex);

                    // console.log(`이동된 범위: ${start} ~ ${end}`);

                    for (let i = start; i <= end; i++) {
                        const item = array[i];
                        item.priority = i;

                        this.$postData(item,{return : true});
                        // console.log(`수정 대상 idx ${i}:`, item);
                    }

                    // console.log('드래그 완료:', evt.oldIndex, '→', evt.newIndex);
                },
                async paySuccess(data) {
                    //data.tid // 거래 결제번호
                    //data.moid // 거래 주문번호

                    let order = await this.$getData({
                        table : "orders",

                        where: [
                            {column: "moid",value: data.moid},
                        ],
                    });

                    order.tid = data.tid;
                    try {
                        await this.$postData(order,{
                            message : "결제가 완료되었습니다.",
                            href : ""
                        });
                    } catch (e) {
                        await this.lib.alert(e.message)
                    }
                },
            },
            computed: {
                pay_core() {
                    return {
                        payMethod: this.payMethod,
                        goodsName: this.order.product_log.name,
                        amt: this.order.price,
                        buyerName: "테스트",
                        buyerTel: this.member.mb_hp.formatOnlyNumber(),
                        buyerEmail: this.member.mb_email,
                        // 주문데이터에 해당 값이 있어야 미리 주문을 만듬 * 결제리턴시 새로고침되어 유지되어있는값이 초기화되는형상떄문에 데이터를 만든후 업데이트로 작업해야함
                        moid : this.order.moid,
                    }
                },
                global() { // 내 앱의 글로벌 변수 접근하는 변수
                    return window.JAYDREAM_VUE_GLOBAL[this.$jd.app];
                }
            },
            watch: {
                global : {
                    async handler(newVal) {
                        console.log('내 앱 상태가 바뀜', newVal);
                    },
                    deep: true
                },
                async "object.key"(value, old_value) {

                }
            }
        }
    });

</script>

<style>

</style>