<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div class="paging" v-if="parseInt(count)">
        <div class="pagingWrap">
            <a class="first" @click="setPage(1)">
                <i class="fa-light fa-angles-left"></i>
            </a>
            <a class="prev" @click="setPage(page-1)">
                <i class="fa-light fa-angle-left"></i>
            </a>
            <template v-for="index in getPages()">
                <a @click="setPage(index)" :class="{'active': index == page}">{{ index }}</a>
            </template>
            <a class="next" @click="setPage(page+1)">
                <i class="fa-light fa-angle-right"></i>
            </a>
            <a class="last" @click="setPage(last)">
                <i class="fa-light fa-angles-right"></i>
            </a>
        </div>
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

<style>
    .paging {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 20px 0;
        font-family: Arial, sans-serif;
    }

    .pagingWrap a {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin: 0 5px;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
    }

    .pagingWrap a:hover {
        background-color: #007BFF;
        color: #fff;
    }

    .pagingWrap a.active {
        background-color: #007BFF;
        color: #fff;
        border-color: #007BFF;
    }

    .pagingWrap svg {
        width: 16px;
        height: 16px;
        fill: #333;
        transition: fill 0.3s;
    }

    .pagingWrap a:hover svg {
        fill: #fff;
    }
</style>
