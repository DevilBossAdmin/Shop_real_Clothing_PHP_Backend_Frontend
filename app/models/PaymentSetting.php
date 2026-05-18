<?php
require_once __DIR__ . '/../db.php';

class PaymentSetting
{
    public static function get(): array
    {
        $db = db();

        $keys = ['BANK_ACCOUNT', 'BANK_NAME', 'BANK_OWNER', 'QR_IMAGE'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));

        $st = $db->prepare("
            SELECT `key`, `value`
            FROM settings
            WHERE `key` IN ($placeholders)
        ");
        $st->execute($keys);

        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'bank_account' => '',
            'bank_name'    => '',
            'bank_owner'   => '',
            'qr_image'     => '',
        ];

        foreach ($rows as $r) {
            if ($r['key'] === 'BANK_ACCOUNT') $data['bank_account'] = $r['value'] ?? '';
            if ($r['key'] === 'BANK_NAME')    $data['bank_name']    = $r['value'] ?? '';
            if ($r['key'] === 'BANK_OWNER')   $data['bank_owner']   = $r['value'] ?? '';
            if ($r['key'] === 'QR_IMAGE')     $data['qr_image']     = $r['value'] ?? '';
        }

        return $data;
    }

    public static function save(array $data): void
    {
        $db = db();

        $map = [
            'BANK_ACCOUNT' => trim($data['bank_account'] ?? ''),
            'BANK_NAME'    => trim($data['bank_name'] ?? ''),
            'BANK_OWNER'   => trim($data['bank_owner'] ?? ''),
            'QR_IMAGE'     => trim($data['qr_image'] ?? ''),
        ];

        $st = $db->prepare("
            INSERT INTO settings (`key`, `value`, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
              `value` = VALUES(`value`),
              updated_at = NOW()
        ");

        foreach ($map as $key => $value) {
            $st->execute([$key, $value]);
        }
    }
}