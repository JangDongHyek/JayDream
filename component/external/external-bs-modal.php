<?php $componentName = str_replace(".php","",basename(__FILE__)); ?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div>
        <div class="modal fade" :class="modelValue.class_1" :id="component_idx" tabindex="-1" :aria-hidden="!modelValue.status">
            <div class="modal-dialog modal-dialog-centered" :class="modelValue.class_2">
                <template v-if="modelValue.status">
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
                if(this.modelValue.id) {
                    this.component_id = this.modelValue.id
                }else {
                    this.component_idx = this.$jd.lib.generateUniqueId();
                }

            },
            mounted: function () {
                document.getElementById(this.component_idx).addEventListener('hide.bs.modal', this.hideModal);

                this.$nextTick(() => {

                });
            },
            methods: {
                hideModal() {
                    let copy = Object.assign({}, this.modelValue);
                    copy.status = false;
                    copy.primary = "";
                    copy.table = "";
                    copy.row = null;
                    this.$emit("update:modelValue", copy);
                }
            },
            computed: {},
            watch: {
                "modelValue.status"(value) {
                    const modalEl = document.getElementById(this.component_idx);
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                    if (value) {
                        modal.show();
                    } else {
                        modal.hide();
                    }
                }
            }
        }});
</script>

<style>

</style>