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
    private $attachments = array();

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

    /**
     * 첨부파일 추가
     * @param string $filepath 파일 실제 경로
     * @param string $filename 메일에 표시될 파일명 (생략 시 basename 사용)
     */
    public static function attach($filepath, $filename = '')
    {
        $instance = self::getInstance();

        if (!file_exists($filepath)) {
            $instance->error = "첨부파일 없음: $filepath";
            return $instance;
        }

        $instance->attachments[] = array(
            'path'     => $filepath,
            'filename' => $filename ?: basename($filepath),
            'mime'     => mime_content_type($filepath) ?: 'application/octet-stream',
            'data'     => base64_encode(file_get_contents($filepath)),
        );

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
            return array('success' => false, 'error' => $this->error);
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
        $recipients = array_merge(array($this->to_email), $this->cc, $this->bcc);
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

        // 헤더 + 바디 전송
        $message = $this->buildMessage();
        fputs($socket, $message . "\r\n.\r\n");

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

    private function buildMessage()
    {
        $boundary = '----=_Boundary_' . md5(uniqid(time()));

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

        if (!empty($this->attachments)) {
            // 첨부파일 있으면 multipart/mixed
            $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

            $body = implode("\r\n", $headers) . "\r\n\r\n";

            // 본문 파트
            $content_type = $this->is_html ? 'text/html' : 'text/plain';
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Type: ' . $content_type . '; charset=UTF-8' . "\r\n";
            $body .= 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n";
            $body .= $this->content . "\r\n\r\n";

            // 첨부파일 파트
            foreach ($this->attachments as $attachment) {
                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Type: ' . $attachment['mime'] . '; name="' . $this->encodeHeader($attachment['filename']) . '"' . "\r\n";
                $body .= 'Content-Transfer-Encoding: base64' . "\r\n";
                $body .= 'Content-Disposition: attachment; filename="' . $this->encodeHeader($attachment['filename']) . '"' . "\r\n\r\n";
                $body .= chunk_split($attachment['data'], 76, "\r\n");
                $body .= "\r\n";
            }

            $body .= '--' . $boundary . '--';

        } else {
            // 첨부파일 없으면 기존 방식
            $content_type = $this->is_html ? 'text/html' : 'text/plain';
            $headers[] = 'Content-Type: ' . $content_type . '; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';

            $body = implode("\r\n", $headers) . "\r\n\r\n" . $this->content;
        }

        return $body;
    }

    private function encodeHeader($text)
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }

    private function readResponse($socket)
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] == ' ') {
                break;
            }
        }
        return $response;
    }

    private function reset()
    {
        $this->from_email  = null;
        $this->from_name   = null;
        $this->to_email    = null;
        $this->title       = null;
        $this->content     = null;
        $this->cc          = array();
        $this->bcc         = array();
        $this->is_html     = false;
        $this->attachments = array();
        $this->error       = null;
        self::$instance    = null;
    }

    public static function getError()
    {
        $instance = self::getInstance();
        return $instance->error;
    }
}