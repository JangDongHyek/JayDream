class JayDreamLib {
    constructor(jd) {
        this.jd = jd;
    }

    isMobile() {
        return /iPhone|iPad|iPod|Android|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    async alert(message, options = {}) {
        await this.jd.plugin.alert(message, options);
    }

    async confirm(message, options = {}) {
        return await this.jd.plugin.confirm(message, options);
    }

    generateUniqueId() {
        const timestamp = Date.now().toString(); // 현재 시간을 밀리초 단위로 문자열로 변환 * 13자
        const randomPart = Math.floor(Math.random() * 100).toString(); // 2자리 랜덤 숫자 생성
        return timestamp + randomPart; // 15자 (동일한 밀리세컨드안에 주문이 들어갈경우 중복될 확률 1퍼)
    }

    processObject(objs,obj,name) {
        objs = this.copyObject(objs);
        obj = this.copyObject(obj);

        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                if(key[0] == "$") delete obj[key]; //첫글자가 $일경우 조인데이터이기때문에 삭제
                const value = obj[key];
                if (value instanceof File) {
                    objs[key] = value;
                    //delete obj[key];
                }else if(Array.isArray(value)) {
                    const filteredArray = value.filter(item => !(item instanceof File));
                    if (filteredArray.length !== value.length) {
                        objs[key] = value; // File이 포함된 원본 배열 유지
                    }
                    obj[key] = filteredArray; // File 제거된 배열로 obj 업데이트
                }else if (typeof value === "boolean") {
                    // 불린 값을 문자열로 변환
                    obj[key] = value ? "true" : "false";
                }else if (typeof value === "object" && value !== null && Object.keys(value).length === 0) {
                    obj[key] = ""; // 빈 객체 {}를 빈 문자열로 변환
                }
            }
        }

        objs[name] = JSON.stringify(obj);
        return objs;
    }

    copyObject(obj) {
        // 파일 객체는 복사하지 않고 그대로 반환
        if (obj instanceof File) {
            return obj;
        }

        // 배열일 경우
        if (Array.isArray(obj)) {
            return obj.map(item => this.copyObject(item));
        }

        // 객체일 경우
        if (obj !== null && typeof obj === 'object') {
            const copy = {};
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    copy[key] = this.copyObject(obj[key]);
                }
            }
            return copy;
        }

        // 원시 타입일 경우 (숫자, 문자열, 불리언 등)
        return obj;
    }

    ajax(method,obj,url,options = {}) {
        if(!obj) new Error("obj 가 존재하지않습니다.");

        return new Promise((resolve, reject) => {
            var object = this.copyObject(obj);

            if("required" in options) {
                for (let i = 0; i < options.required.length; i++) {
                    let req = options.required[i];
                    if(req.name == "") continue;

                    if(typeof object[req.name] === "string") {
                        if(object[req.name].trim() == "") {
                            reject(new Error(req.message));
                            return false;
                        }

                        if (req.min && object[req.name].length < req.min.length) {
                            reject(new Error(`${req.min.message}`));
                            return false;
                        }
                        if (req.max && object[req.name].length > req.max.length) {
                            reject(new Error(`${req.max.message}`));
                            return false;
                        }
                    }

                    if(typeof object[req.name] === "number") {

                    }

                    if (typeof object[req.name] === "boolean") {
                        if(!object[req.name]) {
                            reject(new Error(req.message));
                            return false;
                        }
                    }

                    if(Array.isArray(object[req.name])) {
                        if (req.min && object[req.name].length < req.min.length) {
                            reject(new Error(`${req.min.message}`));
                            return false;
                        }
                        if (req.max && object[req.name].length > req.max.length) {
                            reject(new Error(`${req.max.message}`));
                            return false;
                        }
                    }
                }
            }

            let objects = {_method : method};
            objects = this.processObject(objects,object,'obj');
            objects = this.processObject(objects,options,'options');

            //form 으로 데이터가공
            let form = new FormData();
            for (let i in objects) {
                let data = objects[i];
                if(Array.isArray(data)) {
                    data.forEach(file => {
                        form.append(i+"[]", file);

                    })
                }else {
                    form.append(i, objects[i]);

                }
            }

            // 통신부분
            var xhr = new XMLHttpRequest();
            xhr.open('POST', this.jd.url + url, true);
            xhr.withCredentials = true; // ✅ 세션 쿠키 유지
            xhr.responseType = "download" in options ? 'blob' : "json";

            let res = null
            let _this = this;

            xhr.onload = function () {
                res = xhr.response;
                if (xhr.status === 200) {
                    if(!_this.jd.dev) res = JSON.parse(_this.decryptAES(res));
                    if("download" in options && options.download) {
                        const contentType = xhr.getResponseHeader('content-type');

                        if (contentType && contentType.indexOf('application/json') !== -1) {
                            // It's JSON error, not a file
                            const reader = new FileReader();
                            reader.onload = function() {
                                try {
                                    const jsonResponse = JSON.parse(reader.result);
                                    if (!jsonResponse.success) {
                                        throw new Error(jsonResponse.message);
                                    }
                                    resolve(jsonResponse);
                                } catch (error) {
                                    reject(error); // This will propagate to apiDownload's catch
                                }
                            };
                            reader.onerror = function() {
                                reject(new Error("xhr 파일 변환 실패"));
                            };
                            reader.readAsText(xhr.response);
                            return; // Stop further processing
                        }

                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(res);
                        link.download = options.download;  // 다운로드할 파일 이름 설정
                        link.click();

                        // 메모리 해제를 위해 URL 객체를 폐기
                        window.URL.revokeObjectURL(link.href);
                    }else {
                        if (!res.success) {
                            let message = res.message + "\n";

                            if(_this.jd.dev) {
                                if(res.file_0) {
                                    message += `${res.file_0} : ${res.line_0} Line\n`;
                                }
                                if(res.file_1) {
                                    message += `${res.file_1} : ${res.line_1} Line\n`;
                                }
                                if(res.file_2) {
                                    message += `${res.file_2} : ${res.line_2} Line\n`;
                                }
                            }
                            reject(new Error(message));
                        }
                    }
                    _this.log(res,options.component_name,method);
                    resolve(res);

                } else {
                    reject(new Error("xhr Status 200 아님"));
                    console.log(xhr.statusText);
                }
            };

            xhr.onerror = function () {
                reject(new Error("xhr on error 발생"));
            };

            xhr.send(form);

        });
    }

    log(obj,name,method,background = "#35495e",color = "white") {
        if(!this.jd.dev) return false;

        console.group(
            `%c${name} %c(${method})`,
            `background: ${background}; color: ${color}; font-weight: bold; font-size: 12px; padding: 5px; border-radius: 1px; margin-left : 10px;`,
            'color: gray; font-size: 12px; margin-left: 5px;'
        );
        console.log(obj);
        console.groupEnd();
    }

    href(url,obj = null) {
        // 앞뒤 슬래시 정리
        url = url.trim();
        const base = this.jd.url.replace(/\/+$/, ''); // 끝 슬래시 제거
        const path = url.replace(/^\/+/, '');         // 앞 슬래시 제거
        const fullUrl = `${base}/${path}`;
        window.location.href = this.normalizeUrl(fullUrl,obj);
    }

    decryptAES(cipherText) {
        let decrypted = CryptoJS.AES.decrypt(cipherText, this.jd.api_key, {
            iv: this.jd.api_iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        });
        return decrypted.toString(CryptoJS.enc.Utf8);
    }

    normalizeUrl(url, obj = null) {
        try {
            // url이 빈 값이면 현재 페이지 URL로 설정
            if (!url || url.trim() === "") {
                url = window.location.href;
            }

            // 절대 URL이 아니면 현재 origin 기준으로 생성
            const urlObj = url.startsWith("http")
                ? new URL(url)
                : new URL(url, window.location.origin);

            // path 정규화
            urlObj.pathname = urlObj.pathname.replace(/\/{2,}/g, '/');

            // obj 있으면 GET 파라미터 추가
            if (obj && typeof obj === "object") {
                for (const [key, value] of Object.entries(obj)) {
                    urlObj.searchParams.set(key, value);
                }
            }

            return urlObj.toString();
        } catch (e) {
            console.error("normalizeUrl error:", e);
            return url;
        }
    }

    download(data) {
        if(!data.src && !data['$jd_file__src']) {
            alert("다운로드의 참조 데이터가 잘못됐습니다.");
            return false;
        }
        let src = data.src ? data.src : data['$jd_file__src'];
        let name = data.name ? data.name : data['$jd_file__name'];
        // 동적으로 a 태그 생성
        const link = document.createElement('a');
        link.href = this.jd.url + src;
        link.download = name; // 다운로드할 파일 이름 설정
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click(); // 클릭 이벤트로 다운로드 트리거
        document.body.removeChild(link); // DOM에서 제거
    }

    checkEmail(email) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return !pattern.test(email);
    }

    checkUserId(userId) {
        const pattern = /^[a-zA-Z0-9]{4,20}$/;
        return !pattern.test(userId);
    }

    getParam(key) {
        const params = new URLSearchParams(window.location.search);
        const allParams = Object.fromEntries(params.entries());

        // key가 없으면 전체 반환
        if (!key) {
            return allParams;
        }

        // key가 있으면 해당 값 반환 (없으면 null)
        return allParams[key] ?? null;
    }
}
