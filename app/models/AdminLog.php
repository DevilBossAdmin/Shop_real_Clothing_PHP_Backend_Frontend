<?php
require_once __DIR__ . '/../db.php';

class AdminLog
{
  public static function add(?int $adminId, string $action, ?string $entity=null, ?int $entityId=null, ?array $meta=null): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $json = $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null;

    $st = db()->prepare("INSERT INTO admin_logs (admin_id, action, entity, entity_id, meta_json, ip, user_agent)
                         VALUES (?,?,?,?,?,?,?)");
    $st->execute([$adminId, $action, $entity, $entityId, $json, $ip, $ua]);
  }

  public static function count(?string $q=null): int {
    $where="1=1"; $p=[];
    if ($q) {
      $where.=" AND (action LIKE ? OR entity LIKE ? OR ip LIKE ?)";
      $p[]="%$q%"; $p[]="%$q%"; $p[]="%$q%";
    }
    $st = db()->prepare("SELECT COUNT(*) FROM admin_logs WHERE $where");
    $st->execute($p);
    return (int)$st->fetchColumn();
  }

  public static function list(int $limit, int $offset, ?string $q=null): array {
    $where="1=1"; $p=[];
    if ($q) {
      $where.=" AND (action LIKE ? OR entity LIKE ? OR ip LIKE ?)";
      $p[]="%$q%"; $p[]="%$q%"; $p[]="%$q%";
    }
    $sql = "
      SELECT l.*, a.username
      FROM admin_logs l
      LEFT JOIN admin_users a ON a.id=l.admin_id
      WHERE $where
      ORDER BY l.id DESC
      LIMIT ? OFFSET ?
    ";
    $st = db()->prepare($sql);
    $i=1;
    foreach ($p as $v) $st->bindValue($i++, $v);
    $st->bindValue($i++, $limit, PDO::PARAM_INT);
    $st->bindValue($i++, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }
}