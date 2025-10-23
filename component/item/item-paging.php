<?php
$componentName = str_replace(".php", "", basename(__FILE__));
?>
<script type="text/x-template" id="<?= $componentName ?>-template">
    <nav v-if="parseInt(count)" aria-label="Page navigation">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item" :class="{ disabled: this.localPaging.page <= 1 }">
                <a class="page-link" href="javascript:;" @click="setPage(1)">«</a>
            </li>
            <li class="page-item" :class="{ disabled: this.localPaging.page <= 1 }">
                <a class="page-link" href="javascript:;" @click="setPage(this.localPaging.page-1)">‹</a>
            </li>

            <li v-for="index in getPages()" class="page-item" :class="{ active: index == this.localPaging.page }" :key="index">
                <a class="page-link" href="javascript:;" @click="setPage(index)">{{ index }}</a>
            </li>

            <li class="page-item" :class="{ disabled: this.localPaging.page >= this.localPaging.last }">
                <a class="page-link" href="javascript:;" @click="setPage(this.localPaging.page+1)">›</a>
            </li>
            <li class="page-item" :class="{ disabled: this.localPaging.page >= this.localPaging.last }">
                <a class="page-link" href="javascript:;" @click="setPage(this.localPaging.last)">»</a>
            </li>
        </ul>
    </nav>
</script>

<script>
    JayDream_components.push({
        name: "<?= $componentName ?>", object: {
            template: "#<?= $componentName ?>-template",
            props: {
                filter : { type: Object, default: null },
            },
            setup(props) {
                const localPaging = Vue.toRef(props.filter, 'paging')
                return { localPaging };
            },
            methods: {
                getPages() {
                    let current = this.localPaging.page;
                    let last = this.filter.paging.last;
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
                    if (page > this.filter.paging.last) page = this.filter.paging.last;
                    this.filter.paging.page = page;
                    this.$emit("change", page);
                }
            },
            computed: {
                count() { return this.localPaging?.count ?? 0 },
                limit() { return this.localPaging?.limit },
            }
        }
    });
</script>
