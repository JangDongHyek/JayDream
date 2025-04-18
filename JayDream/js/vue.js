class JayDreamVue {
    onNumber(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, ""); // 숫자 이외 제거
        value.value = e.target.value;
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
                            f.preview = e.target.result;
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
                            f.preview = e.target.result;
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