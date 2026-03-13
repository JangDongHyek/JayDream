<?php
namespace JayDream;

/**
 * Google Gmail API OAuth2 메일 발송
 * Mail::send() 에서 mail_driver = 'google' 일때 자동 호출
 * 직접 호출 불필요
 */
class GoogleMail
{
    private static $token_file = __DIR__ . '/token.json';

    /**
     * Mail 인스턴스로부터 데이터를 받아 발송
     */
    public static function sendFromMail($mail_instance)
    {
        $data = $mail_instance->getData();

        $token_result = self::getAccessToken();
        if (!$token_result['success']) {
            return array('success' => false, 'error' => $token_result['error']);
        }

        $access_token = $token_result['access_token'];
        $raw_mail     = self::buildRawMail($data);
        $encoded      = rtrim(strtr(base64_encode($raw_mail), '+/', '-_'), '=');

        $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('raw' => $encoded)));
        $response  = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return array('success' => true);
        }

        return array('success' => false, 'error' => json_encode($response));
    }

    private static function buildRawMail($data)
    {
        $boundary = '----=_Part_' . md5(uniqid());
        $has_attachment = !empty($data['attachments']);

        $from = $data['from_name']
            ? '=?UTF-8?B?' . base64_encode($data['from_name']) . '?= <' . $data['from_email'] . '>'
            : $data['from_email'];

        $headers  = "From: {$from}\r\n";
        $headers .= "To: {$data['to_email']}\r\n";

        if (!empty($data['cc']))  $headers .= "Cc: "  . implode(', ', $data['cc'])  . "\r\n";
        if (!empty($data['bcc'])) $headers .= "Bcc: " . implode(', ', $data['bcc']) . "\r\n";

        $headers .= "Subject: =?UTF-8?B?" . base64_encode($data['title']) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        $content_type = $data['is_html'] ? 'text/html' : 'text/plain';

        if ($has_attachment) {
            $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
            $body  = "--{$boundary}\r\n";
            $body .= "Content-Type: {$content_type}; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($data['content'])) . "\r\n";
            foreach ($data['attachments'] as $attachment) {
                if (!file_exists($attachment['path'])) continue;
                $file_data = chunk_split(base64_encode(file_get_contents($attachment['path'])));
                $file_name = '=?UTF-8?B?' . base64_encode($attachment['name']) . '?=';
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: application/octet-stream; name=\"{$file_name}\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"{$file_name}\"\r\n\r\n";
                $body .= $file_data . "\r\n";
            }
            $body .= "--{$boundary}--";
        } else {
            $headers .= "Content-Type: {$content_type}; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body = chunk_split(base64_encode($data['content']));
        }

        return $headers . $body;
    }

    private static function getAccessToken()
    {
        if (!file_exists(self::$token_file)) {
            return array('success' => false, 'error' => 'token.json 없음. /JayDream/provider/google/oauth2.php 먼저 접속하세요.');
        }

        $token   = json_decode(file_get_contents(self::$token_file), true);
        $expired = !isset($token['access_token']) ||
            (isset($token['created_at']) && (time() - $token['created_at']) > 3500);

        if ($expired) {
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'client_id'     => Config::$MAIL_GOOGLE_CLIENT_ID,
                'client_secret' => Config::$MAIL_GOOGLE_CLIENT_SECRET,
                'refresh_token' => $token['refresh_token'],
                'grant_type'    => 'refresh_token',
            )));
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (!isset($response['access_token'])) {
                return array('success' => false, 'error' => '토큰 갱신 실패: ' . json_encode($response));
            }

            $token['access_token'] = $response['access_token'];
            $token['created_at']   = time();
            file_put_contents(self::$token_file, json_encode($token));
        }

        return array('success' => true, 'access_token' => $token['access_token']);
    }
}