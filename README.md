# jaydream
[![PHP](https://img.shields.io/badge/PHP-5.3%20~%208.x-blue?logo=php)]()
[![Vue](https://img.shields.io/badge/Vue.js-3.x-green?logo=vue.js)]()
[![GitHub](https://img.shields.io/badge/GitHub-jaydream-black?logo=github)](https://github.com/JangDongHyek/JayDream)

## ✨ 프레임워크 특징 (Highlights)

`JayDream`은 PHP 환경에서 **Vue.js 기반의 동적 페이지 구성**, **세션/DB/파일 자동화**, **JWT 기반 단일 API 통신 보안**,  
그리고 **실제 서비스 운영을 위한 난독화/버전 관리 체계**까지 갖춘 통합 프레임워크입니다.

---

### 🔹 환경 및 자동화
- ⚙️ **환경 자동 인식 및 세션 관리**  
  실행 중인 서버 환경에 따라 세션을 자동으로 생성하고 유지합니다.  
  탭 간 세션 동기화와 비동기 접근(`this.session.get/set`)을 지원합니다.

- 🧩 **플러그인 사용 시 전용 스키마 자동 생성**  
  각 플러그인 실행 시 필요한 DB 스키마를 자동으로 감지 및 생성합니다.  
  별도의 SQL 파일이나 수동 마이그레이션이 필요 없습니다.

- 📂 **파일 테이블 및 리소스 자동 관리**  
  업로드된 파일은 자동으로 테이블에 등록되고, 삭제 시 파일 리소스도 함께 정리됩니다.  
  `File::save()` / `File::delete()` 함수로 일관된 파일 관리가 가능합니다.

- 🧠 **Vue.js 완전 통합 지원**  
  `<script type="text/x-template">` 기반 컴포넌트 구조를 지원하여  
  PHP에서도 손쉽게 Vue 컴포넌트를 로드할 수 있습니다.

---

### 🔒 보안 및 배포 구조
- 🧱 **Dev / Prod 버전 분리**
    - **Dev 버전**: 개발용 디버그 및 로그 기능 활성화
    - **Prod 버전**: 난독화 처리 및 내부 코드 암호화로 보안 강화  
      ip기반으로 자동 상용버전이 되고 코드는 난독화되어 코드 유출 위험을 최소화합니다.

- 🔐 **JWT 기반 단일 엔트리 구조**  
  모든 통신은 `/JayDream/api.php` 단일 엔드포인트를 통해 이루어지며,  
  내부적으로 JWT 토큰 검증을 수행하여 API 위변조를 방지합니다.

- 🧰 **멀티 프레임워크 호환성**
    - Legacy PHP 5.3 ~ 8.x
    - GNUBoard 기반 솔루션
    - CodeIgniter 3 / 4  
      다양한 PHP 환경에서도 동일한 코드로 동작합니다.

---

---

## 📦 설치 (Installation)

PHP 프로젝트 루트에 JayDream 폴더를 복사한 후, 777권한과 아래 한 줄만 추가하면 됩니다.

```php
<?php include_once "./JayDream/init.php"; ?>
```

## ⚙️ 기본 설정 (Configuration)

설치 후 아래 파일만 수정하면 바로 실행 가능합니다.
> 📄 `JayDream/Config.php`

해당 파일에는 개발/상용 구분, DB 설정, JWT 쿠키 유지시간, 알림방식, 암호화 방식 등이 정의되어 있습니다.

```php
<?php
class Config
{
    /** 🔹 개발 환경 설정 */
    private static $DEV_IPS = ["111.11.1.1"]; 
    // 위 목록에 포함된 IP로 접속 시 자동으로 Dev 모드 활성화

    /** 🔹 데이터베이스 설정 */
    const HOSTNAME = "localhost";
    const DATABASE = "exam";
    const USERNAME = "exam";
    const PASSWORD = "pass";

    /** 🔹 JWT 및 쿠키 설정 */
    const COOKIE_TIME = 7200; // 초 단위 (기본 2시간)

    /** 🔹 알림(경고창) 방식 */
    const ALERT = "origin"; // origin | swal

    /** 🔹 암호화 방식 */
    const ENCRYPT = "md5"; // md5 | sha256 | sha512 | hmac | gnuboard | ci4
}
```

## 사용 예시
```php
<?php
include_once(G5_PATH."/JayDream/init.php");
?>

<div id="app">
    <exam-input></exam-input>
</div>

<?php
//매개변수에 해당하는 아이디값을 가진 태그내의 영역에 vue 를 선언한다는뜻입니다 기본값은 app 이며 다중선언이 가능합니다
$jd->vueLoad("app");

// 루트폴더/component/exam 를 로드한다는뜻입니다 폴더명이라면 폴더안에있는 파일을 전체로드 합니다. (폴더에폴더제외)
// id="app" 태그안에 선언된 vue 태그가 파일명입니다 ex) <exam-input> = public_html/component/exam/exam-input.php
$jd->componentLoad("/exam");
?>
```

## component 사용법
해당 파일에 더 상세한 사용법이 적혀있습니다.
> 📄 `JayDream/component/detail.php`
```php
<?php
$componentName = str_replace(".php","",basename(__FILE__));
?>
<script type="text/x-template" id="<?=$componentName?>-template">
    <div v-if="load">

    </div>

    <div v-if="!load"><div class="loader"></div></div>
</script>

<script>
    JayDream_components.push({name : "<?=$componentName?>",object : {
            template: "#<?=$componentName?>-template",
            props: {
                primary : {type : String, default : ""},
            },
            data: function () {
                return {
                    load : false,
                    component_name : "<?=$componentName?>",
                    component_idx: "",

                    row: null,
                    rows : [],
                };
            },
            async created() {
                this.component_idx = this.lib.generateUniqueId();
            },
            async mounted() {
                //this.row = await this.api.get(this.filtering);
                //await this.api.gets(this.filtering,this.rows);
                //this.session.set('exam','exam') // (string,array,object)
                //let exam = await this.session.get('exam') // (string,array,object)

                this.load = true;

                this.$nextTick(async () => {
                    // 해당부분에 퍼블리싱 라이브러리,플러그인 선언부분 하시면 됩니다 ex) swiper
                });
            },
            updated() {

            },
            methods: {

            },
            computed: {
                filtering() {
                    // let filter = {table : ""}
                    return { ...((typeof filter !== 'undefined' ? filter : this.filter) || {}), ...(this.paging ? { paging: this.paging } : {}) }
                }
            },
            watch: {

            }
        }});
</script>
```