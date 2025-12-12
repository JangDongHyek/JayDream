<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">
        <div :id="component_idx"></div>
    </div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                primary : {type : String, default : ""},
                row : {type : Object, default : null},
                field : {type : String, default : ""},
                customEvent : {type : Function, default : null},
                colorEvent : {type : Function, default : null},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
            },
            async mounted() {
                let component = this;

                this.load = true;

                this.$nextTick(() => {
                    $(document).ready(function() {
                        $(`#${component.component_idx}`).summernote({
                            height: 400,
                            lang: 'ko-KR',
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'underline', 'clear']],
                                ['fontsize', ['fontsize']],
                                ['color', ['color']],
                                ['para', ['paragraph']],
                                ['insert', ['picture', 'link']],
                                ['view'],
                                ['custom', ['bgColorButton', 'customButton']],
                            ],
                            buttons : {
                                bgColorButton: function(context) {
                                    if (!component.colorEvent) return null;
                                    var ui = $.summernote.ui;

                                    // 드롭다운 버튼 그룹
                                    var button = ui.buttonGroup([
                                        ui.button({
                                            className: 'dropdown-toggle',
                                            contents: '<i class="fas fa-palette" title="백그라운드 컬러 변경"></i> <span class="caret"></span>',
                                            tooltip: '배경색',
                                            data: {
                                                toggle: 'dropdown'
                                            }
                                        }),
                                        ui.dropdown({
                                            className: 'dropdown-menu external-summernote-dropdown-color-picker',
                                            contents: `
                                                <div style="padding: 10px; min-width: 250px;">
                                                    <div class="form-group">
                                                        <label style="font-size: 12px; margin-bottom: 5px;">색상 선택</label>
                                                        <input type="color" id="bgColorInput" class="form-control" value="#d9edf78c" style="height: 40px; cursor: pointer;">
                                                    </div>
                                                    <div class="external-summernote-color-preset-grid">
                                                        <div class="external-summernote-preset-color" data-color="transparent" style="background: transparent; position: relative;" title="투명 (배경 없음)">
                                                            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 24px; color: #dc3545; font-weight: bold;">×</span>
                                                        </div>
                                                        <div class="external-summernote-preset-color" data-color="#d9edf78c" style="background: #d9edf78c;" title="연한 하늘 (기본)"></div>
                                                        <div class="external-summernote-preset-color" data-color="#fff3cd8c" style="background: #fff3cd8c;" title="연한 노랑"></div>
                                                        <div class="external-summernote-preset-color" data-color="#d4edda8c" style="background: #d4edda8c;" title="연한 민트"></div>
                                                        <div class="external-summernote-preset-color" data-color="#f8d7da8c" style="background: #f8d7da8c;" title="연한 핑크"></div>
                                                        <div class="external-summernote-preset-color" data-color="#e2e3e58c" style="background: #e2e3e58c;" title="연한 회색"></div>
                                                        <div class="external-summernote-preset-color" data-color="#d1ecf18c" style="background: #d1ecf18c;" title="연한 청록"></div>
                                                    </div>
                                                </div>
                                            `,
                                            callback: function($dropdown) {
                                                // color input 변경
                                                $dropdown.find('#bgColorInput').on('change', function() {
                                                    const color = $(this).val();
                                                    const colorWithAlpha = color + 'CC'; // 80% 투명도

                                                    if (component.colorEvent) {
                                                        component.colorEvent(colorWithAlpha);
                                                    }
                                                });

                                                // 프리셋 색상 클릭
                                                $dropdown.find('.external-summernote-preset-color').on('click', function(e) {
                                                    e.preventDefault();
                                                    const color = $(this).data('color');

                                                    if (component.colorEvent) {
                                                        component.colorEvent(color);
                                                    }

                                                    // 드롭다운 닫기
                                                    $dropdown.closest('.dropdown').removeClass('open');
                                                });

                                                // 드롭다운 내부 클릭시 닫히지 않게
                                                $dropdown.on('click', function(e) {
                                                    e.stopPropagation();
                                                });
                                            }
                                        })
                                    ]);

                                    return button.render();
                                },
                                customButton: function(context) {
                                    if (!component.customEvent) return null;

                                    var ui = $.summernote.ui;

                                    var button = ui.button({
                                        contents: '<i class="fas fa-save"></i> 저장',
                                        tooltip: '내용 저장',
                                        className: 'external-summernote-btn-save-custom',
                                        click: async function() {
                                            component.customEvent();
                                        }
                                    });

                                    return button.render();
                                }
                            },
                            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '28', '30', '36', '50', '72'],
                            placeholder: '내용을 입력해 주세요',
                            popover: {
                                image: [
                                    ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                                    ['float', ['floatLeft', 'customFloatCenter', 'floatRight', 'floatNone']],
                                    ['remove', ['removeMedia']]
                                ]
                            },
                            callbacks: {
                                onImageUpload: async function(files) {
                                    for (let i = 0; i < files.length; i++) {
                                        await component.uploadImage(files[i], this);
                                    }
                                },
                                onChange: function(contents) {
                                    component.row[component.field] = contents;
                                }
                            }
                        });

                        if(component.row[component.field]) $(`#${component.component_idx}`).summernote('code', component.row[component.field]);

                    });
                });
            },
            updated() {

            },
            methods: {
                async uploadImage(file,editor) {
                    let method = "file_save";
                    let data = {
                        upfile : file,
                    };
                    let options = {
                        table : "summernote"
                    };
                    try {
                        let res = await this.lib.ajax(method,data,"/JayDream/api",options);
                        $(editor).summernote('insertImage', `${res.file.src}`);
                    }catch (e) {
                        alert(e.message)
                    }
                }
            },
            computed: {

            },
            watch: {
                'row.primary'(newVal, oldVal) {
                    if (!newVal) return;
                    this.$nextTick(() => {
                        const $el = $(`#${this.component_idx}`);
                        if (!$el.length) return;
                        const html = (this.row && this.row[this.field]) ? this.row[this.field] : '';
                        $el.summernote('code', html);
                    });
                }
            }
        }});
</script>

<style>
    .external-summernote-btn-save-custom {
        background-color: #28a745 !important;
        color: white !important;
        border-color: #28a745 !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .external-summernote-btn-save-custom:hover {
        background-color: #218838 !important;
        border-color: #1e7e34 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .external-summernote-btn-save-custom:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .external-summernote-btn-save-custom .fa-spinner {
        animation: external-summernote-spin 1s linear infinite;
    }

    @keyframes external-summernote-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* 컬러피커 드롭다운 */
    .external-summernote-dropdown-color-picker {
        padding: 0 !important;
    }

    .external-summernote-color-preset-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 10px;
    }

    .external-summernote-preset-color {
        width: 100%;
        height: 40px;
        border: 2px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .external-summernote-preset-color:hover {
        border-color: #007bff;
        transform: scale(1.05);
    }
</style>