
<? $jd->jsLoad();?>

<script>
    let jd = {};
    jd.url = JayDream_url;
    jd.dev = JayDream_dev;
    jd.alert = JayDream_alert;
    jd.api_key = JayDream_api_key;
    jd.api_iv = JayDream_api_iv;
    jd.plugin = new JayDreamPlugin(jd);
    jd.lib = new JayDreamLib(jd);

    async function getData() {
        try {
            let res = await jd.lib.ajax("get",{
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
            await jd.lib.alert(e.message)
        }

    }

    async function postData() {
        let row = {
            subject : document.getElementById("subject").value,
            upfiles : document.getElementById("fileInput_0").files[0] || null,
        };

        let options = {
            table : ""
        }

        try {
            let method = row.primary ? 'update' : 'insert'
            let res = await jd.lib.ajax(method,row,"/JayDream/api.php",options);

            await jd.lib.alert('수정되었습니다.');
            // window.location.reload();
        }catch (e) {
            await jd.lib.alert(e.message)
        }
    }

    async function deleteData(idx) {
        let row = {primary : idx};

        let options = {
            table : "",
        }

        if(await jd.lib.confirm(`정말 삭제 하시겠습니까?`)) {
            try {
                let res = await jd.lib.ajax("delete",row,"/JayDream/api.php",options);

                await jd.lib.alert('삭제되었습니다.');
                window.location.reload();
            }catch (e) {
                await jd.lib.alert(e.message)
            }
        }
    }
</script>