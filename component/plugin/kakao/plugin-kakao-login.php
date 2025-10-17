<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script src="//developers.kakao.com/sdk/js/kakao.js"></script>
<script type="text/x-template" id="<?=$componentName?>-template">
    <slot></slot>
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

                    row: {},
                    rows : [],

                    redirect_uri : "",
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
            },
            async mounted() {
                await this.getInfo();
                await this.getLoginUri();
                this.load = true;

                this.$nextTick(async () => {
                    if (!window.Kakao.isInitialized()) {
                        Kakao.init(this.info.client_id);

                    }
                });
            },
            updated() {

            },
            methods: {
                async goUri() {
                    if (!window.Kakao || !Kakao.Auth) {
                        await this.lib.alert("카카오 SDK가 로드되지 않았습니다.");
                        return;
                    }

                    Kakao.Auth.authorize({
                        redirectUri: this.redirect_uri,
                    });
                },
                async getLoginUri() {
                    try {
                        let res = await this.lib.ajax("redirect_uri",{},"/JayDream/plugin/kakao/api.php",{});
                        this.redirect_uri = res.uri;

                    }catch (e) {
                        await this.lib.alert(e.message)
                    }
                },
                async getInfo() {
                    try {
                        let res = await this.lib.ajax("info",{},"/JayDream/plugin/kakao/api.php",{});
                        this.info = res.info;

                    }catch (e) {
                        await this.lib.alert(e.message)
                    }
                },
            },
            computed: {

            },
            watch: {

            }
        }});
</script>