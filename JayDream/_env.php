<?php
return [
    'VERSION' => '5',
    // DB
    'HOSTNAME' => 'localhost',
    'DATABASE' => 'exam',
    'USERNAME' => 'exam',
    'PASSWORD' => 'password',

    // 기타 사용자 설정
    "DEV_IPS"       =>  ['127.0.0.1'],  // 해당 하는 ip면 dev로 자동 변경
    "COOKIE_TIME"   =>  7200,           // jwt 쿠키 타임 해당 시간동안 사용자가 아무것도 안할시 통신안됨
    "ALERT"         =>  "origin",       // origin , swal
    'ENCRYPT'       =>  'md5',          // md5,sha256,sha512,hmac,gnuboard,ci4;
    "REWRITE"       =>  null,           // true,false null 일경우 환경에 맞게 자동으로 대응된다 ci = true , 나머지 false

    "Cloudflare_image_server" => false,  // cloudflare 이미지 저장소 api를 사용한다면 true ? 파일 저장로직에 이미지들이 자동으로 cloudflare에 저장된다
    "JS_image_resizing" => false, // true 이면 image 업로드할때 vue.js/resizeWithPica() 함수 실행됌
];