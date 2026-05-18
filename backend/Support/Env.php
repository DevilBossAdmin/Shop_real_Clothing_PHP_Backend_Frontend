<?php
final class Env
{
    private static array $data = [];
    private static bool $loaded = false;

    public static function load(?string $file = null): void
    {
        if (self::$loaded) return;
        $file ??= __DIR__ . '/../.env';
        if (!is_file($file)) {
            self::$loaded = true;
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            $pos = strpos($line, '=');
            if ($pos === false) continue;
            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));
            $val = trim($val, "\"'");
            self::$data[$key] = $val;
            $_ENV[$key] = $val;
            putenv($key . '=' . $val);
        }
        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        self::load();
        if (array_key_exists($key, self::$data)) return self::$data[$key];
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = strtolower((string) self::get($key, $default ? 'true' : 'false'));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}
