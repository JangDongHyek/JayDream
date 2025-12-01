
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
    jd.api = new JayDreamAPI(jd);
    jd.session = new JayDreamSession(jd);

    jd.api.table("member").get(a);
</script>