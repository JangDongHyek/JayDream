<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <nav v-if="parseInt(count)" aria-label="Page navigation" class="text-center">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item" :class="{ disabled: table.filter.paging.page <= 1 }">
                <a class="page-link" href="javascript:;" @click="setPage(1)">«</a>
            </li>
            <li class="page-item" :class="{ disabled: table.filter.paging.page <= 1 }">
                <a class="page-link" href="javascript:;" @click="setPage(table.filter.paging.page-1)">‹</a>
            </li>

            <li v-for="index in getPages()" class="page-item" :class="{ active: index == table.filter.paging.page }" :key="index">
                <a class="page-link" href="javascript:;" @click="setPage(index)">{{ index }}</a>
            </li>

            <li class="page-item" :class="{ disabled: table.filter.paging.page >= table.filter.paging.last }">
                <a class="page-link" href="javascript:;" @click="setPage(table.filter.paging.page+1)">›</a>
            </li>
            <li class="page-item" :class="{ disabled: table.filter.paging.page >= table.filter.paging.last }">
                <a class="page-link" href="javascript:;" @click="setPage(table.filter.paging.last)">»</a>
            </li>
        </ul>
    </nav>
</script>

<script>
    JayDream_components.push({
        name: "<?= $componentName ?>", object: {
            template: "#<?= $componentName ?>-template",
            props: {
                rows : { type: Array, default: [] },
                table : { type: String, default: "" },
            },
            methods: {
                getPages() {
                    let current = this.table.filter.paging.page;
                    let last = this.table.filter.paging.last;
                    let min = current - 2;
                    let max = current + 2;

                    if (min < 1) max += (1 - min);
                    if (max > last) min -= (max - last);

                    let pages = [];
                    for (let i = min; i <= max; i++) {
                        if (i >= 1 && i <= last) pages.push(i);
                    }
                    return pages;
                },
                async setPage(page) {
                    if (page < 1) page = 1;
                    if (page > this.table.filter.paging.last) page = this.table.filter.paging.last;
                    this.table.filter.paging.page = page;
                    // this.$emit("change", page);
                    // this.rows = [];
                    await this.table.get(this.rows);

                    this.$forceUpdate();
                },
            },
            watch: {
                // rows가 변경될 때마다 filter 업데이트
                rows: {
                    handler() {
                        this.$forceUpdate();
                    },
                    deep: true
                }
            },
            computed: {
                count() { return this.table?.filter.paging.count ?? 0 },
                limit() { return this.table.filter.paging.limit },
                filter() { return this.table.filter },
            }
        }
    });
</script>
