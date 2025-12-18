class JayDreamSession {
    constructor(jd) {
        this.jd = jd;
    }

    async get(key) {
        let payload = {};

        if (Array.isArray(key)) {
            // 배열이면 각 키를 value="" 로 초기화
            key.forEach(k => payload[k] = "");
        } else if (typeof key === "string") {
            // 문자열이면 단일 키
            payload[key] = "";
        } else if (key && typeof key === "object") {
            // 객체면 그대로 사용 (예외 지원)
            payload = key;
        } else {
            throw new Error("[JayDream.Session] key 형식이 잘못되었습니다.");
        }

        const res = await this.jd.lib.ajax(
            "session_get",
            payload,
            `/JayDream/${this.jd.api_url}`
        );

        return res.sessions;
    }

    async set(key, value = "") {
        let payload = {};

        if (Array.isArray(key)) {
            // 배열 → value도 배열인지 확인
            if (Array.isArray(value)) {
                if (key.length !== value.length) {
                    throw new Error("[JayDream.Session] key 배열과 value 배열의 길이가 다릅니다.");
                }
                key.forEach((k, i) => payload[k] = value[i]);
            } else {
                // value가 단일 값이면 전부 같은 값으로 세팅
                key.forEach(k => payload[k] = value);
            }
        }
        else if (typeof key === "object" && key !== null) {
            // 객체 → 그대로 사용
            payload = { ...key };
        }
        else if (typeof key === "string") {
            // 문자열 → 단일 key=value
            payload[key] = value;
        }
        else {
            throw new Error("[JayDream.Session] key 형식이 잘못되었습니다. (string | array | object)");
        }

        // 통신
        await this.jd.lib.ajax("session_set", payload, `/JayDream/${this.jd.api_url}`);

        return true; // 성공 시 true 반환
    }

    async all() {
        const res = await this.jd.lib.ajax(
            "session_all",
            {},
            `/JayDream/${this.jd.api_url}`
        );

        return res.sessions;
    }
}