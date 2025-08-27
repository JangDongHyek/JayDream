<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">
        <external-bs-modal v-model="modelValue" @update:modelValue="value => $emit('update:modelValue', value)">
            <template v-slot:header>

            </template>

            <!-- body -->
            <template v-slot:default>

            </template>


            <template v-slot:footer>

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

                    temp : {},
                    row: {},
                    rows : [],
                };
            },
            async created() {
                this.component_idx = this.$jd.lib.generateUniqueId();
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

            },
            watch: {
                async "modelValue.status"(value, old_value) {
                    if (value) {
                        // if(this.modelValue.primary) {
                        //     this.row = await this.$getData({
                        //         table : "category",
                        //         file_db: true,
                        //
                        //         where: [
                        //             {column: "primary",value: this.modelValue.primary},
                        //         ],
                        //     });
                        // }else {
                        //     this.row = this.temp;
                        // }
                    }else {
                        // this.modelValue.parent = null;
                    }
                }
            }
        }});
</script>