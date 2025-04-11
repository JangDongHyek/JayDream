class JayDreamLib {
    constructor(jd) {
        this.jd = jd;
    }

    isMobile() {
        return /iPhone|iPad|iPod|Android|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
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

            console.log(objects)

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

            console.log(form);

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
}
