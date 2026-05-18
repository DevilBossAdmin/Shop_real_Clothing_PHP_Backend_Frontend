<?php
require_once __DIR__ . '/../Core/App.php';

final class Mailer
{
    public static function send(array $payload): bool
    {
        $cfg = App::config('mail');
        $driver = strtolower((string) ($cfg['driver'] ?? 'log'));
        $payload['from'] = [
            'address' => $cfg['from_address'] ?? 'no-reply@example.com',
            'name' => $cfg['from_name'] ?? 'ATINO STYLE',
        ];
        $payload['driver'] = $driver;
        $payload['logged_at'] = date('c');
        $payload['is_html'] = !empty($payload['is_html']);
        if ($driver === 'smtp') return self::sendViaSmtp($payload, $cfg);
        if ($driver === 'mail') return self::sendViaMail($payload);
        self::logMail($payload, $cfg, 'mail_logged');
        return true;
    }
    private static function sendViaMail(array $payload): bool
    {
        $to = (string) ($payload['to'] ?? '');
        $subject = (string) ($payload['subject'] ?? '');
        $body = (string) ($payload['body'] ?? '');
        $from = $payload['from']['address'] ?? 'no-reply@example.com';
        $fromName = $payload['from']['name'] ?? 'ATINO STYLE';
        $isHtml = !empty($payload['is_html']);
        $headers = ['MIME-Version: 1.0', 'Content-Type: ' . ($isHtml ? 'text/html' : 'text/plain') . '; charset=UTF-8', 'From: ' . self::formatAddress($from, $fromName)];
        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
        if (!$ok) self::logMail(array_merge($payload, ['mail_error' => 'PHP mail() trả về false']), App::config('mail'), 'mail_failed');
        return $ok;
    }
    private static function sendViaSmtp(array $payload, array $cfg): bool
    {
        $smtp = $cfg['smtp'] ?? [];
        $host = (string) ($smtp['host'] ?? '');
        $port = (int) ($smtp['port'] ?? 587);
        $username = (string) ($smtp['username'] ?? '');
        $password = (string) ($smtp['password'] ?? '');
        $encryption = strtolower((string) ($smtp['encryption'] ?? 'tls'));
        $timeout = (int) ($smtp['timeout'] ?? 20);
        if ($host === '' || $username === '' || $password === '') { self::logMail(array_merge($payload, ['smtp_error' => 'Thiếu cấu hình SMTP host/username/password']), $cfg, 'smtp_failed'); return false; }
        $transport = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $fp = @stream_socket_client($transport . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$fp) { self::logMail(array_merge($payload, ['smtp_error' => "Kết nối SMTP thất bại: {$errstr} ({$errno})"]), $cfg, 'smtp_failed'); return false; }
        stream_set_timeout($fp, $timeout);
        try {
            self::expect($fp, [220]); self::command($fp, 'EHLO localhost', [250]);
            if ($encryption === 'tls') { self::command($fp, 'STARTTLS', [220]); if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('Không thể bật STARTTLS. Hãy kiểm tra extension openssl trong XAMPP/PHP.'); self::command($fp, 'EHLO localhost', [250]); }
            self::command($fp, 'AUTH LOGIN', [334]); self::command($fp, base64_encode($username), [334]); self::command($fp, base64_encode($password), [235]);
            $fromAddress = (string) ($payload['from']['address'] ?? $username); $toAddress = self::extractEmail((string) ($payload['to'] ?? ''));
            self::command($fp, 'MAIL FROM:<' . $fromAddress . '>', [250]); self::command($fp, 'RCPT TO:<' . $toAddress . '>', [250, 251]); self::command($fp, 'DATA', [354]);
            $raw = self::buildMimeMessage($payload); fwrite($fp, $raw . "\r\n.\r\n"); self::expect($fp, [250]); self::command($fp, 'QUIT', [221]); fclose($fp); return true;
        } catch (Throwable $e) { fclose($fp); self::logMail(array_merge($payload, ['smtp_error' => $e->getMessage()]), $cfg, 'smtp_failed'); return false; }
    }
    private static function buildMimeMessage(array $payload): string
    {
        $to = self::extractEmail((string) ($payload['to'] ?? '')); $subject = (string) ($payload['subject'] ?? '');
        $body = str_replace(["\r\n", "\r"], "\n", (string) ($payload['body'] ?? '')); $body = preg_replace("/(?m)^\./", '..', $body ?? ''); $body = str_replace("\n", "\r\n", $body);
        $fromAddress = (string) ($payload['from']['address'] ?? 'no-reply@example.com'); $fromName = (string) ($payload['from']['name'] ?? 'ATINO STYLE'); $contentType = !empty($payload['is_html']) ? 'text/html' : 'text/plain';
        $headers = ['From: ' . self::formatAddress($fromAddress, $fromName), 'To: <' . $to . '>', 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=', 'MIME-Version: 1.0', 'Content-Type: ' . $contentType . '; charset=UTF-8', 'Content-Transfer-Encoding: 8bit', 'Date: ' . date(DATE_RFC2822)];
        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }
    private static function command($fp, string $command, array $expectedCodes): string { fwrite($fp, $command . "\r\n"); return self::expect($fp, $expectedCodes); }
    private static function expect($fp, array $expectedCodes): string
    {
        $response = '';
        while (!feof($fp)) { $line = fgets($fp, 515); if ($line === false) break; $response .= $line; if (preg_match('/^(\d{3})([\s-])/', $line, $m) && $m[2] === ' ') { $code = (int) $m[1]; if (!in_array($code, $expectedCodes, true)) throw new RuntimeException('SMTP lỗi: ' . trim($response)); return $response; } }
        throw new RuntimeException('SMTP không phản hồi đúng.');
    }
    private static function extractEmail(string $value): string { if (preg_match('/<([^>]+)>/', $value, $m)) return trim($m[1]); return trim($value); }
    private static function formatAddress(string $email, string $name): string { $safeName = '=?UTF-8?B?' . base64_encode($name) . '?='; return $safeName . ' <' . $email . '>'; }
    private static function logMail(array $payload, array $cfg, string $prefix = 'mail'): void { $dir = $cfg['log_dir'] ?? (__DIR__ . '/../storage/mail_logs'); if (!is_dir($dir)) mkdir($dir, 0777, true); $file = $dir . '/' . $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.json'; file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); }
}
