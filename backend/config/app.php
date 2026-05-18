<?php
require_once __DIR__ . '/../Support/Env.php';
Env::load();

return [
    'app' => [
        'name' => Env::get('APP_NAME', 'ATINO-STYLE PHP SHOP (REAL DEMO)'),
        'env' => Env::get('APP_ENV', 'local'),
        'debug' => Env::bool('APP_DEBUG', true),
        'url' => Env::get('APP_URL', 'http://localhost/shop_real/public'),
        'timezone' => Env::get('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),
    ],
    'db' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'name' => Env::get('DB_NAME', 'atino_real'),
        'user' => Env::get('DB_USER', 'root'),
        'pass' => Env::get('DB_PASS', ''),
        'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    ],
    'site' => [
        'name' => Env::get('SHOP_NAME', Env::get('APP_NAME', 'ATINO-STYLE PHP SHOP (REAL DEMO)')),
        'hotline' => Env::get('SHOP_HOTLINE', '083 267 2005'),
        'email' => Env::get('SHOP_EMAIL', 'support@example.com'),
        'address' => Env::get('SHOP_ADDRESS', 'Ha Noi'),
    ],
    'admin' => [
        'username' => Env::get('ADMIN_USERNAME', 'admin'),
        'password' => Env::get('ADMIN_PASSWORD', 'admin123'),
    ],
    'uploads' => [
        'dir' => dirname(__DIR__, 2) . '/' . trim((string) Env::get('UPLOAD_DIR', 'public/uploads'), '/'),
        'url' => Env::get('UPLOAD_URL', '/uploads'),
    ],
    'vnpay' => [
        'tmn_code' => Env::get('VNPAY_TMN_CODE', 'YOUR_TMN_CODE'),
        'hash_secret' => Env::get('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET'),
        'pay_url' => Env::get('VNPAY_PAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => Env::get('VNPAY_RETURN_URL', 'http://localhost/shop_real/public/vnpay/return'),
        'ipn_url' => Env::get('VNPAY_IPN_URL', 'http://localhost/shop_real/public/vnpay/ipn'),
        'bank_code_card' => Env::get('VNPAY_BANK_CODE_CARD', 'INTCARD'),
    ],
    'mail' => [
        'driver' => Env::get('MAIL_DRIVER', 'log'),
        'from_address' => Env::get('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'from_name' => Env::get('MAIL_FROM_NAME', 'ATINO STYLE'),
        'fallback_to' => Env::get('MAIL_TO_FALLBACK', ''),
        'smtp' => [
            'host' => Env::get('SMTP_HOST', 'smtp.gmail.com'),
            'port' => (int) Env::get('SMTP_PORT', '587'),
            'username' => Env::get('SMTP_USERNAME', ''),
            'password' => Env::get('SMTP_PASSWORD', ''),
            'encryption' => Env::get('SMTP_ENCRYPTION', 'tls'),
            'timeout' => (int) Env::get('SMTP_TIMEOUT', '20'),
        ],
        'log_dir' => dirname(__DIR__) . '/storage/mail_logs',
    ],
    'otp' => [
        'length' => max(4, (int) Env::get('OTP_LENGTH', '6')),
        'ttl_minutes' => max(1, (int) Env::get('OTP_TTL_MINUTES', '10')),
        'max_attempts' => max(1, (int) Env::get('OTP_MAX_ATTEMPTS', '5')),
    ],
];
