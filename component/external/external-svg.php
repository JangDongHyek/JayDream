<?php $componentName = str_replace(".php","",basename(__FILE__)); ?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div>
        <external-bs-modal v-model="modelValue" @update:modelValue="$emit('update:modelValue', $event)">

            <template v-slot:header>
                <h5 class="modal-title">아이콘 추가</h5>
            </template>

            <template v-slot:default>
                <div v-if="!loaded" style="text-align:center; padding: 40px;">
                    <div class="loader"></div>
                </div>

                <div v-if="loaded">
                    <!-- 탭 -->
                    <div class="external-svg-tabs">
                        <button
                                v-for="tab in tabs" :key="tab.key"
                                class="external-svg-tab-btn"
                                :class="{'external-svg-tab-btn-active': currentTab === tab.key}"
                                @click="selectTab(tab.key)"
                        >{{ tab.label }}</button>
                    </div>

                    <!-- 검색 + 색상 + 크기 -->
                    <div class="external-svg-toolbar">
                        <div class="external-svg-search-wrap">
                            <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    v-model="search"
                                    placeholder="검색어를 입력해 주세요."
                                    @input="onSearch"
                            >
                            <i class="glyphicon glyphicon-search external-svg-search-icon"></i>
                        </div>
                        <div class="external-svg-toolbar-right">
                            <span class="external-svg-toolbar-label">색상</span>
                            <external-picker v-model="iconColor"></external-picker>
                            <span class="external-svg-toolbar-label">크기</span>
                            <input type="number" v-model.number="iconSize" class="form-control form-control-sm external-svg-size-input">
                            <span class="external-svg-toolbar-label">px</span>
                        </div>
                    </div>

                    <!-- 아이콘 그리드 -->
                    <div class="external-svg-grid">
                        <div
                                v-for="icon in pagedIcons" :key="icon.library + ':' + icon.name"
                                class="external-svg-grid-item"
                                :class="{'external-svg-grid-item-active': selectedIcon && selectedIcon.library === icon.library && selectedIcon.name === icon.name}"
                                @click="selectIcon(icon)"
                                :title="icon.name"
                        >
                            <span v-if="icon.library === 'feather'" v-html="renderFeather(icon.name)"></span>
                            <i v-else-if="icon.library === 'bootstrap'" :class="'bi bi-' + icon.name" :style="{color: iconColor, fontSize: iconSize + 'px'}"></i>
                            <i v-else-if="icon.library === 'phosphor'" :class="'ph ph-' + icon.name" :style="{color: iconColor, fontSize: iconSize + 'px'}"></i>
                            <i v-else-if="icon.library === 'simpleline'" :class="'icon-' + icon.name" :style="{color: iconColor, fontSize: iconSize + 'px'}"></i>
                        </div>
                    </div>

                    <!-- 더보기 -->
                    <div v-if="hasMore" class="external-svg-more">
                        <button class="btn btn-default btn-sm" @click="loadMore">더보기 ({{ filteredIcons.length - page * perPage }}개 남음)</button>
                    </div>

                    <!-- 결과 없음 -->
                    <div v-if="filteredIcons.length === 0" class="external-svg-empty">
                        검색 결과가 없습니다.
                    </div>
                </div>
            </template>

            <template v-slot:footer>
                <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-orange" @click="confirm" :disabled="!selectedIcon">선택</button>
            </template>

        </external-bs-modal>
    </div>
</script>

<script>
    JayDream_components.push({name: "<?=$componentName?>", object: {
            template: "#<?=$componentName?>-template",
            props: {
                modelValue: {type: Object, default: () => ({})},
            },
            emits: ['update:modelValue', 'select'],
            data() {
                return {
                    component_name: "<?=$componentName?>",
                    component_idx : "",

                    loaded  : false,
                    allIcons: [],
                    iconMap : {},

                    tabs: [
                        {key: 'all',        label: '전체'},
                        {key: 'feather',    label: 'Feathericons'},
                        {key: 'bootstrap',  label: 'Bootstrap'},
                        {key: 'phosphor',   label: 'Phosphor'},
                        {key: 'simpleline', label: 'Simple Line'},
                    ],

                    currentTab  : 'all',
                    search      : '',
                    selectedIcon: null,
                    iconColor   : '#000000',
                    iconSize    : 24,

                    page   : 1,
                    perPage: 80,

                    injectUrls: [
                        'https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css',
                        'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css',
                        'https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.5.5/css/simple-line-icons.min.css',
                        'https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js',
                    ],
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
            },
            async mounted() {
                this.$nextTick(() => {});
            },
            methods: {
                async loadIcons() {
                    if (this.allIcons.length > 0) return;
                    try {
                        const res  = await fetch('/JayDream/resource/json/svg-icons.json');
                        const data = await res.json();
                        this.iconMap = data;

                        const list = [];
                        ['feather', 'bootstrap', 'phosphor', 'simpleline'].forEach(lib => {
                            if (data[lib]) {
                                data[lib].forEach(name => list.push({library: lib, name}));
                            }
                        });
                        this.allIcons = list;
                        this.loaded   = true;
                    } catch(e) {
                        console.error('svg-icons.json 로드 실패', e);
                        this.loaded = true;
                    }
                },
                selectTab(key) {
                    this.currentTab = key;
                    this.page       = 1;
                },
                onSearch() {
                    this.page = 1;
                },
                loadMore() {
                    this.page++;
                },
                selectIcon(icon) {
                    this.selectedIcon = icon;
                },
                confirm() {
                    if (!this.selectedIcon) return;
                    this.$emit('select', {
                        library: this.selectedIcon.library,
                        name   : this.selectedIcon.name,
                        src    : this.selectedIcon.library + ':' + this.selectedIcon.name,
                        color  : this.iconColor,
                        size   : this.iconSize,
                    });
                    let copy    = Object.assign({}, this.modelValue);
                    copy.status = false;
                    this.$emit('update:modelValue', copy);
                },
                renderFeather(name) {
                    if (typeof feather !== 'undefined' && feather.icons[name]) {
                        return feather.icons[name].toSvg({
                            width : this.iconSize,
                            height: this.iconSize,
                            color : this.iconColor,
                        });
                    }
                    return `<span style="font-size:10px;color:#ccc">${name}</span>`;
                },
            },
            computed: {
                filteredIcons() {
                    let list = this.currentTab === 'all'
                        ? this.allIcons
                        : this.allIcons.filter(i => i.library === this.currentTab);

                    if (this.search.trim()) {
                        const q = this.search.trim().toLowerCase();
                        list = list.filter(i => i.name.includes(q));
                    }
                    return list;
                },
                pagedIcons() {
                    return this.filteredIcons.slice(0, this.page * this.perPage);
                },
                hasMore() {
                    return this.filteredIcons.length > this.page * this.perPage;
                },
            },
            watch: {
                "modelValue.status"(value) {
                    if (value) {
                        this.selectedIcon = null;
                        this.search       = '';
                        this.currentTab   = 'all';
                        this.page         = 1;
                        this.loadIcons();
                    }
                },
            },
        }});
</script>

<style>
    .external-svg-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
    }

    .external-svg-tab-btn {
        border: 1px solid #ddd;
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 13px;
        background: #fff;
        cursor: pointer;
        color: #555;
        transition: all 0.15s;
    }

    .external-svg-tab-btn:hover {
        border-color: #e64d11;
        color: #e64d11;
    }

    .external-svg-tab-btn-active {
        background: #e64d11 !important;
        border-color: #e64d11 !important;
        color: #fff !important;
    }

    .external-svg-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .external-svg-search-wrap {
        position: relative;
        flex: 1;
        min-width: 160px;
    }

    .external-svg-search-wrap input {
        padding-right: 30px;
    }

    .external-svg-search-icon {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 13px;
        pointer-events: none;
    }

    .external-svg-toolbar-right {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    .external-svg-toolbar-label {
        font-size: 13px;
        color: #555;
        white-space: nowrap;
    }

    .external-svg-size-input {
        width: 60px !important;
    }

    .external-svg-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
        gap: 4px;
        max-height: 380px;
        overflow-y: auto;
        border: 1px solid #eee;
        border-radius: 6px;
        padding: 8px;
    }

    .external-svg-grid-item {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.15s, background 0.15s;
    }

    .external-svg-grid-item:hover {
        background: #f5f5f5;
    }

    .external-svg-grid-item-active {
        border-color: #e64d11 !important;
        background: #fff5f3 !important;
    }

    .external-svg-more {
        text-align: center;
        margin-top: 12px;
    }

    .external-svg-empty {
        text-align: center;
        padding: 40px;
        color: #999;
        font-size: 14px;
    }
</style>