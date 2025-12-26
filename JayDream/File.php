<?php
namespace JayDream;

use JayDream\Lib;
use JayDream\Config;

class File {
    public static function save($file, $table,$obj, $primary = "") {
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
        $savedName = Lib::generateUniqueId() . '.' . $ext;

        // 최종 저장 경로
        $targetPath = $basePath . '/' . $savedName;

        // 실제 이동
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Lib::error("파일 저장 실패");
        }

        //파일에 대한 정보를 따로 담은 변수
        $uniqueKey = base64_encode(urlencode($file['name'] . '_' . $file['size']));
        $info = isset($obj[$uniqueKey]) ? $obj[$uniqueKey] : null;
        
        //고정 저장 정보
        $fixed = [
            'table_name'      => $table,
            'table_primary'   => $primary,
            'name'            => $originalName,
            'size'            => $file['size'],
            'height'          => $info['height'] ?? null,
            'width'           => $info['width'] ?? null,
            'ext'             => $ext,
            'src'             => '/' . str_replace(Config::$ROOT . '/', '', $targetPath),
            'path'            => $targetPath,
            'rename'          => $savedName,
        ];


        // $obj 랑 합쳐서 저장 정보 반환
        return array_merge($obj, $fixed);
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
            }
        }
    }

    public static function normalize($files) {
        $normalized = [];

        foreach ($files as $field => $data) {
            // 유효하지 않은 데이터 무시
            if (
                empty($data) ||
                !isset($data['name']) ||
                (is_array($data['name']) && count(array_filter($data['name'])) === 0) ||
                (is_string($data['name']) && trim($data['name']) === '')
            ) {
                continue;
            }

            // 단일 파일일 경우
            if (is_string($data['name'])) {
                $normalized[] = [
                    'name'     => $data['name'],
                    'type'     => $data['type'],
                    'tmp_name' => $data['tmp_name'],
                    'error'    => $data['error'],
                    'size'     => $data['size'],
                    'keyword'    => $field, // 폼의 input name 추적용 (선택)
                ];
            }
            // 복수 파일일 경우
            else {
                foreach ($data['name'] as $i => $name) {
                    if (trim($name) === '') continue;

                    $normalized[] = [
                        'name'     => $data['name'][$i],
                        'type'     => $data['type'][$i],
                        'tmp_name' => $data['tmp_name'][$i],
                        'error'    => $data['error'][$i],
                        'size'     => $data['size'][$i],
                        'keyword'    => $field, // 어떤 필드에서 왔는지 추적 가능
                    ];
                }
            }
        }

        return $normalized;
    }
}
?>