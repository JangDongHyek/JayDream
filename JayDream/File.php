<?php
namespace JayDream;

use JayDream\Lib;
use JayDream\Config;

class File {

    private $path;
    private $resizing;

    function __construct($path = "",$resizing = false){
        $this->resizing = $resizing;

        if(!empty($path)){
            $this->path = Config::$ROOT.$path;

            if(!is_dir($this->path)){
                if(!mkdir($this->path, 0777)) Lib::error("JlFile __construct($path) : 폴더 생성오류");
                chmod($this->path, 0777);
            }
        }
    }

    ///////////////////////////////////////////////////
    // size : 파일 사이즈 반환 함수
    ///////////////////////////////////////////////////
    function size($file){
        return $file['size'];
    }

    ///////////////////////////////////////////////////
    // size : 파일 확장자 반환 함수
    ///////////////////////////////////////////////////
    function ext($file, $comma = false){
        return $comma ? '.'.pathinfo($file['name'], PATHINFO_EXTENSION) : pathinfo($file['name'], PATHINFO_EXTENSION);
    }

    ///////////////////////////////////////////////////
    // save : 파일 저장함수
    ///////////////////////////////////////////////////
    function save($file, $path = ""){

        $path = $path ? $path : $this->path;
        if(empty($path)) Lib::error("JlFile : 파일 업로드 경로가 설정되지 않았습니다");


        // 업로드 디렉토리 생성
        if(!is_dir($path)){
            mkdir($path, 0777);
            chmod($path, 0777);
        }

        if(0 < $file['size']){
            copy($file['tmp_name'], $path.'/'.$file['name']);
            chmod($path.'/'.$file['name'], 0777);
            return $file['name'];
        }
    }

    ///////////////////////////////////////////////////
    // rename : 파일 이름 변경함수
    ///////////////////////////////////////////////////
    function rename($src, $dst, $path = ""){
        $path = $path ? $path : $this->path;
        if(empty($path)) Lib::error("JlFile : 파일 경로가 설정되지 않았습니다");


        rename($path.'/'.$src, $path.'/'.$dst);

        return $dst;
    }

    function deleteDirGate($data) {
        if(!$data) return;

        if(Lib::isAssociativeArray($data)) {
            Lib::deleteDir($data['dir']);
        }else {
            if (is_array($data)) {
                foreach ($data as $item) {
                    Lib::deleteDir($item['dir']);
                }
            }
        }

    }

    function bindGate($file,$permission = "",$path = "") {
        if(is_array($file['name'])) $result = $this->multiple_bind($file,$permission,$path);
        else $result = $this->bind($file,$permission,$path);

        return $result;
    }

    function bind($file,$permission = "",$path = "") {
        if($file == "null" || $file == "undefined" || $file == null || $file == "" || $file["size"] == 0) {
            return "";
        }else {
            if (array_key_exists('preview',$file)) {
                unset($file->preview);
            }
            $_idx = uniqid().str_pad(rand(0, 99), 2, "0", STR_PAD_LEFT);
            $path = $path ? $path : $this->path."/$_idx";
            if(empty($path)) Lib::error("JlFile bind(): 파일 경로가 설정되지 않았습니다");
            $permission = $permission ? $permission : $this->getPermission();
            $ext = $this->ext($file,true);
            $ext = strtolower($ext);
            if(!in_array($ext,$permission)) Lib::error("JlFile bind() : 허용된 파일이 아닙니다.");

            if(!$this->PHP) Lib::error("JlFile bind() : PHP 설정을 읽을수가없습니다.");
            $upload_max_filesize = (int)str_replace('M','',$this->PHP['upload_max_filesize']['global_value']);
            $post_max_size = (int)str_replace('M','',$this->PHP['post_max_size']['global_value']);
            $size = Lib::bytesToMB($file['size']);
            if($size >= $upload_max_filesize) Lib::error("JlFile bind() : 파일사이즈가 upload_max_filesize보다 큽니다.\nfile : $size\nupload_max_filesize : $upload_max_filesize");
            if($size >= $post_max_size) Lib::error("JlFile bind() : 파일사이즈가 post_max_size보다 큽니다.\nfile : {$size}MB\npost_max_size : {$post_max_size}MB");

            $src = $this->save($file,$path);

            // 파일네임변경 * 이미지 알집다운로드 필요시 주석처리
            $dst = $_idx.$ext;
            $this->rename($src,$dst,$path);
            $src = $dst;

            $image_path = str_replace(Config::$ROOT,"",$path);
            $file['status'] = "read";
            $file['dir'] = $image_path;
            $file['src'] = $image_path."/".$src;

            if($this->resizing) {
                $file['resize_src'] = $image_path."/resize_".$src;
                $this->resize_image($file['src'],$file['resize_src'],200,100);
            }

            return Lib::jsonEncode($file);
        }
    }

    function multiple_bind($files,$permission = "",$path = "") {
        $datas = array();
        for ($i=0; $i < count($files['name']); $i++) {
            $file = array(
                "name" => $files['name'][$i],
                "type" => $files['type'][$i],
                "tmp_name" => $files['tmp_name'][$i],
                "size" => $files['size'][$i]
            );

            $data = $this->bind($file,$permission,$path);

            if($data == "null" || $data == null) continue;

            array_push($datas,json_decode($data));
        }

        return Lib::jsonEncode($datas);
    }

    function resize_image($file, $newfile, $w, $h) {
        list($width, $height) = getimagesize($file);
        if(strpos(strtolower($file), ".jpg"))
            $src = imagecreatefromjpeg($file);
        else if(strpos(strtolower($file), ".png"))
            $src = imagecreatefrompng($file);
        else if(strpos(strtolower($file), ".gif"))
            $src = imagecreatefromgif($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
        if(strpos(strtolower($newfile), ".jpg"))
            imagejpeg($dst, $newfile);
        else if(strpos(strtolower($newfile), ".png"))
            imagepng($dst, $newfile);
        else if(strpos(strtolower($newfile), ".gif"))
            imagegif($dst, $newfile);
    }

    function getPermission() {
        // 이미지
        $images = array(".bmp",".gif",".jpeg",".jpg",".png",".psd",".pic",".raw",".tiff");

        // 동영상
        $movies = array(".avi",".flv",".mkv",".mov");

        // 음성
        $audios = array(".mp3",".mp4",".wav",".wma");

        // 문서
        $docs = array(".ppt",".pptx",".doc",".docx",".xls",".xlsx",".pdf",".ai",".hwp",".hwpx",".txt");

        // 압축파일
        $compress = array(".zip",".alz",".egg",".rar");

        $arrays = array_merge($images,$movies,$audios,$docs,$compress);

        return $arrays;
    }
}
?>