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