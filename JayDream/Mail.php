<?php
namespace JayDream;

class Mail
{
    private static $instance;

    // SMTP 설정 (전역)
    private static $smtp_host = 'localhost';
    private static $smtp_port = 25;
    private static $smtp_user = '';
    private static $smtp_pass = '';

    // 메일 데이터
    private $from_email;
    private $from_name;
    private $to_email;
    private $title;
    private $content;
    private $cc = array();
    private $bcc = array();
    private $is_html = false;

    private $error;

    private static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * SMTP 설정 (한번만 설정)
     */
    public static function init($host, $port = 25, $user = '', $pass = '')
    {
        self::$smtp_host = $host;
        self::$smtp_port = $port;
        self::$smtp_user = $user;
        self::$smtp_pass = $pass;
    }

    /* -------- static entry -------- */
    public static function from($email, $name = '')
    {
        $instance = self::getInstance();
        $instance->from_email = $email;
        $instance->from_name = $name;
        return $instance;
    }

    public static function to($email)
    {
        $instance = self::getInstance();
        $instance->to_email = $email;
        return $instance;
    }

    public static function title($title)
    {
        $instance = self::getInstance();
        $instance->title = $title;
        return $instance;
    }

    public static function content($content, $is_html = false)
    {
        $instance = self::getInstance();
        $instance->content = $content;
        $instance->is_html = $is_html;
        return $instance;
    }

    public static function html($content)
    {
        $instance = self::getInstance();
        $instance->content = $content;
        $instance->is_html = true;
        return $instance;
    }

    public static function cc($email)
    {
        $instance = self::getInstance();
        $instance->cc[] = $email;
        return $instance;
    }

    public static function bcc($email)
    {
        $instance = self::getInstance();
        $instance->bcc[] = $email;
        return $instance;
    }

    /* -------- execution -------- */
    public static function send()
    {
        $instance = self::getInstance();
        $result = $instance->sendMail();
        $instance->reset();
        return $result;
    }

    private function sendMail()
    {
        // 필수 값 체크
        if (!$this->from_email || !$this->to_email || !$this->title || !$this->content) {
            $this->error = '필수 항목이 누락되었습니다 (from, to, title, content)';
            return array(
                'success' => false,
                'error' => $this->error
            );
        }

        // SMTP 연결
        $socket = @fsockopen(self::$smtp_host, self::$smtp_port, $errno, $errstr, 30);
        if (!$socket) {
            $this->error = "SMTP 연결 실패: $errstr ($errno)";
            return array('success' => false, 'error' => $this->error);
        }

        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($socket);
            $this->error = "SMTP 서버 응답 오류: $response";
            return array('success' => false, 'error' => $this->error);
        }

        // EHLO/HELO
        fputs($socket, 'EHLO ' . self::$smtp_host . "\r\n");
        $response = $this->readResponse($socket);
        if (substr($response, 0, 3) != '250') {
            fputs($socket, 'HELO ' . self::$smtp_host . "\r\n");
            $this->readResponse($socket);
        }

        // 인증 (필요한 경우)
        if (self::$smtp_user && self::$smtp_pass) {
            fputs($socket, "AUTH LOGIN\r\n");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                $this->error = "인증 시작 실패: $response";
                return array('success' => false, 'error' => $this->error);
            }

            fputs($socket, base64_encode(self::$smtp_user) . "\r\n");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                $this->error = "사용자명 인증 실패: $response";
                return array('success' => false, 'error' => $this->error);
            }

            fputs($socket, base64_encode(self::$smtp_pass) . "\r\n");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) != '235') {
                fclose($socket);
                $this->error = "비밀번호 인증 실패: $response";
                return array('success' => false, 'error' => $this->error);
            }
        }

        // MAIL FROM
        fputs($socket, 'MAIL FROM: <' . $this->from_email . ">\r\n");
        $response = $this->readResponse($socket);
        if (substr($response, 0, 3) != '250') {
            fclose($socket);
            $this->error = "MAIL FROM 실패: $response";
            return array('success' => false, 'error' => $this->error);
        }

        // RCPT TO
        $recipients = array_merge(
            array($this->to_email),
            $this->cc,
            $this->bcc
        );

        foreach ($recipients as $recipient) {
            fputs($socket, 'RCPT TO: <' . trim($recipient) . ">\r\n");
            $response = $this->readResponse($socket);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                $this->error = "RCPT TO 실패 ($recipient): $response";
                return array('success' => false, 'error' => $this->error);
            }
        }

        // DATA
        fputs($socket, "DATA\r\n");
        $response = $this->readResponse($socket);
        if (substr($response, 0, 3) != '354') {
            fclose($socket);
            $this->error = "DATA 명령 실패: $response";
            return array('success' => false, 'error' => $this->error);
        }

        // 헤더 구성
        $headers = $this->buildHeaders();
        $body = $headers . "\r\n" . $this->content . "\r\n.";

        fputs($socket, $body . "\r\n");
        $response = $this->readResponse($socket);
        if (substr($response, 0, 3) != '250') {
            fclose($socket);
            $this->error = "메일 전송 실패: $response";
            return array('success' => false, 'error' => $this->error);
        }

        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return array('success' => true);
    }

    private function buildHeaders()
    {
        $headers = array();

        // From
        if ($this->from_name) {
            $headers[] = 'From: ' . $this->encodeHeader($this->from_name) . ' <' . $this->from_email . '>';
        } else {
            $headers[] = 'From: <' . $this->from_email . '>';
        }

        // To
        $headers[] = 'To: <' . $this->to_email . '>';

        // CC
        if (!empty($this->cc)) {
            $headers[] = 'Cc: ' . implode(', ', $this->cc);
        }

        // Subject
        $headers[] = 'Subject: ' . $this->encodeHeader($this->title);

        // Date
        $headers[] = 'Date: ' . date('r');

        // MIME
        $headers[] = 'MIME-Version: 1.0';

        if ($this->is_html) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }

        $headers[] = 'Content-Transfer-Encoding: 8bit';

        return implode("\r\n", $headers);
    }

    private function encodeHeader($text)
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }

    /**
     * SMTP 멀티라인 응답 완전히 읽기
     */
    private function readResponse($socket)
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            // 4번째 문자가 공백이면 마지막 라인
            if (isset($line[3]) && $line[3] == ' ') {
                break;
            }
        }
        return $response;
    }

    private function reset()
    {
        $this->from_email = null;
        $this->from_name = null;
        $this->to_email = null;
        $this->title = null;
        $this->content = null;
        $this->cc = array();
        $this->bcc = array();
        $this->is_html = false;
        $this->error = null;
        self::$instance = null;
    }

    public static function getError()
    {
        $instance = self::getInstance();
        return $instance->error;
    }
}

?>