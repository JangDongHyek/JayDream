<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <nav v-if="parseInt(count)" aria-label="Page navigation">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item" :class="{ disabled: page <= 1 }">
                <a class="page-link" href="javascript:;" @click="setPage(1)">«</a>
            </li>
            <li class="page-item" :class="{ disabled: page <= 1 }">
                <a class="page-link" href="javascript:;" @click="setPage(page-1)">‹</a>
            </li>

            <li v-for="index in getPages()" class="page-item" :class="{ active: index == page }" :key="index">
                <a class="page-link" href="javascript:;" @click="setPage(index)">{{ index }}</a>
            </li>

            <li class="page-item" :class="{ disabled: page >= last }">
                <a class="page-link" href="javascript:;" @click="setPage(page+1)">›</a>
            </li>
            <li class="page-item" :class="{ disabled: page >= last }">
                <a class="page-link" href="javascript:;" @click="setPage(last)">»</a>
            </li>
        </ul>
    </nav>
</script>

<script>
    JayDream_components.push({
        name: "<?= $componentName ?>", object: {
            template: "#<?= $componentName ?>-template",
            props: {
                paging: { type: Object, default: null },
            },
            methods: {
                getPages() {
                    let current = this.current;
                    let last = this.last;
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
                setPage(page) {
                    if (page < 1) page = 1;
                    if (page > this.last) page = this.last;
                    this.paging.page = page;
                    this.$emit("change", page);
                }
            },
            computed: {
                count() { return this.paging.count },
                limit() { return this.paging.limit },
                page() { return this.paging.page },
                current() { return parseInt(this.page) },
                last() { return Math.ceil(this.count / this.limit) }
            }
        }
    });
</script>
