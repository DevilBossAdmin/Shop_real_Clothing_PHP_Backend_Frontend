<?php
final class App
{
    private static ?array $config = null;

    public static function config(?string $key = null, $default = null)
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/app.php';
            date_default_timezone_set(self::$config['app']['timezone'] ?? 'Asia/Ho_Chi_Minh');
        }
        if ($key === null) return self::$config;
        $segments = explode('.', $key);
        $value = self::$config;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) return $default;
            $value = $value[$segment];
        }
        return $value;
    }
}
