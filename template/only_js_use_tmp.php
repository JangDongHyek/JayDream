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


    async function putData() {
        let row = {};

        let options = {
            table : ""
        }

        try {
            let res = await jd.lib.ajax("update",row,"/JayDream/api.php",options);

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