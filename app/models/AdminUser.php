<?php
require_once __DIR__ . '/../db.php';

class AdminUser
{
  public static function findByUsername(string $username): ?array {
    $st = db()->prepare("SELECT * FROM admin_users WHERE username=? LIMIT 1");
    $st->execute([$username]);
    $r = $st->fetch();
    return $r ?: null;
  }

  public static function byId(int $id): ?array {
    $st = db()->prepare("SELECT * FROM admin_users WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ?: null;
  }

 public static function verify($username, $password) {
  $db = db();
  $st = $db->prepare("SELECT * FROM admin_users WHERE username=? LIMIT 1");
  $st->execute([$username]);
  $u = $st->fetch(PDO::FETCH_ASSOC);

  if (!$u) return false;

  // nếu có cột is_active thì chặn tài khoản bị khóa
  if (isset($u['is_active']) && (int)$u['is_active'] !== 1) {
    return false;
  }

  // đúng cột lưu hash
  if (!password_verify($password, $u['password_hash'])) {
    return false;
  }

  return $u;
}

  public static function count(?string $q=null): int {
    $where="1=1"; $p=[];
    if ($q) { $where.=" AND username LIKE ?"; $p[]="%$q%"; }
    $st = db()->prepare("SELECT COUNT(*) FROM admin_users WHERE $where");
    $st->execute($p);
    return (int)$st->fetchColumn();
  }

  public static function list(int $limit, int $offset, ?string $q=null): array {
    $where="1=1"; $p=[];
    if ($q) { $where.=" AND username LIKE ?"; $p[]="%$q%"; }
    $sql = "SELECT id, username, role, is_active, created_at, updated_at
            FROM admin_users WHERE $where ORDER BY id DESC LIMIT ? OFFSET ?";
    $st = db()->prepare($sql);
    $i=1;
    foreach ($p as $v) $st->bindValue($i++, $v);
    $st->bindValue($i++, $limit, PDO::PARAM_INT);
    $st->bindValue($i++, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }

  public static function create(string $username, string $password, string $role, int $isActive=1): int {
    $role = in_array($role, ['superadmin','sales','warehouse'], true) ? $role : 'sales';
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $st = db()->prepare("INSERT INTO admin_users (username, password_hash, role, is_active) VALUES (?,?,?,?)");
    $st->execute([$username, $hash, $role, $isActive ? 1 : 0]);
    return (int)db()->lastInsertId();
  }

  public static function update(int $id, array $d): void {
    $role = in_array(($d['role'] ?? ''), ['superadmin','sales','warehouse'], true) ? $d['role'] : 'sales';
    $isActive = !empty($d['is_active']) ? 1 : 0;

    $st = db()->prepare("UPDATE admin_users SET role=?, is_active=? WHERE id=?");
    $st->execute([$role, $isActive, $id]);
  }

  public static function setPassword(int $id, string $newPassword): void {
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $st = db()->prepare("UPDATE admin_users SET password_hash=? WHERE id=?");
    $st->execute([$hash, $id]);
  }
}