<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
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
                    injectUrls : [
                        "https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js",
                        "https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/nano.min.css"
                    ],

                    picker : null,
                    color : "",
                    syncingFromPicker: false,
                    pickerTextInput: null,
                    pickerTextInputHandler: null,
                    pickerTextChangeHandler: null,
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
                    const defaultColor = this.normalizeColor(this.modelValue);
                    this.color = defaultColor;
                    this.picker = Pickr.create({
                        el: this.$refs.picker,
                        theme: 'nano',
                        default: defaultColor,
                        components: {
                            preview: true,
                            opacity: true,
                            hue: true,

                            interaction: {
                                hex: true,
                                rgba: true,
                                input: true,
                            }
                        }
                    });

                    this.picker.on('change', (color) => {
                        this.emitColor(color);
                    });

                    this.ensurePickerFocusGuard();
                    this.bindPickerTextInput();
                });
            },
            unmounted() {
                this.unbindPickerTextInput();

                if (this.picker) {
                    this.picker.destroyAndRemove();
                    this.picker = null;
                }
            },
            updated() {

            },
            methods: {
                normalizeColor(value) {
                    return value || '#000000';
                },
                normalizeTextColor(value, allowShort) {
                    let nextColor = String(value || '').trim();

                    if (!nextColor) return '';

                    if (nextColor[0] !== '#') {
                        nextColor = '#' + nextColor;
                    }

                    const fullHex = /^#([0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/;
                    const shortHex = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4})$/;

                    if (fullHex.test(nextColor) || (allowShort && shortHex.test(nextColor))) {
                        return nextColor;
                    }

                    return '';
                },
                isPotentialTextColor(value) {
                    let nextColor = String(value || '').trim();

                    if (nextColor[0] === '#') {
                        nextColor = nextColor.slice(1);
                    }

                    return /^[0-9a-fA-F]{0,8}$/.test(nextColor);
                },
                ensurePickerFocusGuard() {
                    if (!window.__jdPickrBootstrapFocusPatched && window.jQuery?.fn?.modal?.Constructor) {
                        const Modal = window.jQuery.fn.modal.Constructor;

                        Modal.prototype.enforceFocus = function () {
                            window.jQuery(document)
                                .off('focusin.bs.modal')
                                .on('focusin.bs.modal', window.jQuery.proxy(function (event) {
                                    if (window.jQuery(event.target).closest('.pcr-app').length) {
                                        return;
                                    }

                                    if (this.$element[0] !== event.target && !this.$element.has(event.target).length) {
                                        this.$element.trigger('focus');
                                    }
                                }, this));
                        };

                        window.__jdPickrBootstrapFocusPatched = true;
                    }

                    const visibleModal = window.jQuery?.('.modal.in, .modal.show')?.last?.();
                    const modalData = visibleModal?.data?.('bs.modal');

                    if (modalData?.enforceFocus) {
                        modalData.enforceFocus();
                    }

                    if (window.__jdPickrFocusGuard) return;

                    window.__jdPickrFocusGuard = (event) => {
                        if (event.target?.closest?.('.pcr-app')) {
                            event.stopImmediatePropagation();
                        }
                    };

                    document.addEventListener('focusin', window.__jdPickrFocusGuard, true);
                },
                emitColor(color) {
                    const hexColor = color.toHEXA().toString();
                    if (hexColor === this.color) return;

                    this.syncingFromPicker = true;
                    this.color = hexColor;
                    this.$emit('update:modelValue', hexColor);
                    this.$nextTick(() => {
                        this.syncingFromPicker = false;
                    });
                },
                syncPickerColor(value) {
                    const nextColor = this.normalizeColor(value);
                    if (!this.picker || nextColor === this.color) return;

                    this.color = nextColor;
                    try {
                        this.picker.setColor(nextColor, true);
                    } catch (e) {
                    }
                },
                applyTextColor(value, allowShort, syncPicker) {
                    const nextColor = this.normalizeTextColor(value, allowShort);
                    if (!nextColor) return;
                    if (nextColor === this.color) {
                        if (syncPicker) {
                            this.syncPickerColor(nextColor);
                        }
                        return;
                    }

                    this.syncingFromPicker = true;
                    this.color = nextColor;

                    if (syncPicker) {
                        try {
                            this.picker.setColor(nextColor, true);
                        } catch (e) {
                        }
                    }

                    this.$emit('update:modelValue', nextColor);
                    this.$nextTick(() => {
                        this.syncingFromPicker = false;
                    });
                },
                bindPickerTextInput() {
                    const root = this.picker?.getRoot?.();
                    const input = root?.interaction?.result;

                    if (!input || this.pickerTextInput === input) return;

                    this.unbindPickerTextInput();

                    this.pickerTextInput = input;
                    this.pickerTextInputHandler = (event) => {
                        if (!this.isPotentialTextColor(event.target.value)) {
                            return;
                        }

                        event.stopImmediatePropagation();
                        this.applyTextColor(event.target.value, false, false);
                    };
                    this.pickerTextChangeHandler = (event) => {
                        this.applyTextColor(event.target.value, true, true);
                    };

                    input.addEventListener('input', this.pickerTextInputHandler, true);
                    input.addEventListener('change', this.pickerTextChangeHandler);
                    input.addEventListener('blur', this.pickerTextChangeHandler);
                },
                unbindPickerTextInput() {
                    if (!this.pickerTextInput) return;

                    this.pickerTextInput.removeEventListener('input', this.pickerTextInputHandler, true);
                    this.pickerTextInput.removeEventListener('change', this.pickerTextChangeHandler);
                    this.pickerTextInput.removeEventListener('blur', this.pickerTextChangeHandler);
                    this.pickerTextInput = null;
                    this.pickerTextInputHandler = null;
                    this.pickerTextChangeHandler = null;
                },
            },
            computed: {

            },
            watch: {
                modelValue(newVal) {
                    if (this.syncingFromPicker) return;
                    this.syncPickerColor(newVal);
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
        border: 2px solid #ddd !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* 내부 컬러 미리보기도 동그랗게 */
    .pcr-button::before,
    .pcr-button::after {
        border-radius: 50% !important;
    }

    /* hover 시 테두리 강조 */
    .pcr-button:hover {
        border-color: #999 !important;
    }

    .pcr-app {
        z-index: 2147483647 !important;
    }

    .pcr-app .pcr-interaction input {
        pointer-events: auto !important;
        user-select: text !important;
        -webkit-user-select: text !important;
    }
</style>
