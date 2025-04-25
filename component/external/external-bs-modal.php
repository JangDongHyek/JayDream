<?php $componentName = str_replace(".php","",basename(__FILE__)); ?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div>
        <div class="modal fade" :class="modelValue.class_1" :id="component_idx" tabindex="-1" :aria-hidden="modelValue.status">
            <div class="modal-dialog" :class="modelValue.class_2">
                <template v-if="modelValue.load">
                    <div class="modal-content">
                        <div class="modal-header" v-if="$slots.header">
                            <slot name="header"></slot>
                        </div>
                        <div class="modal-body">
                            <slot></slot>
                        </div>
                        <div class="modal-footer" v-if="$slots.footer">
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                modelValue : {type: Object, default: {}},
            },
            data: function () {
                return {
                    component_idx: "",

                };
            },
            created: function () {
                this.component_idx = this.$jd.lib.generateUniqueId();

            },
            mounted: function () {
                $(`#${this.component_idx}`).on('hide.bs.modal', this.hideModal);

                this.$nextTick(() => {

                });
            },
            methods: {
                hideModal() {
                    let copy = Object.assign({}, this.modelValue);
                    copy.status = false;
                    this.$emit("update:modelValue", copy);
                }
            },
            computed: {},
            watch: {
                "modelValue.status"(value) {
                    if (value) $(`#${this.component_idx}`).modal('show');
                    else {
                        $(`#${this.component_idx}`).modal('hide');
                    }
                }
            }
        }});
</script>

<style>

</style>