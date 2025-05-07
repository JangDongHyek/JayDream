<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div>
        <div v-if="load">
            <a :href="redirect_uri">카카오 로그인</a>
        </div>

        <div v-if="!load"></div>
    </div>
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
                this.component_idx = this.$jd.lib.generateUniqueId();
            },
            async mounted() {

                await this.getLoginUri();
                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {
                async getLoginUri() {
                    try {
                        let res = await this.$jd.lib.ajax("login_uri",{},"/JayDream/plugin/kakao/api.php",{});
                        this.redirect_uri = res.uri;

                    }catch (e) {
                        await this.$jd.lib.alert(e.message)
                    }
                }
            },
            computed: {

            },
            watch: {

            }
        }});
</script>