<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;
use Firebase\JWT\JWT;

class App {
    public static $JS_LOAD = false;
    public static $VUE_LOAD = false;
    public static $PLUGINS = array();

    function __construct() {
        // JWT 쿠키 생성
        if(empty($_COOKIE['jd_jwt_token'])) {
            $payload = array(
                "iss" => Config::$URL,
                "sub" => "api:access",
                "iat" => time(),
                "exp" => time() + Config::COOKIE_TIME,
            );

            $jwt = JWT::encode($payload, Config::PASSWORD);
            setcookie("jd_jwt_token",$jwt,time() + Config::COOKIE_TIME,"/","",false,true);
        }
    }

    function vueLoad($app_name = "app",$plugin = array()) {
        if(!self::$VUE_LOAD) {
            $this->jsLoad($plugin);
            if(Config::$DEV) {
                echo '<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>';
            }
            else {
                echo '<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>';
            }

            self::$VUE_LOAD = true;
        }

        echo "<script>";
        echo "document.addEventListener('DOMContentLoaded', function(){";
        echo "vueLoad('$app_name')";
        echo "}, false);";
        echo "</script>";
    }

    function jsLoad($plugin = array()) {
        if(!self::$JS_LOAD) {
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>';
            echo "<script>";
            echo Lib::js_obfuscate("var JayDream_url = '".Config::$URL."';");
            echo Lib::js_obfuscate("var JayDream_dev = ".json_encode(Config::$DEV).";");     // false 일때 빈값으로 들어가 jl 에러가 나와 encode처리
            echo Lib::js_obfuscate("var JayDream_alert = '".Config::ALERT."';");
            //Vue 데이터 연동을 위한 변수
            echo Lib::js_obfuscate("var JayDream_data = {};");
            echo Lib::js_obfuscate("var JayDream_methods = {};");
            echo Lib::js_obfuscate("var JayDream_watch = {};");
            echo Lib::js_obfuscate("var JayDream_computed = {};");
            //Vue3 데이터 연동을 위한 변수
            echo Lib::js_obfuscate("var JayDream_vue = [];");
            echo Lib::js_obfuscate("var JayDream_components = [];");
            //통신 복호화 키
            $key = substr(hash('sha256', Config::USERNAME), 0, 32); // 32바이트 = AES-256 키
            $iv  = substr(hash('sha256', Config::PASSWORD), 0, 16); // 16바이트 = IV
            echo Lib::js_obfuscate("var JayDream_api_key = CryptoJS.enc.Utf8.parse('".$key."');");
            echo Lib::js_obfuscate("var JayDream_api_iv = CryptoJS.enc.Utf8.parse('".$iv."');");

            echo "</script>";
            echo "<script src='".Config::$URL."/JayDream/js/init.js'></script>";
            echo "<script src='".Config::$URL."/JayDream/js/prototypes.js'></script>";
            echo "<script src='".Config::$URL."/JayDream/js/lib.js'></script>";
            echo "<script src='".Config::$URL."/JayDream/js/plugin.js'></script>";
            echo "<script src='".Config::$URL."/JayDream/js/vue.js'></script>";

            self::$JS_LOAD = true;
            echo "<script>";
            echo "</script>";
        }

        $this->pluginLoad($plugin);
    }

    function pluginLoad($plugin = array()) {
        $plugins = Lib::convertToArray($plugin);

        if(in_array('drag',$plugins)) {
            if(!in_array("drag",self::$PLUGINS)) {
                echo '<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.8.4/Sortable.min.js"></script>';
                echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.20.0/vuedraggable.umd.min.js"></script>';
                array_push(self::$PLUGINS,"drag");
            }
        }

        if(in_array('swal',$plugins)) {
            if(!in_array("swal",self::$PLUGINS)) {
                echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">';
                echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>';
                array_push(self::$PLUGINS,"swal");
            }
        }

        if(in_array('jquery',$plugins)) {
            if(!in_array("jquery",self::$PLUGINS)) {
                echo '<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>';
                array_push(self::$PLUGINS,"jquery");
            }
        }

        if(in_array('summernote',$plugins)) {
            if(!in_array("summernote",self::$PLUGINS)) {
                echo '<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">';
                echo '<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>';
                array_push(self::$PLUGINS,"summernote");
            }
        }

        if(in_array('bootstrap',$plugins)) {
            if(!in_array("bootstrap",self::$PLUGINS)) {
                echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>';
                echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>';
                array_push(self::$PLUGINS,"bootstrap");
            }
        }

        if(in_array('viewer',$plugins)) {
            if(!in_array("viewer",self::$PLUGINS)) {
                echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/viewerjs@latest/dist/viewer.min.css">';
                echo '<script src="https://cdn.jsdelivr.net/npm/viewerjs@latest/dist/viewer.min.js"></script>';
                array_push(self::$PLUGINS,"viewer");
            }
        }

        if(in_array('swiper',$plugins)) {
            if(!in_array("swiper",self::$PLUGINS)) {
                //echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">';
                echo '<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>';
                array_push(self::$PLUGINS,"swiper");
            }
        }
    }

    function componentLoad($path) {
        if($path[0] != "/") $path = "/".$path;

        $path = Config::$ROOT."/component".$path;

        if(is_file($path)) {
            include_once($path);
        }else if(is_file($path.".php")){
            include_once($path.".php");
        }else if(is_dir($path)) {
            Lib::includeDir($path);
        }else {
            Lib::error("Jl componentLoad() : $path 가 존재하지않습니다.");
        }
    }
}
