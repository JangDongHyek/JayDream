<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/nano.min.css"/>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div ref="picker"></div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                modelValue : {type : String, default : ""},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",

                    picker : null,
                    color : "",
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
                this.api.component_name = this.component_name
            },
            async mounted() {
                // this.table = await this.api.table("exam");
                // await this.table.get(this.rows,{paging : 10})

                this.load = true;

                this.$nextTick(async () => {
                    let component = this;
                    this.picker = Pickr.create({
                        el: this.$refs.picker,
                        theme: 'nano',
                        default: '#000000',
                        components: {
                            preview: true,
                            opacity: true,  // 투명도 지원!
                            hue: true,

                            interaction: {
                                hex: true,
                                input: true,
                            }
                        }
                    });

                    this.picker.on('change', (color) => {
                        const hexColor = color.toHEXA().toString();
                        this.$emit('update:modelValue', hexColor);  // ← 부모에게 전달
                    });
                });
            },
            updated() {

            },
            methods: {

            },
            computed: {

            },
            watch: {
                modelValue(newVal) {
                    if (this.picker && newVal) {
                        this.picker.setColor(newVal);
                    }
                }
            }
        }});
</script>

<style>
    /* Pickr 버튼을 동그랗게 */
    .pcr-button {
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        padding: 0;
    }

    /* 내부 컬러 미리보기도 동그랗게 */
    .pcr-button::before,
    .pcr-button::after {
        border-radius: 50% !important;
    }
</style>