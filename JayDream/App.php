<?php
namespace JayDream;

use JayDream\Config;
use JayDream\Lib;

class App {
    public static $JS_LOAD = false;
    public static $VUE_LOAD = false;
    public static $PLUGINS = array();

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
            echo "<script>";
            echo "const JayDream_url = '".Config::$URL."';";
            echo "const JayDream_dev = ".json_encode(Config::$DEV).";";     // false 일때 빈값으로 들어가 jl 에러가 나와 encode처리
            echo "const JayDream_alert = '".Config::ALERT."';";
            //Vue 데이터 연동을 위한 변수
            echo "let JayDream_data = {};";
            echo "let JayDream_methods = {};";
            echo "let JayDream_watch = {};";
            echo "let JayDream_computed = {};";
            //Vue3 데이터 연동을 위한 변수
            echo "let JayDream_vue = [];";
            echo "let JayDream_components = [];";

            echo "</script>";
            echo "<script src='".Config::$URL."/JayDream/js/init.js'></script>";
            echo "<script src='".Config::$URL."/JayDream/js/prototypes.js'></script>";
            echo "<script src='".Config::$URL."/JayDream/js/lib.js'></script>";
            echo "<script src='".Config::$URL."/JayDream/js/plugin.js'></script>";
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
                echo '<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.css" rel="stylesheet">';
                echo '<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>';
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
