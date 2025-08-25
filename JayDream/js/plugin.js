class JayDreamPlugin {
    constructor(jd) {
        this.jd = jd;
    }

    async alert(message, options = {}) {
        switch (this.jd.alert) {
            case "origin" : {
                alert(message);
                break;
            }
            case "swal" : {
                await this.swalAlert(message,options);
                break;
            }
        }
    }

    async swalAlert(message, options = {}) {
        const defaultOptions = {
            title: message,
            icon: null,
            confirmButtonText: "확인",
            focusConfirm: true,
            didOpen: (popup) => {
                const btn = popup.querySelector(".swal2-confirm");

                // document 레벨에서 keydown 이벤트 등록
                const handler = (e) => {
                    if (e.key === "Enter") {
                        btn.click();   // 버튼 클릭과 동일하게 실행
                        document.removeEventListener("keydown", handler); // 이벤트 제거
                    }
                };
                document.addEventListener("keydown", handler);

                // 스타일 커스터마이징
                popup.querySelector(".swal2-title").style.fontSize = "16px";
                btn.style.backgroundColor = "#4e73df";
                btn.style.border = "0";
            },
        };

        await Swal.fire({ ...defaultOptions, ...options });
    }

    async confirm(message, options = {}) {
        switch (this.jd.alert) {
            case "origin" : {
                return confirm(message);
            }
            case "swal" : {
                return await this.swalConfirm(message,options);
            }
        }
    }

    async swalConfirm(message, options = {}) {
        const defaultOptions = {
            title: message,
            icon: null,
            showCancelButton: true,
            confirmButtonText: "확인",
            cancelButtonText: "취소",
            didOpen: (popup) => {
                popup.querySelector(".swal2-title").style.fontSize = "16px";
                const btn = popup.querySelector(".swal2-confirm");
                btn.style.backgroundColor = "#4e73df";
                btn.style.border = "0";
            },
        };

        const result = await Swal.fire({ ...defaultOptions, ...options });

        return result.isConfirmed; // 확인: true, 취소: false
    }

    async toast(message, options = {}) {
        switch (this.jd.alert) {
            case "origin" : {
                //confirm(message);
                break;
            }
            case "swal" : {
                await this.swalToast(message,options);
                break;
            }
        }
    }

    async swalToast(message, options = {}) {
        const defaultOptions = {
            toast: true,
            position: 'top-center',
            showConfirmButton: false,
            timer: options.duration || 1200,
            timerProgressBar: true,
        };

        const Toast = Swal.mixin(defaultOptions);

        await Toast.fire({
            title: message,
            icon: options.icon || null
        });
    }

    /*
       this.$nextTick(() =>{
            this.$jd.plugin.imageViewer('main_image',{
                zIndex:9999,
                toolbar : false,
                title : (image) => {return this.data.main_image_array[i].name}
            })
        });
     */
    imageViewer(tag_id,options = {}) {
        /*
           options = {
               inline: false, // 1. true 시 인라인으로 표시, false 시 모달로 표시됩니다 (기본값: false).
               button: true,  // 2. 오른쪽 상단 닫기 버튼 표시 여부 (기본값: true).
               navbar: true,  // 3. 하단 썸네일 네비게이션 표시 여부 (기본값: true).
               title: true,   // 4. 상단에 이미지 제목과 번호를 표시 여부 (기본값: true).
               title : (image) => {return example.jpg} // 밑에 뜨는 제목을 변경하고싶을떄 함수로 이용해서 변경해야함
               toolbar: true, // 5. 하단 툴바(확대, 축소, 회전 등) 표시 여부 (기본값: true).
               tooltip: true, // 6. 마우스 오버 시 확대/축소 비율 툴팁 표시 여부 (기본값: true).
               movable: true, // 8. 이미지를 드래그하여 이동할 수 있는지 여부 (기본값: true).
               zIndex: 9999, // 14. Viewer 모달의 z-index 설정 (기본값: 2015).
               transition: true, // 15. 확대/축소 또는 전환 애니메이션 활성화 여부 (기본값: true).
           }
        */
        if (!tag_id) {
            this.alert("tag_id가 필요합니다.");
            return;
        }

        const element = document.getElementById(tag_id);

        if (!element) {
            this.alert(`ID가 '${tag_id}'인 요소를 찾을 수 없습니다.`);
            return;
        }


        new Viewer(element, options);
    }
}