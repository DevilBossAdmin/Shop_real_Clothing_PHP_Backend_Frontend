<?php
require_once __DIR__ . '/../db.php';

class Shipping
{
  public static function settings(): array
  {
    $st = db()->query("SELECT * FROM shipping_settings WHERE id=1 LIMIT 1");
    $r = $st->fetch(PDO::FETCH_ASSOC);
    if ($r) return $r;

    db()->exec("INSERT INTO shipping_settings (id, default_fee, free_ship_min, ghn_enabled, ghn_base_url, ghn_token, ghn_shop_id, ghtk_enabled, ghtk_base_url, ghtk_token, created_at, updated_at) VALUES (1,0,0,0,NULL,NULL,NULL,0,NULL,NULL,NOW(),NOW())");
    $st = db()->query("SELECT * FROM shipping_settings WHERE id=1 LIMIT 1");
    return $st->fetch(PDO::FETCH_ASSOC) ?: [
      'id'=>1,'default_fee'=>0,'free_ship_min'=>0,
      'ghn_enabled'=>0,'ghn_base_url'=>null,'ghn_token'=>null,'ghn_shop_id'=>null,
      'ghtk_enabled'=>0,'ghtk_base_url'=>null,'ghtk_token'=>null
    ];
  }

  public static function updateSettings(array $d): void
  {
    self::settings();
    $st = db()->prepare("
      UPDATE shipping_settings SET
        default_fee=?,
        free_ship_min=?,
        ghn_enabled=?, ghn_base_url=?, ghn_token=?, ghn_shop_id=?,
        ghtk_enabled=?, ghtk_base_url=?, ghtk_token=?,
        updated_at=NOW()
      WHERE id=1
    ");
    $st->execute([
      (int)($d['default_fee'] ?? 0),
      (int)($d['free_ship_min'] ?? 0),
      (int)($d['ghn_enabled'] ?? 0),
      $d['ghn_base_url'] ?? null,
      $d['ghn_token'] ?? null,
      $d['ghn_shop_id'] ?? null,
      (int)($d['ghtk_enabled'] ?? 0),
      $d['ghtk_base_url'] ?? null,
      $d['ghtk_token'] ?? null,
    ]);
  }

  public static function zoneCount(?string $q=null): int
  {
    $where = "1=1";
    $p=[];
    if ($q) { $where.=" AND name LIKE ?"; $p[]="%$q%"; }
    $st = db()->prepare("SELECT COUNT(*) FROM shipping_zones WHERE $where");
    $st->execute($p);
    return (int)$st->fetchColumn();
  }

  public static function zoneList(int $limit, int $offset, ?string $q=null): array
  {
    $where = "1=1";
    $p=[];
    if ($q) { $where.=" AND name LIKE ?"; $p[]="%$q%"; }
    $sql = "SELECT * FROM shipping_zones WHERE $where ORDER BY sort_order ASC, id DESC LIMIT ? OFFSET ?";
    $st = db()->prepare($sql);
    $i=1;
    foreach ($p as $v) $st->bindValue($i++, $v);
    $st->bindValue($i++, $limit, PDO::PARAM_INT);
    $st->bindValue($i++, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function zoneById(int $id): ?array
  {
    $st = db()->prepare("SELECT * FROM shipping_zones WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
  }

  public static function zoneCreate(array $d): int
  {
    $st = db()->prepare("INSERT INTO shipping_zones (name, provinces_text, fee, is_active, sort_order, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())");
    $st->execute([
      trim((string)$d['name']),
      trim((string)($d['provinces_text'] ?? '')),
      (int)($d['fee'] ?? 0),
      (int)($d['is_active'] ?? 1),
      (int)($d['sort_order'] ?? 0),
    ]);
    return (int)db()->lastInsertId();
  }

  public static function zoneUpdate(int $id, array $d): void
  {
    $st = db()->prepare("UPDATE shipping_zones SET name=?, provinces_text=?, fee=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?");
    $st->execute([
      trim((string)$d['name']),
      trim((string)($d['provinces_text'] ?? '')),
      (int)($d['fee'] ?? 0),
      (int)($d['is_active'] ?? 1),
      (int)($d['sort_order'] ?? 0),
      $id
    ]);
  }

  public static function zoneDelete(int $id): void
  {
    $st = db()->prepare("DELETE FROM shipping_zones WHERE id=?");
    $st->execute([$id]);
  }

  public static function calcFeeFromAddress(string $address, int $subtotal): int
  {
    $s = self::settings();
    $freeMin = (int)($s['free_ship_min'] ?? 0);
    if ($freeMin > 0 && $subtotal >= $freeMin) return 0;

    $default = (int)($s['default_fee'] ?? 0);
    $zone = self::resolveZoneByAddress($address);
    if ($zone) {
      return (int)($zone['fee'] ?? 0);
    }
    return $default;
  }

  public static function resolveZoneByAddress(string $address): ?array
  {
    $addr = mb_strtolower($address);
    $zones = db()->query("SELECT * FROM shipping_zones WHERE is_active=1 ORDER BY sort_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($zones as $z) {
      $lines = preg_split("/\r\n|\n|\r/", (string)($z['provinces_text'] ?? ''));
      foreach ($lines as $prov) {
        $prov = trim($prov);
        if ($prov === '') continue;
        if (mb_strpos($addr, mb_strtolower($prov)) !== false) {
          return $z;
        }
      }
    }
    return null;
  }

}
