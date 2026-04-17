<?php
namespace JayDream;

use JayDream\Http;

/**
 * Google Cloud Translation API v2
 * 텍스트 번역 (API Key 방식)
 *
 * [사용 예시]
 * $result = GoogleTranslate::translate('봄');
 * // ['success' => true, 'text' => 'Spring']
 *
 * $result = GoogleTranslate::translate('spring', 'ko', 'en');
 * // ['success' => true, 'text' => '봄']
 */
class GoogleTranslate
{
    private static $config = null;

    private static function getConfig()
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/config.php';
        }
        return self::$config;
    }

    /**
     * 한국어 여부 감지
     */
    private static function isKorean($text)
    {
        return (bool) preg_match('/[\x{AC00}-\x{D7A3}]/u', $text);
    }

    /**
     * 텍스트 번역 (한국어일 때만 API 호출, 그 외 원문 반환)
     *
     * @param string $text    번역할 텍스트
     * @param string $target  번역 대상 언어 코드 (기본: 'en')
     * @return array ['success' => bool, 'text' => string, 'translated' => bool, 'error' => string|null]
     */
    public static function translate($text, $target = 'en')
    {
        $config = self::getConfig();

        if (empty($config['translate_api_key'])) {
            return [
                'success'    => false,
                'text'       => null,
                'translated' => false,
                'error'      => 'translate_api_key가 설정되지 않았습니다. provider/google/config.php를 확인하세요.',
            ];
        }

        if (empty(trim($text))) {
            return [
                'success'    => false,
                'text'       => null,
                'translated' => false,
                'error'      => '번역할 텍스트가 비어있습니다.',
            ];
        }

        // 한국어 아니면 원문 그대로 반환
        if (!self::isKorean($text)) {
            return [
                'success'    => true,
                'text'       => $text,
                'translated' => false,
                'error'      => null,
            ];
        }

        $result = Http::url('https://translation.googleapis.com/language/translate/v2')
            ->query([
                'key'    => $config['translate_api_key'],
                'q'      => $text,
                'target' => $target,
                'source' => 'ko',
                'format' => 'text',
            ])
            ->get();

        if (!$result['success']) {
            return [
                'success'    => false,
                'text'       => null,
                'translated' => false,
                'error'      => $result['error'] ?? 'API 요청 실패',
            ];
        }

        $translated = $result['data']['data']['translations'][0]['translatedText'] ?? null;

        if ($translated === null) {
            return [
                'success'    => false,
                'text'       => null,
                'translated' => false,
                'error'      => '번역 결과를 파싱할 수 없습니다: ' . json_encode($result['data']),
            ];
        }

        return [
            'success'    => true,
            'text'       => $translated,
            'translated' => true,
            'error'      => null,
        ];
    }
}