class JayDreamAPI {
    constructor(jd) {
        this.jd = jd;
    }

    table(name, component_name = null) {
        return new JayDreamTableAPI(this.jd, name, component_name);
    }
}

// 🔥 공통 필터 메서드를 가진 베이스 클래스
class JayDreamFilterBase {
    constructor(jd, filter) {
        this.jd = jd;
        this.filter = filter;
    }

    where(...args) {
        const defaults = {
            column: null,
            value: null,
            logical: "AND",
            operator: "=",
            encrypt: false,
            as: null
        };

        const params = this.jd.lib.args(defaults, ...args);
        let { column, value, logical, operator, encrypt, as } = params;

        if (!column) return this;

        // LIKE 자동 처리
        let processedValue = value;
        if (operator.toLowerCase() === "like") {
            if (processedValue && !processedValue.includes("%")) {
                processedValue = `%${processedValue}%`;
            }
        }

        // 🔥 currentBlock이 있을 때 처리
        if (this.currentBlock) {
            let existing = this.currentBlock.where.find(w => w.column === column);

            // value가 빈값이고 기존 조건이 있음 → 삭제
            if (!processedValue && existing) {
                const idx = this.currentBlock.where.indexOf(existing);
                if (idx !== -1) this.currentBlock.where.splice(idx, 1);
                return this;
            }

            // value가 빈값이고 기존도 없음 → 무시
            if (!processedValue && !existing) return this;

            // 기존 조건이 있으므로 업데이트
            if (existing) {
                existing.value = processedValue;
                existing.logical = logical;
                existing.operator = operator;
                existing.encrypt = encrypt;
                return this;
            }

            // 새로운 조건 추가
            this.currentBlock.where.push({
                column,
                value: processedValue,
                logical,
                operator,
                encrypt
            });
            return this;
        }

        // 🔥 일반 where 처리 (기존 로직)
        const searchKey = as || column;
        let existing = this.filter.where.find(w => (w.as || w.column) === searchKey);

        if (!processedValue && existing) {
            const idx = this.filter.where.indexOf(existing);
            if (idx !== -1) this.filter.where.splice(idx, 1);
            return this;
        }

        if (!processedValue && !existing) return this;

        if (existing) {
            existing.column = column;
            existing.value = processedValue;
            existing.logical = logical;
            existing.operator = operator;
            existing.encrypt = encrypt;
            if (as) existing.as = as;
            return this;
        }

        const whereObj = {
            column,
            value: processedValue,
            logical,
            operator,
            encrypt
        };

        if (as) whereObj.as = as;
        this.filter.where.push(whereObj);

        return this;
    }

    field(expression) {
        if (!this.filter.fields) this.filter.fields = [];
        this.filter.fields.push(expression);
        return this;
    }

    groupBy(...columns) {
        if (!this.filter.group_bys) {
            this.filter.group_bys = {
                by: [],
                selects: [],
            };
        }

        // groupBy("payment.idx", "payment.code") 식으로 여러개 가능
        columns.forEach(col => {
            if (!this.filter.group_bys.by.includes(col)) {
                this.filter.group_bys.by.push(col);
            }
        });

        return this;
    }

    groupBySelect(...args) {
        if (!this.filter.group_bys) {
            this.filter.group_bys = { by: [], selects: [] };
        }

        const opts = this.jd.lib.args({
            type: "SUM",   // SUM, COUNT, AVG, MAX, MIN
            column: "",
            as: "",
        }, ...args);

        if (!opts.column || !opts.as) return this;

        let existing = this.filter.group_bys.selects.find(s => s.as === opts.as);
        if (existing) {
            existing.type = opts.type;
            existing.column = opts.column;
        } else {
            this.filter.group_bys.selects.push({
                type: opts.type,
                column: opts.column,
                as: opts.as,
            });
        }

        return this;
    }

    having(...args) {
        if (!this.filter.group_bys) return this;

        if (!this.filter.group_bys.having) {
            this.filter.group_bys.having = [];
        }

        const opts = this.jd.lib.args({
            column: "",
            value: "",
            logical: "AND",
            operator: "=",
        }, ...args);

        if (!opts.column) return this;

        let existing = this.filter.group_bys.having.find(h => h.column === opts.column);
        if (existing) {
            existing.value = opts.value;
            existing.logical = opts.logical;
            existing.operator = opts.operator;
        } else {
            this.filter.group_bys.having.push(opts);
        }

        return this;
    }

    // 🔥 blockStart 추가
    async blockStart(keyword, logical = "AND") {
        if (this.currentBlock) {
            await this.jd.lib.alert('api.js blockStart가 중복되었습니다.');
            return false;
        }

        if (!this.filter.blocks) this.filter.blocks = [];

        let block = this.filter.blocks.find(b => b.keyword === keyword);

        if (!block) {
            block = {
                keyword: keyword,
                logical: logical,
                where: []
            };
            this.filter.blocks.push(block);
        }

        this.currentBlock = block;
        return this;
    }

    // 🔥 blockEnd 추가
    blockEnd() {
        this.currentBlock = null;
        return this;
    }

    join(...args) {
        const opts = this.jd.lib.args({
            table: "",
            base: "",
            foreign: "",
            type: "LEFT",
            select_column: "*",
            as: "",
            on: undefined,
        }, ...args);

        // "* 아닐경우 문자열을 배열로 치환 'nick,age' < ['nick','age']"
        if (opts.select_column !== "*") {
            if (!Array.isArray(opts.select_column)) {
                opts.select_column = opts.select_column.split(",").map(s => s.trim());
            }
        }

        let obj = {
            table: opts.table,
            base: opts.base,
            foreign: opts.foreign,
            type: opts.type,
            select_column: opts.select_column,
            as: opts.as,
        };

        if (opts.on) obj.on = opts.on;

        this.filter.joins.push(obj);
        return this;
    }

    orderBy(column, value = "DESC", priority = 0) {
        this.filter.order_by[priority] = { column, value };
        this.filter.order_by = this.filter.order_by.filter(item => item != null);
        return this;
    }

    between(column, start, end, logical = "and") {
        this.filter.between.push({
            column: column,
            start: start,
            end: end,
            logical: logical,
        });
        return this;
    }

    in(column, values, logical = "AND") {
        // 빈 배열이면 기존 조건 제거
        if (!values || values.length === 0) {
            const index = this.filter.in.findIndex(item => item.column === column);
            if (index > -1) {
                this.filter.in.splice(index, 1);
            }
            return this;
        }

        // 기존에 같은 컬럼의 in 조건이 있는지 확인
        let existing = this.filter.in.find(item => item.column === column);

        if (existing) {
            existing.value = values;
            existing.logical = logical;
        } else {
            this.filter.in.push({
                column: column,
                value: values,
                logical: logical
            });
        }

        return this;
    }
}

// 🔥 TableAPI - 베이스 클래스 상속
class JayDreamTableAPI extends JayDreamFilterBase {
    constructor(jd, tableName, component_name) {
        const filter = {
            table: tableName,
            where: [],
            joins: [],
            between: [],
            order_by: [],
            in: [],
            relations: [],
            blocks: [],
            paging: {
                page: 1,
                limit: 99999,
                count: 0,
                last: 0,
            }
        };

        super(jd, filter);

        this.currentTable = tableName;
        this.component_name = component_name;
    }

    async get(bind, options = {}) {
        options.component_name = this.component_name;

        try {
            if (options.paging) this.filter.paging.limit = options.paging;
            if (options.page) this.filter.paging.page = options.page;
            if (options.file) this.filter.file_db = options.file;

            const res = await this.jd.lib.ajax("get", this.filter, `/JayDream/api.php`, options);
            const data = Array.isArray(res.data) ? res.data : [];

            if (this.filter.paging) {
                this.filter.paging.count = res.count;
                this.filter.paging.last = Math.ceil(this.filter.paging.count / this.filter.paging.limit);
            }

            if (bind) {
                if (Array.isArray(bind)) {
                    bind.splice(0, bind.length, ...data);
                } else if (typeof bind === "object" && bind !== null) {
                    Object.assign(bind, data[0] || {});
                }
            }

            if (options.callback) await options.callback(res);

            return data;
        } catch (e) {
            await this.jd.plugin.alert(e.message);
            return [];
        }
    }

    getFire(bind, options = {}) {
        options.component_name = this.component_name;

        try {
            if (options.paging) this.filter.paging.limit = options.paging;
            if (options.page) this.filter.paging.page = options.page;
            if (options.file) this.filter.file_db = options.file;

            const res = this.jd.lib.ajax("get", this.filter, `/JayDream/api.php`, options);
            const data = Array.isArray(res.data) ? res.data : [];

            if (this.filter.paging) {
                this.filter.paging.count = res.count;
                this.filter.paging.last = Math.ceil(this.filter.paging.count / this.filter.paging.limit);
            }

            if (bind) {
                if (Array.isArray(bind)) {
                    bind.splice(0, bind.length, ...data);
                } else if (typeof bind === "object" && bind !== null) {
                    Object.assign(bind, data[0] || {});
                }
            }

            if (options.callback) options.callback(res);

            return data;
        } catch (e) {
            this.jd.plugin.alert(e.message);
            return [];
        }
    }

    async post(data, options = {}) {
        let method = data.primary ? 'update' : 'insert';
        let url = `/JayDream/api.php`;
        options.component_name = this.component_name;

        try {
            if (this.currentTable && !options.table) options.table = this.currentTable;
            if (!data['$table'] && !options.table) throw new Error("테이블값이 존재하지않습니다.");
            if (data['$table'] && !options.table) options.table = data['$table'];

            if ("confirm" in options) {
                if (!await this.jd.plugin.confirm(options.confirm.message)) {
                    if (options.confirm.callback) {
                        await options.confirm.callback();
                    } else {
                        return false;
                    }
                }
            }

            if (options.url) url = options.url;
            if (options.method) method = options.method;

            let res = await this.jd.lib.ajax(method, data, url, options);

            if (options.return) return res;

            if (options.callback) {
                await options.callback(res);
            } else {
                let message = options.message ? options.message : "완료되었습니다.";
                await this.jd.plugin.alert(message);

                if (options.href) window.location.href = this.jd.url + options.href;
                else window.location.reload();
            }
        } catch (e) {
            await this.jd.plugin.alert(e.message);
        }
    }

    async delete(data, options = {}) {
        options.component_name = this.component_name;
        let message = "정말 삭제하시겠습니까?";
        if (options.message) message = options.message;

        if (!options.return) {
            if (!await this.jd.plugin.confirm(message)) return false;
        }

        try {
            if (this.currentTable && !options.table) options.table = this.currentTable;
            if (!data['$table'] && !options.table) throw new Error("테이블값이 존재하지않습니다.");
            options.table = data['$table'];

            let res = await this.jd.lib.ajax("remove", data, `/JayDream/api.php`, options);

            if (options.return) return res;

            if (options.callback) {
                await options.callback(res);
            } else {
                await this.jd.plugin.alert("완료되었습니다.");
                if (options.href) window.location.href = this.jd.url + options.href;
                else window.location.reload();
            }
        } catch (e) {
            await this.jd.plugin.alert(e.message);
        }
    }

    async whereUpdate(update_column, options = {}) {
        let url = `/JayDream/api.php`;
        options.component_name = this.component_name;

        try {
            if (this.currentTable && !options.table) options.table = this.currentTable;
            if (!options.table) throw new Error("테이블값이 존재하지않습니다.");

            if ("confirm" in options) {
                if (!await this.jd.plugin.confirm(options.confirm.message)) {
                    if (options.confirm.callback) {
                        await options.confirm.callback();
                    } else {
                        return false;
                    }
                }
            }

            if (options.url) url = options.url;

            let res = await this.jd.lib.ajax("where_update", update_column, url, this.filter);

            if (options.return) return res;

            if (options.callback) {
                await options.callback(res);
            } else {
                await this.jd.plugin.alert("완료되었습니다.");

                if (options.href) window.location.href = this.jd.url + options.href;
                else window.location.reload();
            }
        } catch (e) {
            await this.jd.plugin.alert(e.message);
        }
    }

    async whereDelete(filter, options = {}) {
        let url = `/JayDream/api.php`;
        options.component_name = this.component_name;

        try {
            if (this.currentTable && !options.table) options.table = this.currentTable;
            if (!filter.table) throw new Error("테이블값이 존재하지않습니다.");

            if ("confirm" in options) {
                if (!await this.jd.plugin.confirm(options.confirm.message)) {
                    if (options.confirm.callback) {
                        await options.confirm.callback();
                    } else {
                        return false;
                    }
                }
            }

            if (options.url) url = options.url;

            let res = await this.jd.lib.ajax("where_delete", filter, url, options);

            if (options.return) return res;

            if (options.callback) {
                await options.callback(res);
            } else {
                await this.jd.plugin.alert("완료되었습니다.");

                if (options.href) window.location.href = this.jd.url + options.href;
                else window.location.reload();
            }
        } catch (e) {
            await this.jd.plugin.alert(e.message);
        }
    }

    relations(table, as = '') {
        if (!table) return null;

        as = as || table;

        if (!Array.isArray(this.filter.relations)) {
            this.filter.relations = [];
        }

        let existing = this.filter.relations.find(r => r.as === as);

        if (existing) {
            return new JayDreamRelationAPI(this, existing);
        }

        let relation = {
            table: table,
            as: as,
        };

        this.filter.relations.push(relation);

        return new JayDreamRelationAPI(this, relation);
    }
}

// 🔥 RelationAPI - 베이스 클래스 상속
class JayDreamRelationAPI extends JayDreamFilterBase {
    constructor(parentTableAPI, relation) {
        // relation 초기화
        if (!relation.where) relation.where = [];
        if (!relation.joins) relation.joins = [];
        if (!relation.order_by) relation.order_by = [];
        if (!relation.between) relation.between = [];
        if (!relation.relations) relation.relations = [];
        if (!relation.in) relation.in = [];

        super(parentTableAPI.jd, relation);

        this.parentTableAPI = parentTableAPI;
        this.relation = relation;
    }

    // 재귀적 relations
    relations(table, as = '') {
        if (!table) return null;
        as = as || table;

        let existing = this.relation.relations.find(r => r.as === as);

        if (existing) {
            return new JayDreamRelationAPI(this.parentTableAPI, existing);
        }

        let newRelation = {
            table: table,
            as: as,
        };

        this.relation.relations.push(newRelation);

        return new JayDreamRelationAPI(this.parentTableAPI, newRelation);
    }
}