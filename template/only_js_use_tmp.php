<? $jd->jsLoad();?>

<script>
    let jd = {};

    jd.url = JayDream_url;
    jd.dev = JayDream_dev;
    jd.alert = JayDream_alert;
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
</script>