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
                    this.component_idx = this.lib.generateUniqueId();
                }

            },
            mounted: function () {
                document.getElementById(this.component_idx).addEventListener('hide.bs.modal', this.hideModal);
                // Bootstrap 3 방식도 추가 (보험용)
                $('#' + this.component_idx).on('hide.bs.modal', this.hideModal);

                this.$nextTick(() => {

                });
            },
            methods: {
                hideModal() {
                    console.log(1133);
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

                    if (value) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        } else if (typeof $ !== 'undefined' && $.fn.modal) {
                            $('#' + this.component_idx).modal('show');
                        }

                        // 열릴 때 실행할 로직
                    } else {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.hide();
                        } else if (typeof $ !== 'undefined' && $.fn.modal) {
                            $('#' + this.component_idx).modal('hide');
                        }

                        // 닫힐 때 실행할 로직
                    }
                }
            }
        }});
</script>

<style>

</style>