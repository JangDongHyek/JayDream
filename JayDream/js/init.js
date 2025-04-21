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

    JayDream.app = app_name;
    JayDream.url = JayDream_url;
    JayDream.dev = JayDream_dev;
    JayDream.alert = JayDream_alert;
    JayDream.plugin = new JayDreamPlugin(JayDream);
    JayDream.lib = new JayDreamLib(JayDream);
    JayDream.vue = new JayDreamVue();

    // Vue 내부에서만 접근 가능하게 설정
    app.config.globalProperties.$jd = JayDream;

    app.config.globalProperties.$postData = async function(data,options = {}) {
        let method = data.primary ? 'update' : 'insert';
        let url = "/JayDream/api.php";
        options.component_name = this.component_name;
        try {
            if(!options.table) throw new Error("테이블값이 존재하지않습니다.");

            if("confirm" in options) {
                if(!await this.$jd.plugin.confirm(options.confirm.message)) {
                    if(options.confirm.callback) {
                        await options.confirm.callback()
                    }else {
                        return false;
                    }
                }
            }

            if(options.url) url = options.url;
            if(options.method) method = options.method;

            let res = await this.$jd.lib.ajax(method, data, url,options);

            if(options.return) return res

            if(options.callback) {
                await options.callback(res)
            }else {
                await this.$jd.plugin.alert("완료되었습니다.");

                if(options.href) window.location.href = options.href;
                else window.location.reload();
            }
        }catch (e) {
            await this.$jd.plugin.alert(e.message)
        }
    }

    app.config.globalProperties.$getsData = async function(filter,arrays,options = {}) {
        options.component_name = this.component_name;
        try {
            if(!filter.table) throw new Error("테이블값이 존재하지않습니다.");

            let res = await this.$jd.lib.ajax("get", filter, "/JayDream/api.php",options);

            if(options.callback) {
                await options.callback(res)
            }else {
                filter.count = res.count;
                arrays.splice(0, arrays.length, ...res.data); // vue가 인식을 못할수도 있으므로 splice후 배열 복제
            }
        } catch (e) {
            await this.$jd.plugin.alert(e.message)
        }
    }

    app.config.globalProperties.$getData = async function(filter,options = {}) {
        options.component_name = this.component_name;
        try {
            if(!filter.table) throw new Error("테이블값이 존재하지않습니다.");

            let res = await this.$jd.lib.ajax("get", filter, "/JayDream/api.php",options);

            if(options.callback) {
                await options.callback(res)
            }else {
                return res.data[0];
            }
        } catch (e) {
            await this.$jd.plugin.alert(e.message)
        }
    }

    app.config.globalProperties.$deleteData = async function(data,options = {}) {
        options.component_name = this.component_name;
        let message = "정말 삭제하시겠습니까?";
        if(options.message) message = options.message;

        if(!options.return) {
            if(! await this.$jd.plugin.confirm(message)) return false;
        }

        try {
            if(!options.table) throw new Error("테이블값이 존재하지않습니다.");
            let res = await this.$jd.lib.ajax("remove",data,"/JayDream/api.php",options);

            if(options.return) return res

            if(options.callback) {
                await options.callback(res)
            }else {
                await this.$jd.plugin.alert("완료되었습니다.");
                if(options.href) window.location.href = options.href;
                else window.location.reload();
            }
        }catch (e) {
            await this.$jd.plugin.alert(e.message)
        }
    }

    app.config.globalProperties.$whereUpdateData = async function(update_column,options = {}) {
        let url = "/JayDream/api.php";
        options.component_name = this.component_name;
        try {
            if(!options.table) throw new Error("테이블값이 존재하지않습니다.");

            if("confirm" in options) {
                if(!await this.$jd.plugin.confirm(options.confirm.message)) {
                    if(options.confirm.callback) {
                        await options.confirm.callback()
                    }else {
                        return false;
                    }
                }
            }

            if(options.url) url = options.url;

            let res = await this.$jd.lib.ajax("where_update", update_column, url,options);

            if(options.return) return res

            if(options.callback) {
                await options.callback(res)
            }else {
                await this.$jd.plugin.alert("완료되었습니다.");

                if(options.href) window.location.href = options.href;
                else window.location.reload();
            }
        }catch (e) {
            await this.$jd.plugin.alert(e.message)
        }
    }


    app.mount(`#${app_name}`); // 특정 DOM에 마운트
    JayDream_vue.push({ app_name, app }); // 배열에 앱 인스턴스 저장
}