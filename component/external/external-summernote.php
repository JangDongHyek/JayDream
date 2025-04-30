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
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",
                };
            },
            async created() {
                this.component_idx = this.$jd.lib.generateUniqueId();
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
                                // 기본 툴바 설정
                                ['style', ['style']],
                                ['font', ['bold', 'underline', 'clear']],
                                ['fontsize', ['fontsize']],
                                ['color', ['color']],
                                ['para', ['paragraph']], //'ul', 'ol',
                                ['insert', ['picture', 'link']], // 이미지 삽입 버튼 추가
                                // 플러그인 버튼 추가
                                ['view', ['undo', 'redo']],
                            ],
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
                                    // 업로드된 파일 처리
                                    for (let i = 0; i < files.length; i++) {
                                        await component.uploadImage(files[i], this);
                                    }
                                },
                                onChange: function(contents) {
                                    component.row[component.field] = contents; // Vue 데이터 동기화
                                }
                            }


                        });

                        if(component.row[component.field]) $(`#${component.component_idx}`).summernote('code', component.row[component.field]); // 에디터 내용 불러오기

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
                        let res = await this.$jd.lib.ajax(method,data,"/JayDream/api.php",options);

                        $(editor).summernote('insertImage', `${this.$jd.url}/${res.file.src}`);
                    }catch (e) {
                        alert(e.message)
                    }

                }
            },
            computed: {

            },
            watch: {

            }
        }});
</script>