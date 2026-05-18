<?php
// app/models/User.php

require_once __DIR__ . '/../db.php';

class User
{
  // ================== BASIC ==================

  public static function create(string $name, string $email, string $password): array
  {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // ⚠️ Cột mật khẩu trong DB của bạn đang dùng: password_hash
    $st = db()->prepare("INSERT INTO users (name, email, password_hash) VALUES (?,?,?)");
    $st->execute([$name, $email, $hash]);

    return self::findByEmail($email) ?: ['id'=>db()->lastInsertId(), 'name'=>$name, 'email'=>$email];
  }

  public static function findByEmail(string $email): ?array
  {
    $st = db()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $r = $st->fetch();
    return $r ?: null;
  }

  public static function byId(int $id): ?array
  {
    $st = db()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ?: null;
  }

  // ✅ login: chặn user bị khóa
  public static function verify(string $email, string $password): ?array
  {
    $u = self::findByEmail($email);
    if (!$u) return null;

    if (!empty($u['is_locked'])) return null;

    $hash = (string)($u['password_hash'] ?? '');
    if ($hash === '' || !password_verify($password, $hash)) return null;

    return $u;
  }

  // ================== FORGOT PASSWORD ==================

  // Tạo token reset (hết hạn 1 giờ)
  public static function createPasswordReset(int $userId): string
  {
    $token = bin2hex(random_bytes(32)); // 64 ký tự hex
    $expires = date('Y-m-d H:i:s', time() + 3600);

    // Xóa token cũ của user (gọn DB + tránh nhiều link)
    $stDel = db()->prepare("DELETE FROM password_resets WHERE user_id=?");
    $stDel->execute([$userId]);

    $st = db()->prepare("
      INSERT INTO password_resets (user_id, token, expires_at)
      VALUES (?, ?, ?)
    ");
    $st->execute([$userId, $token, $expires]);

    return $token;
  }

  // Tìm token reset và check hết hạn
  public static function findResetByToken(string $token): ?array
  {
    $token = trim($token);
    if ($token === '') return null;

    $st = db()->prepare("
      SELECT pr.*
      FROM password_resets pr
      WHERE pr.token = ?
      LIMIT 1
    ");
    $st->execute([$token]);
    $row = $st->fetch();
    if (!$row) return null;

    // hết hạn
    if (strtotime((string)$row['expires_at']) < time()) return null;

    return $row;
  }

  // Cập nhật mật khẩu mới
  public static function updatePassword(int $userId, string $newPassword): void
  {
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $st = db()->prepare("UPDATE users SET password_hash=? WHERE id=?");
    $st->execute([$hash, $userId]);
  }

  // Xóa token sau khi dùng
  public static function deleteResetToken(string $token): void
  {
    $st = db()->prepare("DELETE FROM password_resets WHERE token=?");
    $st->execute([$token]);
  }

  // ================== ADMIN: CUSTOMERS ==================

  public static function adminCount(?string $q = null): int
  {
    $where = "1=1";
    $p = [];

    if ($q) {
      $where .= " AND (name LIKE ? OR email LIKE ?)";
      $p[] = "%$q%";
      $p[] = "%$q%";
    }

    $st = db()->prepare("SELECT COUNT(*) FROM users WHERE $where");
    $st->execute($p);
    return (int)$st->fetchColumn();
  }

  public static function adminListWithStats(int $limit, int $offset, ?string $q = null): array
  {
    $where = "1=1";
    $p = [];

    if ($q) {
      $where .= " AND (u.name LIKE ? OR u.email LIKE ?)";
      $p[] = "%$q%";
      $p[] = "%$q%";
    }

    $sql = "
      SELECT
        u.*,
        (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders_count,
        (SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.user_id = u.id AND o.status <> 'cancelled') AS total_spent,
        (SELECT MAX(o.created_at) FROM orders o WHERE o.user_id = u.id) AS last_order_at
      FROM users u
      WHERE $where
      ORDER BY u.id DESC
      LIMIT ? OFFSET ?
    ";

    $st = db()->prepare($sql);
    $i = 1;

    foreach ($p as $val) {
      $st->bindValue($i++, $val);
    }

    $st->bindValue($i++, $limit, PDO::PARAM_INT);
    $st->bindValue($i++, $offset, PDO::PARAM_INT);

    $st->execute();
    return $st->fetchAll();
  }

  public static function toggleLock(int $userId, bool $lock): void
  {
    $st = db()->prepare("UPDATE users SET is_locked=? WHERE id=?");
    $st->execute([$lock ? 1 : 0, $userId]);
  }

public static function adminListCustomers($limit = 200, $q = null) {
  $db = db();
  $sql = "SELECT * FROM users WHERE 1=1";
  $params = [];

  if ($q !== null && $q !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%" . $q . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
  }

  $sql .= " ORDER BY id DESC LIMIT " . (int)$limit;

  $st = $db->prepare($sql);
  $st->execute($params);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

public static function findById($id) {
  $db = db();
  $st = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
  $st->execute([(int)$id]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  return $u ? $u : null;
}

  public static function stats(int $userId): array
  {
    $st = db()->prepare("
      SELECT
        (SELECT COUNT(*) FROM orders o WHERE o.user_id=?) AS orders_count,
        (SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.user_id=? AND o.status <> 'cancelled') AS total_spent,
        (SELECT MAX(o.created_at) FROM orders o WHERE o.user_id=?) AS last_order_at
    ");
    $st->execute([$userId, $userId, $userId]);
    $r = $st->fetch();

    return $r ?: ['orders_count'=>0,'total_spent'=>0,'last_order_at'=>null];
  }
}