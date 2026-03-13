<?php
/**
 * ================================================================
 * Google OAuth2 Gmail 발송 설정
 * ================================================================
 *
 * [사전 준비 순서]
 *
 * 1. Google Cloud Console (https://console.cloud.google.com) 접속
 *
 * 2. 프로젝트 생성 (또는 기존 프로젝트 선택)
 *
 * 3. API 및 서비스 → 라이브러리 → "Gmail API" 검색 → 사용 클릭
 *
 * 4. API 및 서비스 → OAuth 동의 화면 → 시작하기
 *    - 대상: 내부 (Google Workspace 도메인 내부용)
 *
 * 5. API 및 서비스 → 사용자 인증 정보 → 사용자 인증 정보 만들기
 *    → OAuth 클라이언트 ID
 *    - 유형: 웹 애플리케이션
 *    - 승인된 JS 원본: https://도메인
 *    - 리디렉션 URI: https://도메인/JayDream/provider/google/oauth2.php
 *
 * 6. 생성된 client_id, client_secret 아래에 입력
 *
 * 7. 브라우저에서 https://도메인/JayDream/provider/google/oauth2.php 접속
 *    → 구글 로그인 → 권한 허용
 *    → token.json 생성 완료 메시지 확인
 *    (최초 1회만 하면 이후 자동 갱신)
 *
 * ================================================================
 */

return [
    "success" => true,

    "client_id"     => "",
    "client_secret" => "",
    "redirect_uri"  => "",
];
