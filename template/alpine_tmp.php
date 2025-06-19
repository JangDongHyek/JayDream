<div x-data="pageComponent"></div>

<? $jd->jsLoad();?>
<script>
    window.pageComponent = function () {
        return {
            $jd : {},

            async init() {
                this.$jd.url = JayDream_url;
                this.$jd.dev = JayDream_dev;
                this.$jd.alert = JayDream_alert;
                this.$jd.api_key = JayDream_api_key;
                this.$jd.api_iv = JayDream_api_iv;
                this.$jd.plugin = new JayDreamPlugin(this.$jd);
                this.$jd.lib = new JayDreamLib(this.$jd);
            },

            async postData() {
                let row = {};

                let options = {
                    table : ""
                }

                try {
                    let method = row.primary ? 'update' : 'insert'
                    let res = await this.$jd.lib.ajax(method,row,"/JayDream/api.php",options);

                    await this.$jd.lib.alert(method === 'update' ? '수정되었습니다.' : '추가되었습니다.');
                    // window.location.reload();
                }catch (e) {
                    await this.$jd.lib.alert(e.message)
                }
            },

            async getData() {
                try {
                    let res = await this.$jd.lib.ajax("get",{
                        table : "",

                        where: [
                            {
                                column: "",             // join 조건시 user.idx
                                value: "",              // LIKE일시 %% 필수 || relations일시  $parent.idx
                                logical: "AND",         // AND,OR,AND NOT
                                operator: "=",          // = ,!= >= <=, LIKE,
                                encrypt : false,        // true시 벨류가 암호화된 값으로 들어감
                            },
                        ],
                    },"/JayDream/api.php");
                }catch (e) {
                    await this.$jd.lib.alert(e.message)
                }
            },

            async deleteData(idx) {
                let row = {primary : idx};

                let options = {
                    table : "",
                }

                if(await tihs.$jd.lib.confirm(`정말 삭제 하시겠습니까?`)) {
                    try {
                        let res = await tihs.$jd.lib.ajax("remove",row,"/JayDream/api.php",options);

                        await tihs.$jd.lib.alert('삭제되었습니다.');
                        window.location.reload();
                    }catch (e) {
                        await tihs.$jd.lib.alert(e.message)
                    }
                }
            },
        }
    }
</script>