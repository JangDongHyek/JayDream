class JayDreamVue {
    constructor(jd) {
        this.jd = jd;
    }
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

    async commonFile(files, obj, key, options = {}) {
        const { permission = [], callback = null, resize = true } = options;

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
                    let after_file = file;
                    if(resize) {
                        after_file = await this.resizeWithPica(file);
                        this.jd.lib.console(`업로드파일 리사이징 : `,after_file)
                    }

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
                                    callback(obj[uniqueKey]);
                                }
                            };
                            img.src = e.target.result;
                        };
                    })(after_file);
                    reader.readAsDataURL(after_file);
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
                    let after_file = file;
                    if(resize) {
                        after_file = await this.resizeWithPica(file);
                        this.jd.lib.console(`업로드파일 리사이징 : `,after_file)
                    }

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
                                    callback(obj[uniqueKey]);
                                }
                            };
                            img.src = e.target.result;
                        };
                    })(after_file);
                    reader.readAsDataURL(after_file);
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

    async dropFile(event, obj, key, options = {}) {
        this.jd.lib.console(`업로드 파일 : `,event.dataTransfer.files)
        await this.commonFile(event.dataTransfer.files, obj, key, options);
    }

    async changeFile(event, obj, key, options = {}) {
        this.jd.lib.console(`업로드 파일 : `,event.target.files)
        await this.commonFile(event.target.files, obj, key, options);
    }

    async resizeWithPica(originalFile, options = {}) {
        const { scale = 0.7, quality = 0.8, maxFileSize = 50 * 1024 * 1024 } = options;

        // 파일 크기 체크
        if (originalFile.size > maxFileSize) {
            const maxSizeMB = Math.round(maxFileSize / 1024 / 1024);
            const fileSizeMB = Math.round(originalFile.size / 1024 / 1024);
            await this.jd.lib.alert(`파일 크기가 너무 큽니다. (현재: ${fileSizeMB}MB, 최대: ${maxSizeMB}MB)`);
            return false;
        }

        const picaInstance = window.pica();

        const img = document.createElement('img');
        img.src = URL.createObjectURL(originalFile);

        try {
            await img.decode();
        } catch(e) {
            alert('이미지 로드에 실패했습니다.');
            throw new Error('이미지 로드 실패');
        }

        let width = img.naturalWidth;
        let height = img.naturalHeight;

        // 스케일 적용
        width = Math.round(width * scale);
        height = Math.round(height * scale);

        const srcCanvas = document.createElement('canvas');
        srcCanvas.width = img.naturalWidth;
        srcCanvas.height = img.naturalHeight;
        srcCanvas.getContext('2d').drawImage(img, 0, 0);

        const dstCanvas = document.createElement('canvas');
        dstCanvas.width = width;
        dstCanvas.height = height;

        await picaInstance.resize(srcCanvas, dstCanvas);

        return new Promise(resolve => {
            dstCanvas.toBlob(blob => {
                const resizedFile = new File(
                    [blob],
                    originalFile.name,
                    {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    }
                );
                resolve(resizedFile);
            }, 'image/jpeg', quality);
        });
    }
}