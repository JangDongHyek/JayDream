function vueLoad(app_name) {
    if (JayDream_vue.some(item => item.app_name == app_name)) {
        alert("중복되는 앱이 있습니다.")
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

    let JayDream = {};

    JayDream.app = app_name;
    JayDream.url = JayDream_url;
    JayDream.dev = JayDream_dev;
    JayDream.alert = JayDream_alert;
    JayDream.api_key = JayDream_api_key;
    JayDream.api_iv = JayDream_api_iv;
    JayDream.csrf_name = JayDream_csrf_name;
    JayDream.csrf_value = JayDream_csrf_value;
    JayDream.plugin = new JayDreamPlugin(JayDream);
    JayDream.lib = new JayDreamLib(JayDream);
    JayDream.api = new JayDreamAPI(JayDream);
    JayDream.session = new JayDreamSession(JayDream);
    JayDream.vue = new JayDreamVue();
    JayDream.route = new JayDreamRoute();

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

    // Vue 내부에서만 접근 가능하게 설정
    app.config.globalProperties.$jd = JayDream;
    app.config.globalProperties.lib = JayDream.lib;
    app.config.globalProperties.route = JayDream.route;
    app.config.globalProperties.api = JayDream.api;
    app.config.globalProperties.vue = JayDream.vue;
    app.config.globalProperties.plugin = JayDream.plugin;
    app.config.globalProperties.session = JayDream.session;


    // JayDream 예약어 목록
    const reservedKeys = ['lib', 'route', 'api','vue','plugin','session'];

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


    app.mixin(protectMixin);
    app.mount(`#${app_name}`); // 특정 DOM에 마운트
    JayDream_vue.push({ app_name, app }); // 배열에 앱 인스턴스 저장
}