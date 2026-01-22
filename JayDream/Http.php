<?php
namespace JayDream;

use JayDream\Lib;

class Http
{
    private static $instance;

    private $url;
    private $method = 'GET';
    private $headers = [];
    private $query = [];
    private $body = null;
    private $timeout = 10;

    private static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /* -------- static entry -------- */
    public static function url($url)
    {
        $instance = self::getInstance();
        $instance->url = $url;
        return $instance;
    }

    public static function header($key, $value)
    {
        $instance = self::getInstance();
        $instance->headers[] = "{$key}: {$value}";
        return $instance;
    }

    public static function headers(array $headers)
    {
        $instance = self::getInstance();
        foreach ($headers as $k => $v) {
            $instance->headers[] = "{$k}: {$v}";
        }
        return $instance;
    }

    public static function query(array $query)
    {
        $instance = self::getInstance();
        $instance->query = $query;
        return $instance;
    }

    public static function json(array $data)
    {
        $instance = self::getInstance();
        $instance->body = json_encode($data);
        $instance->headers[] = 'Content-Type: application/json';
        return $instance;
    }

    public static function form(array $data)
    {
        $instance = self::getInstance();
        $instance->body = http_build_query($data);
        $instance->headers[] = 'Content-Type: application/x-www-form-urlencoded';
        return $instance;
    }

    public static function timeout($seconds)
    {
        $instance = self::getInstance();
        $instance->timeout = $seconds;
        return $instance;
    }

    /* -------- execution -------- */
    public static function get()
    {
        $instance = self::getInstance();
        $result = $instance->send('GET');
        $instance->reset();
        return $result;
    }

    public static function post()
    {
        $instance = self::getInstance();
        $result = $instance->send('POST');
        $instance->reset();
        return $result;
    }

    public static function delete()
    {
        $instance = self::getInstance();
        $result = $instance->send('DELETE');
        $instance->reset();
        return $result;
    }

    public static function file($fileData)
    {
        $instance = self::getInstance();

        if (is_array($fileData) && isset($fileData['tmp_name'])) {
            $instance->body['file'] = new \CURLFile(
                $fileData['tmp_name'],
                $fileData['type'],
                $fileData['name']
            );
        }

        return $instance;
    }

    protected function send($method)
    {
        $ch = curl_init();

        // query 처리
        $url = $this->url;
        if (!empty($this->query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&')
                . http_build_query($this->query);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        if (is_array($this->body) && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        if ($this->body !== null && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        }

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);

        curl_close($ch);

        $result = array(
            'success' => false,
            'code'    => $code,
            'error'   => null,
            'data'    => null,
        );

        // response JSON decode 시도 함수
        $decodeResponse = function ($response) {
            if (!is_string($response) || $response === '') {
                return $response;
            }

            $decoded = json_decode($response, true);

            if ($decoded !== null || json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return $response;
        };

        // 1️⃣ cURL 자체 에러
        if ($error) {
            $result['error'] = 'cURL Error: ' . $error;
            $result['data']  = $decodeResponse($response);
            return $result;
        }

        // 2️⃣ HTTP 에러 코드
        if ($code < 200 || $code >= 300) {
            $result['error'] = 'HTTP ' . $code;
            $result['data']  = $decodeResponse($response);
            return $result;
        }

        // 3️⃣ 정상 응답
        $result['success'] = true;
        $result['data']    = $decodeResponse($response);

        return $result;
    }

    /* -------- reset -------- */
    private function reset()
    {
        $this->url = null;
        $this->method = 'GET';
        $this->headers = [];
        $this->query = [];
        $this->body = null;
        $this->timeout = 10;
        self::$instance = null;
    }

    public static function debug()
    {
        $instance = self::getInstance();

        // 최종 URL (query 포함)
        $url = $instance->url;
        if (!empty($instance->query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&')
                . http_build_query($instance->query);
        }

        // body 정보 정리
        $bodyInfo = null;

        if (is_array($instance->body)) {
            $bodyInfo = [];

            foreach ($instance->body as $key => $value) {
                if ($value instanceof \CURLFile) {
                    $bodyInfo[$key] = [
                        'type' => 'file',
                        'name' => $value->getPostFilename(),
                        'mime' => $value->getMimeType(),
                        'path' => $value->getFilename(),
                    ];
                } else {
                    $bodyInfo[$key] = $value;
                }
            }
        } else {
            $bodyInfo = $instance->body;
        }

        return [
            'url'     => $url,
            'method'  => $instance->method ?? 'AUTO',
            'headers' => $instance->headers,
            'body'    => $bodyInfo,
            'timeout' => $instance->timeout,
        ];
    }
}