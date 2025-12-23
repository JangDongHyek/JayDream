class JayDreamAPI {
    constructor(jd) {
        this.jd = jd;
    }

    table(name,component_name = null) {
        return new JayDreamTableAPI(this.jd, name,component_name);
    }
}

class JayDreamTableAPI {
    constructor(jd, tableName,component_name) {
        this.jd = jd;
        this.currentTable = tableName;
        this.currentBlock = null;
        this.component_name = component_name;

        // 🔥 완전 독립 필터
        this.filter = {
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
    }

    where_set(column, value, logical = "AND", operator = "=", encrypt = false) {
        // LIKE 자동 처리
        if (operator.toLowerCase() === "like") {
            if (value && !value.includes("%")) {
                value = `%${value}%`;
            }
        }

        let existing = null;
        let target = null;

        if (this.currentBlock) {
            existing = this.currentBlock.where.find(w => w.column === column);
            target = this.currentBlock.where;
        } else {
            existing = this.filter.where.find(w => w.column === column);
            target = this.filter.where;
        }

        // 🔥 CASE 1: value가 빈값이고 기존 조건이 있음 → 삭제
        if (!value && existing) {
            const idx = target.indexOf(existing);
            if (idx !== -1) target.splice(idx, 1);
            return false; // 추가 안 함
        }

        // 🔥 CASE 2: value가 빈값이고 기존도 없음 → 아무것도 하지 않음
        if (!value && !existing) {
            return false;
        }

        // 🔥 CASE 3: 기존 조건이 있으므로 업데이트
        if (existing) {
            existing.value = value;
            existing.logical = logical;
            existing.operator = operator;
            existing.encrypt = encrypt;
            return false;
        }

        // 🔥 CASE 4: 새로운 조건 추가
        return {
            column,
            value,
            logical,
            operator,
            encrypt,
        };
    }

    where(column, value, logical = "AND", operator = "=", encrypt = false) {
        let obj = this.where_set(column, value, logical, operator, encrypt);

        if (!obj) return this;

        // currentBlock이 있으면 block의 where에 추가
        if (this.currentBlock) {
            this.currentBlock.where.push(obj);
        } else {
            // 없으면 기존처럼 filter.where에 추가
            this.filter.where.push(obj);
        }

        return this;
    }

    async blockStart(keyword, logical = "AND") {
        if (this.currentBlock) {
            await this.jd.lib.alert('api.js blockStart가 중복되었습니다.');
            return false;
        }
        // 1. keyword가 같은 block 찾기
        let block = this.filter.blocks.find(b => b.keyword === keyword);

        // 2. 없으면 새로 만들어서 추가
        if (!block) {
            block = {
                keyword: keyword,
                logical: logical,
                where: []
            };
            this.filter.blocks.push(block);
        }

        // 3. 현재 작업 중인 block으로 설정
        this.currentBlock = block;

        return this;
    }

    blockEnd() {
        this.currentBlock = null;
        return this;
    }

    blockWhere(keyword, column, value, logical = "AND", operator = "=", encrypt = false) {
        // 1. keyword가 같은 block 찾기
        let block = this.filter.blocks.find(b => b.keyword === keyword);

        // 2. 없으면 새로 만들어서 추가
        if (!block) {
            block = {
                keyword: keyword,
                logical: "AND",
                where: []
            };

            this.filter.blocks.push(block);
        }

        let where_obj = this.where_set(column, value, logical, operator, encrypt);

        if (!where_obj) return this;

        block.where.push(where_obj);

        // 3. 해당 block 반환
        return this;
    }

    between(column, start, end, logical = "and") {
        this.filter.between.push({
            column: column,     // 컬럼 || 함수
            start: start,       // 시간 || 컬럼
            end: end,           // 시간 || 컬럼
            logical: logical,
        });

        return this;
    }

    join(table, base, options = {}) {
        let obj = {
            table: table,
            base: base,
            foreign: options.foreign || "",
            type: options.type || "LEFT",
            select_column: options.select_column || "*",
            as: options.as || "",
        }

        if (options.on) obj.on = options.on;

        this.filter.joins.push(obj);

        return this;
    }

    orderBy(column, value = "DESC", priority = 0) {
        this.filter.order_by[priority] = { column: column, value: value };
        this.filter.order_by = this.filter.order_by.filter(item => item != null);
        return this;
    }

    async get(bind, options = {}) {
        options.component_name = this.component_name;

        try {
            if (options.paging) this.filter.paging.limit = options.paging;
            if (options.page) this.filter.paging.page = options.page;
            if (options.file) this.filter.file_db = options.file;

            const res = await this.jd.lib.ajax("get", this.filter, `/JayDream/${this.jd.api_url}`, options);
            const data = Array.isArray(res.data) ? res.data : [];

            if (this.filter.paging) {
                this.filter.paging.count = res.count;
                this.filter.paging.last = Math.ceil(this.filter.paging.count / this.filter.paging.limit);
            }

            // ✅ Vue 반응성 대응 (배열 / 객체 자동 갱신)
            if (bind) {
                if (Array.isArray(bind)) {
                    // 배열이면 splice로 갱신
                    bind.splice(0, bind.length, ...data);
                } else if (typeof bind === "object" && bind !== null) {
                    // 객체면 Object.assign으로 병합
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

            const res = this.jd.lib.ajax("get", this.filter, `/JayDream/${this.jd.api_url}`, options);
            const data = Array.isArray(res.data) ? res.data : [];

            if (this.filter.paging) {
                this.filter.paging.count = res.count;
                this.filter.paging.last = Math.ceil(this.filter.paging.count / this.filter.paging.limit);
            }

            // ✅ Vue 반응성 대응 (배열 / 객체 자동 갱신)
            if (bind) {
                if (Array.isArray(bind)) {
                    // 배열이면 splice로 갱신
                    bind.splice(0, bind.length, ...data);
                } else if (typeof bind === "object" && bind !== null) {
                    // 객체면 Object.assign으로 병합
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
        let url = `/JayDream/${this.jd.api_url}`;
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

            let res = await this.jd.lib.ajax("remove", data, `/JayDream/${this.jd.api_url}`, options);

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
        let url = `/JayDream/${this.jd.api_url}`;
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

            let res = await this.jd.lib.ajax("where_update", update_column, url, options);

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
        let url = `/JayDream/${this.jd.api_url}`;
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
}