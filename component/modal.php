<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">
        <external-bs-modal v-model="modal">
            <template v-slot:header>
                <h5 class="modal-title" id="exampleModalLabel">데이터 입력</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </template>

            <!-- body -->
            <template v-slot:default>

            </template>


            <template v-slot:footer>
                <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary" @click="api.post(row)">저장</button>
            </template>
        </external-bs-modal>
    </div>

    <div v-if="!load"><div class="loader"></div></div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                modelValue : {type: Object, default: {}},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",

                    temp : {
                        $table : "",
                    },
                    row: {},
                    rows : [],
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
            },
            async mounted() {
                this.load = true;

                this.$nextTick(async () => {
                    // 해당부분에 퍼블리싱 라이브러리,플러그인 선언부분 하시면 됩니다 ex) swiper
                });
            },
            updated() {

            },
            methods: {

            },
            computed: {
                modal: {
                    get() {
                        return this.modelValue;
                    },
                    set(value) {
                        this.$emit('update:modelValue', value);
                    }
                }
            },
            watch: {
                async "modelValue.status"(value, old_value) {
                    if(this.modelValue.primary || this.modelValue.row) {
                        let table = this.modelValue.primary && this.modelValue.table || this.modelValue.row?.$table
                        await this.api.table(table).where("primary",this.modelValue.primary || this.modelValue.row.primary).get(this.row)
                    }else {
                        this.row = this.lib.copyObject(this.temp);
                    }
                }
            }
        }});
</script>