<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
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

                await this.getLoginUri();
                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {
                goUri() {
                    window.location.href = this.redirect_uri
                },
                async getLoginUri() {
                    try {
                        let res = await this.lib.ajax("login_uri",{},"/JayDream/plugin/naver/api.php",{});
                        this.redirect_uri = res.uri;

                    }catch (e) {
                        await this.lib.alert(e.message)
                    }
                }
            },
            computed: {

            },
            watch: {

            }
        }});
</script>