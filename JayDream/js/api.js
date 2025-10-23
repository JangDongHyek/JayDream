class JayDreamAPI {

    constructor(jd) {
        this.jd = jd;
        this.filters = {}; // 테이블별 filter 저장소
        this.component_name = "";
        this.currentTable = null;

        return new Proxy(this, {
            get(target, prop) {
                // 실제 존재하는 속성 (예: get(), post() 등)이면 그대로 반환
                if (prop in target) return target[prop];

                // 존재하지 않는 속성을 접근하면 → table(prop) 자동 실행
                return target.table(prop);
            },
        });
    }

    table(name) {
        // 테이블별 filter 없으면 새로 생성
        if (!this.filters[name]) {
            this.filters[name] = {
                table: name,
                where: [],
                joins: [],
                between: [],
                order_by: [],
                in: [],
                relations: [],
                blocks: [],

                paging : {
                    page: 1,
                    limit: 99999,
                    count: 0,
                    last : 0,
                }
            };
        }

        this.currentTable = name;
        return this;
    }

    get filter() {
        // 현재 테이블 기준으로 filter 반환
        if (!this.currentTable) throw new Error("table()이 먼저 호출되어야 합니다.");
        return this.filters[this.currentTable];
    }

    set filter(newFilter) {
        // 현재 테이블 기준으로 filter 교체
        if (!this.currentTable)
            throw new Error("table()이 먼저 호출되어야 합니다.");

        // 기본 형태가 유지되도록 최소 구조 보장
        this.filters[this.currentTable] = newFilter
    }

    where(column,value,logical = "AND", operator = "=", encrypt = false) {
        // LIKE 처리
        if (operator.toLowerCase() === "like") {
            if (value &&!value.includes("%")) {
                value = `%${value}%`;
            }
        }

        this.filter.where.push(
            {
                column: column,             // join 조건시 user.idx
                value: value,               // LIKE일시 %% 필수 || relations일시  $parent.idx , 공백일경우 __null__ , null 값인경우 null
                logical: logical,           // AND,OR,AND NOT
                operator: operator,         // = ,!= >= <=, LIKE,
                encrypt: encrypt,           // true시 벨류가 암호화된 값으로 들어감
            }
        )

        return this
    }

    between(column,start,end,logical = "and") {
        this.filter.between.push({
            column: column,     // 컬럼 || 함수
            start: start,       // 시간 || 컬럼
            end: end,           // 시간 || 컬럼
            logical: logical,
        });

        console.log(this.filter)

        return this
    }

    join(table,base,foreign,type = "LEFT",select_column = "*",as = "",on = null) {
        let obj = {
            table: table,
            base: base,                     // filter 테이블의 연결 key
            foreign: foreign,               // join 테이블의 연결 key
            type: type,                     // INNER, LEFT, RIGHT
            select_column: select_column,   // 조회할 컬럼 $table__column 식으로 as되서 들어간다 || "*"
            as :as,                         // 값이 있을경우 $as__column 해당방식으로 들어감
        }

        if(on) obj.on = on;

        this.filter.joins.push(obj)

        return this
    }

    async get(options = {}) {
        // ✅ 매개변수가 배열이거나 null이면 자동 변환
        if (Array.isArray(options) || options === null) {
            options = { bind: options };
        }

        options.component_name = this.component_name;

        try {
            if (options.paging) this.filter.paging.limit = options.paging;
            if (options.page) this.filter.paging.page = options.page;
            if (options.file) this.filter.file_db = options.file;

            const res = await this.jd.lib.ajax("get", this.filter, "/JayDream/api.php", options);
            const data = Array.isArray(res.data) ? res.data : [];

            if (this.filter.paging) {
                this.filter.paging.count = res.count;
                this.filter.paging.last = Math.ceil(this.filter.paging.count / this.filter.paging.limit)
            }

            // ✅ Vue 반응성 대응 (배열 / 객체 자동 갱신)
            if (options.bind) {
                if (Array.isArray(options.bind)) {
                    // 배열이면 splice로 갱신
                    options.bind.splice(0, options.bind.length, ...data);
                } else if (typeof options.bind === "object" && options.bind !== null) {
                    // 객체면 Object.assign으로 병합
                    Object.assign(options.bind, data[0] || {});
                }
            }

            if (options.callback) await options.callback(res);

            this.filter.where = [];
            this.filter.between = [];
            this.filter.in = [];
            this.filter.joins = [];

            return data;
        } catch (e) {
            await this.jd.plugin.alert(e.message);
            return [];
        }
    }

    async post(data,options = {}) {
        let method = data.primary ? 'update' : 'insert';
        let url = "/JayDream/api.php";
        options.component_name = this.component_name;
        try {
            if(!data['$table'] && !options.table) throw new Error("테이블값이 존재하지않습니다.");
            if(data['$table'] && !options.table) options.table = data['$table'];


            if("confirm" in options) {
                if(!await this.jd.plugin.confirm(options.confirm.message)) {
                    if(options.confirm.callback) {
                        await options.confirm.callback()
                    }else {
                        return false;
                    }
                }
            }

            if(options.url) url = options.url;
            if(options.method) method = options.method;

            let res = await this.jd.lib.ajax(method, data, url,options);

            if(options.return) return res

            if(options.callback) {
                await options.callback(res)
            }else {
                let message = options.message ? options.message : "완료되었습니다.";
                await this.jd.plugin.alert(message);

                if(options.href) window.location.href = JayDream.url + options.href;
                else window.location.reload();
            }
        }catch (e) {
            await this.jd.plugin.alert(e.message)
        }
    }

    async delete(data,options = {}) {
        options.component_name = this.component_name;
        let message = "정말 삭제하시겠습니까?";
        if(options.message) message = options.message;

        if(!options.return) {
            if(! await this.jd.plugin.confirm(message)) return false;
        }

        try {
            if(!data['$table'] && !options.table) throw new Error("테이블값이 존재하지않습니다.");
            options.table = data['$table'];
            let res = await this.jd.lib.ajax("remove",data,"/JayDream/api.php",options);

            if(options.return) return res

            if(options.callback) {
                await options.callback(res)
            }else {
                await this.jd.plugin.alert("완료되었습니다.");
                if(options.href) window.location.href = JayDream.url + options.href;
                else window.location.reload();
            }
        }catch (e) {
            await this.jd.plugin.alert(e.message)
        }
    }

    async whereUpdate(update_column,options = {}) {
        let url = "/JayDream/api.php";
        options.component_name = this.component_name;
        try {
            if(!options.table) throw new Error("테이블값이 존재하지않습니다.");

            if("confirm" in options) {
                if(!await this.jd.plugin.confirm(options.confirm.message)) {
                    if(options.confirm.callback) {
                        await options.confirm.callback()
                    }else {
                        return false;
                    }
                }
            }

            if(options.url) url = options.url;

            let res = await this.jd.lib.ajax("where_update", update_column, url,options);

            if(options.return) return res

            if(options.callback) {
                await options.callback(res)
            }else {
                await this.jd.plugin.alert("완료되었습니다.");

                if(options.href) window.location.href = JayDream.url + options.href;
                else window.location.reload();
            }
        }catch (e) {
            await this.jd.plugin.alert(e.message)
        }
    }

    async whereDelete(filter,options = {}) {
        let url = "/JayDream/api.php";
        options.component_name = this.component_name;
        try {
            if(!filter.table) throw new Error("테이블값이 존재하지않습니다.");

            if("confirm" in options) {
                if(!await this.jd.plugin.confirm(options.confirm.message)) {
                    if(options.confirm.callback) {
                        await options.confirm.callback()
                    }else {
                        return false;
                    }
                }
            }

            if(options.url) url = options.url;

            let res = await this.jd.lib.ajax("where_delete", filter, url,options);

            if(options.return) return res

            if(options.callback) {
                await options.callback(res)
            }else {
                await this.jd.plugin.alert("완료되었습니다.");

                if(options.href) window.location.href = JayDream.url + options.href;
                else window.location.reload();
            }
        }catch (e) {
            await this.jd.plugin.alert(e.message)
        }
    }
}