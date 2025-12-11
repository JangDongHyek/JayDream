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

    commonFile(files, obj, key, permission, callback) {
        if (files.length > 1 && !Array.isArray(obj[key])) {
            obj[key] = [];
        }

        if(Array.isArray(obj[key])) {
            let loadedCount = 0;
            const totalFiles = files.length;

            // 이미지 정보 저장할 배열
            if(!obj[key + '_info']) {
                obj[key + '_info'] = [];
            }

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
                    const reader = new FileReader();
                    reader.onload = (function(f) {
                        return function(e) {
                            const img = new Image();
                            img.onload = function() {
                                f.src = e.target.result;
                                f.width = this.naturalWidth;
                                f.height = this.naturalHeight;
                                obj[key].push(f);

                                // 파일명을 키로 사용 (tmp_name은 JS에서 알 수 없으니 원본 파일명 사용)
                                if(!obj[key + '_info']) {
                                    obj[key + '_info'] = {};
                                }

                                const uniqueKey = btoa(encodeURIComponent(`${f.name}_${f.size}`));
                                obj[uniqueKey] = {
                                    width: this.naturalWidth,
                                    height: this.naturalHeight
                                };

                                loadedCount++;
                                if(loadedCount === totalFiles && callback) {
                                    callback();
                                }
                            };
                            img.src = e.target.result;
                        };
                    })(file);
                    reader.readAsDataURL(file);
                }else {
                    obj[key] = file;
                    loadedCount++;
                    if(loadedCount === totalFiles && callback) {
                        callback();
                    }
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
                            const img = new Image();
                            img.onload = function() {
                                f.src = e.target.result;
                                f.width = this.naturalWidth;
                                f.height = this.naturalHeight;
                                obj[key] = f;

                                // 단일 파일도 객체로 저장
                                const uniqueKey = btoa(encodeURIComponent(`${f.name}_${f.size}`));
                                obj[uniqueKey] = {
                                    width: this.naturalWidth,
                                    height: this.naturalHeight
                                };

                                if(callback) {
                                    callback();
                                }
                            };
                            img.src = e.target.result;
                        };
                    })(file);
                    reader.readAsDataURL(file);
                }else {
                    obj[key] = file;
                    if(callback) {
                        callback();
                    }
                }
            } else {
                obj[key] = '';
                if(callback) {
                    callback();
                }
            }
        }
    }

    dropFile(event, obj, key, permission = [], callback = null) {
        this.commonFile(event.dataTransfer.files, obj, key, permission, callback);
    }

    changeFile(event, obj, key, permission = [], callback = null) {
        this.commonFile(event.target.files, obj, key, permission, callback);
    }
}