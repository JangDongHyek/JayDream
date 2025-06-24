class JayDreamVue {
    formatPrice(el) {
        let raw = el.value.replace(/[^0-9]/g, '').replace(/^0+/, '').slice(0, 13);
        let formatted = raw;

        if (raw) {
            formatted = parseInt(raw, 10).toLocaleString();
        }

        if (el.value !== formatted) {
            el.value = formatted;
            el.dispatchEvent(new Event("input", { bubbles: true }));
        }
    }

    formatPhone(el) {
        let raw = el.value.replace(/[^0-9]/g, '');

        if (raw.length > 11) {
            raw = raw.slice(0, 11);
        }

        let formatted = raw;

        if (raw.length >= 11) {
            formatted = raw.replace(/(\d{3})(\d{4})(\d{4})/, "$1-$2-$3");
        } else if (raw.length >= 7) {
            formatted = raw.replace(/(\d{3})(\d{3,4})/, "$1-$2");
        } else if (raw.length >= 4) {
            formatted = raw.replace(/(\d{3})(\d{1,3})/, "$1-$2");
        }

        if (formatted.length > 13) {
            formatted = formatted.slice(0, 13);
        }

        if (el.value !== formatted) {
            el.value = formatted;
            el.dispatchEvent(new Event("input", { bubbles: true }));
        }
    }

    formatNumber(el) {
        const raw = el.value.replace(/[^0-9]/g, '');
        if (el.value !== raw) {
            el.value = raw;
            el.dispatchEvent(new Event("input", { bubbles: true }));
        }
    }

    commonFile(files,obj,key,permission) {
        if (files.length > 1 && !Array.isArray(obj[key])) {
            obj[key] = [];
        }

        if(Array.isArray(obj[key])) {
            for (let i = 0; i < files.length; i++) {
                var file = files[i];
                if(!file.type) {
                    alert("파일 타입을 읽을수 없습니다.");
                    return false;
                }

                if(permission.length > 0 && !permission.includes(file.type)) {
                    alert("혀용되는 파일 형식이 아닙니다.");
                    return false;
                }

                if(file.type.startsWith('image/')) {
                    //이미지 미리보기 파일 데이터에 추가
                    const reader = new FileReader();
                    reader.onload = (function(f) {
                        return function(e) {
                            f.src = e.target.result;
                            obj[key].push(f); // 비동기로 파일을 읽는 중이라 onload 안에 넣어줘야 파일을 다 읽고 데이터가 완벽하게 들어간다
                        };
                    })(file); // 클로저 사용
                    reader.readAsDataURL(file);
                }else {
                    obj[key] = file;
                }
            }


        }else {
            file = files[0]
            if (file) {
                if(!file.type) {
                    alert("파일 타입을 읽을수 없습니다.");
                    return false;
                }

                if(permission.length > 0 && !permission.includes(file.type)) {
                    alert("혀용되는 파일 형식이 아닙니다.");
                    return false;
                }

                if(file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (function(f) {
                        return function(e) {
                            f.src = e.target.result;
                            obj[key] = (f); // 비동기로 파일을 읽는 중이라 onload 안에 넣어줘야 파일을 다 읽고 데이터가 완벽하게 들어간다
                        };
                    })(file); // 클로저 사용
                    reader.readAsDataURL(file);
                }else {
                    obj[key] = file;
                }

            } else {
                obj[key]  = '';
            }
        }
    }

    dropFile(event,obj,key,permission = []) {
        this.commonFile(event.dataTransfer.files,obj,key,permission);
    }

    // vue에서 파일업로드시 지정된 오브젝트 key에 파일 데이터 반환해주는 함수
    changeFile(event,obj,key,permission = []) {
        this.commonFile(event.target.files,obj,key,permission)
    }
}