<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">

    </div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                primary : { type: String, default: "" },
            },
            data: function () {
                return {
                    load : false,
                    component_idx: "",

                    row: {},
                    rows : [],

                    modal : {
                        status : false,
                        load : false,
                        primary : "",
                        data : {},
                        class_1 : "",
                        class_2 : "",
                    },
                };
            },
            async created() {
                //this.component_idx = this.jl.generateUniqueId();
            },
            async mounted() {
                this.load = true;

                this.$nextTick(() => {

                });
            },
            updated() {

            },
            methods: {

            },
            computed: {

            },
            watch: {
                async "modal.status"(value,old_value) {
                    if(value) {
                        this.modal.load = true;
                    }else {
                        this.modal.load = false;
                        this.modal.data = {};
                    }
                }
            }
        }});

</script>

<style>

</style>