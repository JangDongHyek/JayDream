<?php
namespace JayDream;

use JayDream\Lib;
use JayDream\Config;

class File {
    public static function save($file, $table, $primary) {
        // 유효성 체크
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            Lib::error("업로드된 파일이 유효하지 않습니다.");
        }

        // 리소스 경로 기반 저장 경로 만들기
        $basePath = Config::resourcePath() . "/{$table}/{$primary}";

        // 디렉토리 없으면 생성
        if (!is_dir($basePath)) {
            if (!mkdir($basePath, 0755, true)) {
                Lib::error("디렉토리 생성 실패: {$basePath}");
            }
        }

        // 원본 파일명과 확장자
        $originalName = $file['name'];
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = pathinfo($originalName, PATHINFO_FILENAME);

        // 저장 파일명 중복 방지로 고유값 사용
        $savedName = $primary. '.' . $ext;

        // 최종 저장 경로
        $targetPath = $basePath . '/' . $savedName;

        // 실제 이동
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Lib::error("파일 저장 실패");
        }

        // 저장 정보 반환
        return [
            'table_name'    => $table,
            'table_primary' => $primary,
            'name'          => $originalName,
            'size'          => $file['size'],
            'ext'           => $ext,
            'src'           => '/' . str_replace(Config::$ROOT . '/', '', $targetPath),
            'path'          => $targetPath,
            'rename'        => $savedName
        ];
    }

    public static function delete($data) {
        // 파일이 존재하면 삭제
        if (file_exists($data['path'])) {
            unlink($data['path']); // 파일 삭제
        } else {
            Lib::error("File > delete() : 파일이 존재하지않습니다.");
        }

        $parentDir = dirname($data['path']);

        // 디렉토리가 비어있는지 확인 후 삭제
        if (is_dir($parentDir)) {
            $files = scandir($parentDir);
            $files = array_diff($files, array('.', '..')); // 현재/상위 디렉토리 제외

            if (empty($files)) {
                rmdir($parentDir); // 디렉토리 비어있으면 삭제
            } else {
                Lib::error("File > delete() : 디렉토리안에 파일이 존재해 폴더 삭제를 중단합니다.");
            }
        }
    }
}
?>