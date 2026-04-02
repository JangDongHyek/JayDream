function vueLoad(app_name) {
    if (JayDream_vue.some(item => item.app_name == app_name)) {
        // alert("중복되는 앱이 있습니다.")
        return false;
    }

    const app = Vue.createApp({
        data() {
            return JayDream_data;
        },
        methods: JayDream_methods,
        computed: JayDream_computed,
        watch: JayDream_watch,
        components: {},
        created() {

        },
        mounted() {

        }
    });

    // drag 일때 컴포넌트삽입
    if (window.vuedraggable) {
        app.component("draggable", window.vuedraggable);
    }

    for (const component of JayDream_components) {
        app.component(component.name,component.object)
    }


    //디렉티브
    app.directive('price', {
        mounted(el) {
            el.addEventListener('input', () => {
                JayDream.vue.formatPrice(el);
            });

            // 초기값이 있을 경우에도 포맷 적용
            JayDream.vue.formatPrice(el);
        },
        updated(el) {
            // 값이 외부에서 바뀐 경우에도 포맷 재적용
            JayDream.vue.formatPrice(el);
        }
    });
    app.directive('phone', {
        mounted(el) {
            el.addEventListener('input', () => {
                JayDream.vue.formatPhone(el);
            });
            JayDream.vue.formatPhone(el); // 초기값 대응
        },
        updated(el) {
            JayDream.vue.formatPhone(el);
        }
    });
    app.directive('number', {
        mounted(el) {
            el.addEventListener('input', () => {
                JayDream.vue.formatNumber(el);
            });
            JayDream.vue.formatNumber(el); // 초기값 대응
        },
        updated(el) {
            JayDream.vue.formatNumber(el);
        }
    });

    app.directive('where', {
        mounted(el, binding, vnode) {  // ✅ bind → mounted
            const { table, column, logical = 'AND', operator = '=', encrypt = false } = binding.value;

            const eventType = el.tagName === 'SELECT' ? 'change' : 'keyup';

            el.addEventListener(eventType, (e) => {
                table.where(column, e.target.value, logical, operator, encrypt);
            });
        }
    });

    app.directive('enter', {
        mounted(el, binding, vnode) {
            const { table, rows } = binding.value;

            el.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    table.get(rows, { page: 1 });
                }
            });
        }
    });

    // Vue 내부에서만 접근 가능하게 설정
    app.config.globalProperties.$app_name = app_name;
    app.config.globalProperties.$jd = JayDream;
    app.config.globalProperties.lib = JayDream.lib;
    app.config.globalProperties.route = JayDream.route;
    app.config.globalProperties.api = JayDream.api;
    app.config.globalProperties.vue = JayDream.vue;
    app.config.globalProperties.plugin = JayDream.plugin;
    app.config.globalProperties.session = JayDream.session;
    app.config.globalProperties.prototype = JayDream.prototype;
    app.config.globalProperties.protocol = window.location.protocol.replace(':', '');

    app.config.globalProperties.$openModal = async function (modal,options = {}) {
        if (!modal) {
            await this.lib.alert("modal 매개 변수가 없습니다.");
        }

        Object.assign(modal, options);
        modal.status = true;
    };

    app.config.globalProperties.$closeModal = async function (modal) {
        if (!modal) {
            await this.lib.alert("modal 매개 변수가 없습니다.");
        }
        modal.status = false;
    };


    // JayDream 예약어 목록
    const reservedKeys = ['lib', 'route', 'api','vue','plugin','session',"protocol","prototype"];

    // 예약어 변수 등록시 에러
    const protectMixin = {
        beforeCreate() {
            const name = this.$options.name || '(Anonymous Component)';

            // data 속성 검사
            if (typeof this.$options.data === 'function') {
                const data = this.$options.data.call(this);
                for (const key of Object.keys(data)) {
                    if (reservedKeys.includes(key)) {
                        JayDream.lib.alert(`[JayDream] 컴포넌트 "${name}"의 data()에서 "${key}"는 예약된 이름입니다.`)
                        throw new Error(`[JayDream] 컴포넌트 "${name}"의 data()에서 "${key}"는 예약된 이름입니다.`);
                    }
                }
            }

            // methods, computed 등도 체크
            const sections = ['methods', 'computed', 'props'];
            for (const section of sections) {
                const obj = this.$options[section];
                if (!obj) continue;
                for (const key of Object.keys(obj)) {
                    if (reservedKeys.includes(key)) {
                        JayDream.lib.alert(`[JayDream] "${name}"의 ${section}에 "${key}"는 사용할 수 없습니다.`)
                        throw new Error(`[JayDream] "${name}"의 ${section}에 "${key}"는 사용할 수 없습니다.`);
                    }
                }
            }
        }
    };

    const apiContextMixin = {
        beforeCreate() {
            // JayDream이 없으면 스킵
            if (!this.$jd || !this.$jd.api) return;
            // 컴포넌트 이름 결정 (우선순위 중요)
            const componentName =
                this.$app_name ||
                this.$options.name ||
                null;

            // 🔥 핵심: this.api 를 컴포넌트 전용 래퍼로 덮어씀
            this.api = {
                table: (name) => {
                    return this.$jd.api.table(name, componentName);
                }
            };
        }
    };

    if (!window.JAYDREAM_VUE_GLOBAL[app_name]) {
        window.JAYDREAM_VUE_GLOBAL[app_name] = Vue.reactive({
            mounted: true,
        });
    }


    app.mixin(protectMixin);
    app.mixin(apiContextMixin);

    const cdnMixin = {
        mounted() {
            if (!this.injectUrls || !this.injectUrls.length) return;
            this.injectUrls.forEach(url => {
                const ext = url.split('?')[0].split('.').pop().toLowerCase();
                if (ext === 'css') {
                    if (document.querySelector(`link[href="${url}"]`)) return;
                    const el = document.createElement('link');
                    el.rel  = 'stylesheet';
                    el.href = url;
                    document.head.appendChild(el);
                } else if (ext === 'js') {
                    if (document.querySelector(`script[src="${url}"]`)) return;
                    const el = document.createElement('script');
                    el.src   = url;
                    document.head.appendChild(el);
                }
            });
        }
    };
    app.mixin(cdnMixin);

    app.mount(`#${app_name}`); // 특정 DOM에 마운트
    JayDream_vue.push({ app_name, app }); // 배열에 앱 인스턴스 저장
}