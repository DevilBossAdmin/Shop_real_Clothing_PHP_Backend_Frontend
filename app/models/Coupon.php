<?php
require_once __DIR__ . '/../db.php';

class Coupon
{
  public static function normalizeCode(string $code): string {
    $code = trim($code);
    $code = preg_replace('/\s+/', '', $code);
    return strtoupper($code);
  }

  public static function findActiveByCode(string $code): ?array
{
  $code = self::normalizeCode($code);
  $st = db()->prepare("SELECT * FROM coupons WHERE code=? LIMIT 1");
  $st->execute([$code]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) return null;

  // dùng is_active thay vì status
  if (isset($row['is_active']) && (int)$row['is_active'] !== 1) return null;

  $now = date('Y-m-d H:i:s');

  // dùng start_date/end_date thay vì starts_at/ends_at
  if (!empty($row['start_date']) && $row['start_date'] > $now) return null;
  if (!empty($row['end_date']) && $row['end_date'] < $now) return null;

  // limit
  if (!empty($row['usage_limit']) && (int)$row['used_count'] >= (int)$row['usage_limit']) return null;

  return $row;
}

  public static function byCode(string $code): ?array {
  $code = trim($code);
  if ($code === '') return null;

  $st = db()->prepare("SELECT * FROM coupons WHERE code = ? LIMIT 1");
  $st->execute([$code]);
  $row = $st->fetch();
  return $row ?: null;
}

  public static function calcDiscount(array $coupon, int $subtotal): int
  {
    $subtotal = max(0, (int)$subtotal);
    $min = (int)($coupon['min_order_total'] ?? 0);
    if ($subtotal < $min) return 0;

    $type = $coupon['type'] ?? 'fixed';
    $value = (int)($coupon['value'] ?? 0);
    $discount = 0;

    if ($type === 'percent') {
      $discount = (int) floor($subtotal * $value / 100);
      $max = $coupon['max_discount'];
      if ($max !== null && $max !== '') $discount = min($discount, (int)$max);
    } else {
      $discount = $value;
    }

    return max(0, min($discount, $subtotal));
  }

  // Tăng used_count an toàn (atomic)
  public static function consume(string $code): bool
{
  $code = self::normalizeCode($code);
  $st = db()->prepare("
    UPDATE coupons
    SET used_count = used_count + 1
    WHERE code=?
      AND is_active=1
      AND (usage_limit IS NULL OR used_count < usage_limit)
      AND (start_date IS NULL OR start_date <= NOW())
      AND (end_date IS NULL OR end_date >= NOW())
  ");
  $st->execute([$code]);
  return $st->rowCount() === 1;
}
}