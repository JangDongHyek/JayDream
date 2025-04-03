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

    for (const component of JayDream_components) {
        app.component(component.name,component.object)
    }

    let JayDream = {};

    JayDream.url = JayDream_url;
    JayDream.dev = JayDream_dev;
    JayDream.alert = JayDream_alert;

    // Vue 내부에서만 접근 가능하게 설정
    app.config.globalProperties.$jd = JayDream;

    app.mount(`#${app_name}`); // 특정 DOM에 마운트
    JayDream_vue.push({ app_name, app }); // 배열에 앱 인스턴스 저장
}