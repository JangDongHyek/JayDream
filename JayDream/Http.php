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
            CURLOPT_HTTPHEADER     => array_merge(
                ['Accept: application/json'],
                $this->headers
            ),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        if ($this->body !== null && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        }

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);

        curl_close($ch);

        if ($error) {
            Lib::error("cURL Error: {$error}");
        }

        if ($code < 200 || $code >= 300) {
            Lib::error("HTTP {$code}: {$response}");
        }

        return json_decode($response, true);
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
}