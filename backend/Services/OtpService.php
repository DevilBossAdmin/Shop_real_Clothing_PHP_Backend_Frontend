<?php
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/App.php';
require_once __DIR__ . '/Mailer.php';
final class OtpService
{
    public static function issue(string $purpose, string $email, array $meta = [], ?int $userId = null): array
    {
        $cfg = App::config('otp'); $length = (int) ($cfg['length'] ?? 6); $ttlMinutes = (int) ($cfg['ttl_minutes'] ?? 10);
        $code = self::generateNumericCode($length); $expiresAt = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));
        $db = Database::connection(); $db->prepare('DELETE FROM email_otps WHERE email = ? AND purpose = ?')->execute([$email, $purpose]);
        $db->prepare('INSERT INTO email_otps (purpose, email, user_id, otp_code, payload_json, attempts, expires_at, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, NOW())')->execute([$purpose, $email, $userId, $code, json_encode($meta, JSON_UNESCAPED_UNICODE), $expiresAt]);
        $sent = self::sendOtpMail($purpose, $email, $code, $ttlMinutes, $meta);
        return ['email' => $email, 'code' => $code, 'expires_at' => $expiresAt, 'ttl_minutes' => $ttlMinutes, 'sent' => $sent];
    }
    public static function verify(string $purpose, string $email, string $otp): ?array
    {
        $otp = trim($otp); $db = Database::connection(); $st = $db->prepare('SELECT * FROM email_otps WHERE purpose = ? AND email = ? ORDER BY id DESC LIMIT 1'); $st->execute([$purpose, $email]); $row = $st->fetch(PDO::FETCH_ASSOC); if (!$row) return null;
        if (!empty($row['verified_at']) || strtotime((string) $row['expires_at']) < time()) return null; $maxAttempts = (int) (App::config('otp.max_attempts', 5)); if ((int) ($row['attempts'] ?? 0) >= $maxAttempts) return null;
        if (!hash_equals((string) $row['otp_code'], $otp)) { $db->prepare('UPDATE email_otps SET attempts = attempts + 1 WHERE id = ?')->execute([(int) $row['id']]); return null; }
        $db->prepare('UPDATE email_otps SET verified_at = NOW() WHERE id = ?')->execute([(int) $row['id']]); $row['payload'] = json_decode((string) ($row['payload_json'] ?? '{}'), true) ?: []; return $row;
    }
    public static function resend(string $purpose, string $email, array $meta = [], ?int $userId = null): array { return self::issue($purpose, $email, $meta, $userId); }
    public static function consume(string $purpose, string $email): void { Database::connection()->prepare('DELETE FROM email_otps WHERE purpose = ? AND email = ?')->execute([$purpose, $email]); }
    private static function sendOtpMail(string $purpose, string $email, string $code, int $ttlMinutes, array $meta = []): bool
    {
        $shopName = (string) App::config('site.name', 'ATINO STYLE'); $shopHotline = (string) App::config('site.hotline', '083 267 2005'); $name = trim((string) ($meta['name'] ?? '')) ?: 'bạn';
        if ($purpose === 'register') { $subject = 'Xác thực tài khoản - ' . $shopName; $title = 'Xác thực tài khoản'; $lead = 'Cảm ơn bạn đã đăng ký tài khoản tại <strong>' . htmlspecialchars($shopName, ENT_QUOTES, 'UTF-8') . '</strong>. Vui lòng dùng mã OTP bên dưới để hoàn tất đăng ký.'; $note = 'Nếu bạn không thực hiện yêu cầu đăng ký này, vui lòng bỏ qua email.'; $accent = '#2563eb'; $boxBg = '#eff6ff'; }
        else { $subject = 'OTP đặt lại mật khẩu - ' . $shopName; $title = 'OTP đặt lại mật khẩu'; $lead = 'Hệ thống vừa nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại <strong>' . htmlspecialchars($shopName, ENT_QUOTES, 'UTF-8') . '</strong>.'; $note = 'Nếu bạn không thực hiện yêu cầu này, vui lòng đổi mật khẩu sau khi đăng nhập và liên hệ bộ phận hỗ trợ.'; $accent = '#ea580c'; $boxBg = '#fff7ed'; }
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); $safeShop = htmlspecialchars($shopName, ENT_QUOTES, 'UTF-8'); $safeHotline = htmlspecialchars($shopHotline, ENT_QUOTES, 'UTF-8');
        $html = <<<HTML
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>{$title}</title></head><body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;"><div style="max-width:640px;margin:30px auto;padding:0 16px;"><div style="background:#111827;color:#ffffff;padding:22px 28px;border-radius:16px 16px 0 0;"><h1 style="margin:0;font-size:24px;">{$safeShop}</h1><p style="margin:8px 0 0;font-size:14px;color:#d1d5db;">Email xác minh bảo mật</p></div><div style="background:#ffffff;padding:32px 28px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 16px 16px;"><p style="margin:0 0 16px;font-size:16px;">Xin chào <strong>{$safeName}</strong>,</p><p style="margin:0 0 16px;line-height:1.7;">{$lead}</p><div style="margin:24px 0;padding:22px;background:{$boxBg};border:1px dashed {$accent};border-radius:14px;text-align:center;"><div style="font-size:13px;color:#6b7280;letter-spacing:.4px;">MÃ OTP CỦA BẠN</div><div style="margin-top:10px;font-size:34px;font-weight:700;letter-spacing:8px;color:#111827;">{$safeCode}</div><div style="margin-top:10px;font-size:14px;color:#dc2626;">Mã có hiệu lực trong <strong>{$ttlMinutes} phút</strong></div></div><div style="margin:18px 0 0;padding:16px;background:#f9fafb;border-left:4px solid {$accent};border-radius:10px;"><p style="margin:0;font-size:14px;line-height:1.7;color:#374151;">Vì lý do bảo mật, vui lòng không chia sẻ mã OTP này cho bất kỳ ai.</p></div><p style="margin:24px 0 0;line-height:1.7;font-size:14px;color:#4b5563;">{$note}</p><hr style="margin:28px 0;border:none;border-top:1px solid #e5e7eb;"><p style="margin:0 0 8px;font-size:13px;color:#6b7280;">Đây là email tự động từ hệ thống <strong>{$safeShop}</strong>, vui lòng không trả lời trực tiếp email này.</p><p style="margin:0;font-size:13px;color:#6b7280;">Nếu cần hỗ trợ, vui lòng liên hệ hotline <strong>{$safeHotline}</strong>.</p></div></div></body></html>
HTML;
        return Mailer::send(['to' => $email, 'subject' => $subject, 'body' => $html, 'is_html' => true, 'meta' => ['purpose' => $purpose]]);
    }
    private static function generateNumericCode(int $length): string { $code = ''; for ($i = 0; $i < $length; $i++) $code .= (string) random_int(0, 9); return $code; }
}
