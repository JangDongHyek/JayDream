<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">
        <div id="map" style="width:100%;height:350px;"></div>
    </div>

    <div v-if="!load"><div class="loader"></div></div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                primary : {type : String, default : ""},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",
                    injectUrls : [
                        "//dapi.kakao.com/v2/maps/sdk.js?autoload=false&appkey=js키값"
                    ],

                    table : null,
                    row: {},
                    rows : [],
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

                await this.$nextTick();

                kakao.maps.load(() => {
                    // 해당부분에 퍼블리싱 라이브러리,플러그인 선언부분 하시면 됩니다 ex) swiper

                    var mapContainer = document.getElementById('map'), // 지도를 표시할 div
                        mapOption = {
                            center: new kakao.maps.LatLng(33.450701, 126.570667), // 지도의 중심좌표
                            level: 3 // 지도의 확대 레벨
                        };

                    // 지도를 표시할 div와  지도 옵션으로  지도를 생성합니다
                    var map = new kakao.maps.Map(mapContainer, mapOption);
                });
            },
            updated() {

            },
            methods: {

            },
            computed: {

            },
            watch: {

            }
        }});
</script>
