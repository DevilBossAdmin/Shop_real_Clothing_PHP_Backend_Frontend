<?php
require_once __DIR__ . '/../db.php';

class SystemSetting
{
  public static function get(): array {
    $st = db()->query("SELECT * FROM system_settings WHERE id=1 LIMIT 1");
    $r = $st->fetch();
    return $r ?: ['id'=>1,'shop_name'=>null,'hotline'=>null,'address'=>null,'email'=>null,'logo_path'=>null];
  }

  public static function update(array $d): void {
    $st = db()->prepare("
      UPDATE system_settings SET
        shop_name=?, hotline=?, address=?, email=?, logo_path=?
      WHERE id=1
    ");
    $st->execute([
      $d['shop_name'] ?? null,
      $d['hotline'] ?? null,
      $d['address'] ?? null,
      $d['email'] ?? null,
      $d['logo_path'] ?? null,
    ]);
  }
}