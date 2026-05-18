<?php
require_once __DIR__ . '/../db.php';

class Shipment
{
  public static function byOrderId(int $orderId): ?array
  {
    $st = db()->prepare("SELECT * FROM shipments WHERE order_id=? LIMIT 1");
    $st->execute([$orderId]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
  }

  public static function upsert(int $orderId, string $carrier, ?string $trackingCode, string $status='created', ?string $json=null): void
  {
    $exists = self::byOrderId($orderId);
    if ($exists) {
      $st = db()->prepare("UPDATE shipments SET carrier=?, tracking_code=?, status=?, last_tracking_json=?, updated_at=NOW() WHERE order_id=?");
      $st->execute([$carrier, $trackingCode, $status, $json, $orderId]);
      return;
    }
    $st = db()->prepare("INSERT INTO shipments (order_id, carrier, tracking_code, status, last_tracking_json, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())");
    $st->execute([$orderId, $carrier, $trackingCode, $status, $json]);
  }

  public static function adminCount(?string $q=null): int
  {
    $where="1=1"; $p=[];
    if ($q) { $where.=" AND (CAST(o.id AS CHAR) LIKE ? OR s.tracking_code LIKE ? OR o.customer_phone LIKE ? OR o.customer_name LIKE ?)"; $p[]="%$q%"; $p[]="%$q%"; $p[]="%$q%"; $p[]="%$q%"; }
    $st = db()->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN shipments s ON s.order_id=o.id WHERE $where");
    $st->execute($p);
    return (int)$st->fetchColumn();
  }

  public static function adminList(int $limit, int $offset, ?string $q=null): array
  {
    $where="1=1"; $p=[];
    if ($q) { $where.=" AND (CAST(o.id AS CHAR) LIKE ? OR s.tracking_code LIKE ? OR o.customer_phone LIKE ? OR o.customer_name LIKE ?)"; $p[]="%$q%"; $p[]="%$q%"; $p[]="%$q%"; $p[]="%$q%"; }

    $sql = "
      SELECT
        o.id AS order_id, o.customer_name, o.customer_phone, o.customer_address, o.total, o.status AS order_status,
        o.shipping_fee, o.shipping_status, o.shipping_carrier, o.tracking_code AS shipping_tracking_code,
        s.carrier AS s_carrier, s.tracking_code AS s_tracking_code, s.status AS s_status, s.updated_at AS s_updated_at
      FROM orders o
      LEFT JOIN shipments s ON s.order_id = o.id
      WHERE $where
      ORDER BY o.id DESC
      LIMIT ? OFFSET ?
    ";

    $st = db()->prepare($sql);
    $i=1;
    foreach ($p as $v) $st->bindValue($i++, $v);
    $st->bindValue($i++, $limit, PDO::PARAM_INT);
    $st->bindValue($i++, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function setManualTracking(int $orderId, string $carrier, string $code): void
  {
    $carrier = in_array($carrier, ['manual','ghn','ghtk','jt','vnpost'], true) ? $carrier : 'manual';
    self::upsert($orderId, $carrier, $code, 'created', null);
    $st = db()->prepare("UPDATE orders SET shipping_carrier=?, tracking_code=?, shipping_status='picking', updated_at=NOW() WHERE id=?");
    $st->execute([$carrier, $code, $orderId]);
  }

  public static function updateOrderShippingFee(int $orderId, int $fee): void
  {
    $st = db()->prepare("UPDATE orders SET shipping_fee=?, updated_at=NOW() WHERE id=?");
    $st->execute([$fee, $orderId]);
  }
}
