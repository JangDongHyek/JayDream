<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div class="b-pagination-outer" v-if="parseInt(count)">
        <ul id="border-pagination">
            <li>
                <a @click="setPage(1)">«</a>
            </li>
            <li>
                <a @click="setPage(page-1)">‹</a>
            </li>
            <template v-for="index in getPages()">
                <li>
                    <a @click="setPage(index)" :class="{'active' : index == page}">{{index}}</a>
                </li>
            </template>
            <li>
                <a @click="setPage(page+1)">›</a>
            </li>
            <li>
                <a @click="setPage(last)">»</a>
            </li>
        </ul>
    </div>

</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                filter: {type: Object, default: null},
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

            },
            updated() {

            },
            methods: {
                getPages: function () {
                    var current = this.current;
                    var last = this.last;
                    var offset = 0;
                    var min = current - 2;
                    var max = current + 2;

                    if (min < 1) offset = 1 - min;
                    if (max > last) offset = last - max;

                    var pages = [];
                    for (var i = min + offset; i <= max + offset; i++) {
                        if (1 <= i && i <= last) pages.push(i);
                    }
                    return pages;
                },
                setPage: function (page) {
                    if (page < 1) page = 1;
                    else if (page > this.last) page = this.last;

                    this.page = page;
                    this.filter.page = page;
                    this.$emit("change", page);
                }
            },
            computed: {
                count: function () {
                    return this.filter.count
                },
                limit: function () {
                    return this.filter.limit
                },
                page: function () {
                    return this.filter.page
                },
                current: function () {
                    return parseInt(this.page);
                },
                last: function () {
                    return Math.ceil(this.count / this.limit);
                }
            },
            watch: {

            }
        }});
</script>