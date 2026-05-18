<?php
require_once __DIR__ . '/../db.php';

class Inventory
{
  // Danh sách biến thể tồn kho
  public static function adminListVariants(int $limit, int $offset, ?string $q = null): array
  {
    $where = "1=1";
    $p = [];

    if ($q) {
      $where .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)";
      $p[] = "%$q%";
      $p[] = "%$q%";
      $p[] = "%$q%";
    }

    $sql = "
      SELECT
        pv.id AS variant_id,
        pv.product_id,
        pv.size,
        pv.color,
        pv.stock,
        p.name AS product_name,
        p.sku AS product_sku,
        p.slug AS product_slug
      FROM product_variants pv
      JOIN products p ON p.id = pv.product_id
      WHERE $where
      ORDER BY p.id DESC, pv.id DESC
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

    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function adminCountVariants(?string $q = null): int
  {
    $where = "1=1";
    $p = [];

    if ($q) {
      $where .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)";
      $p[] = "%$q%";
      $p[] = "%$q%";
      $p[] = "%$q%";
    }

    $sql = "
      SELECT COUNT(*)
      FROM product_variants pv
      JOIN products p ON p.id = pv.product_id
      WHERE $where
    ";

    $st = db()->prepare($sql);
    $st->execute($p);

    return (int)$st->fetchColumn();
  }

  // Tạo phiếu nhập/xuất/điều chỉnh và cập nhật stock
  // Giữ nguyên chữ ký hàm để không vỡ code cũ
  public static function createMovement(
    int $productId,
    ?int $variantId,
    string $type,   // in|out|adjust
    int $qty,
    ?string $note,
    ?string $refType,
    ?int $refId,
    ?string $createdBy
  ): int {
    $type = in_array($type, ['in', 'out', 'adjust'], true) ? $type : 'adjust';
    $qty = (int)$qty;

    if ($variantId === null || $variantId <= 0) {
      throw new Exception("Thiếu variant_id.");
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
      $st = $pdo->prepare("
        SELECT stock
        FROM product_variants
        WHERE id = ? AND product_id = ?
        FOR UPDATE
      ");
      $st->execute([$variantId, $productId]);
      $cur = $st->fetchColumn();

      if ($cur === false) {
        throw new Exception("Variant không tồn tại.");
      }

      $cur = (int)$cur;
      $new = $cur;

      if ($type === 'in') {
        if ($qty <= 0) {
          throw new Exception("Số lượng nhập phải > 0");
        }
        $new = $cur + $qty;
      } elseif ($type === 'out') {
        if ($qty <= 0) {
          throw new Exception("Số lượng xuất phải > 0");
        }
        if ($qty > $cur) {
          throw new Exception("Không đủ tồn kho để xuất.");
        }
        $new = $cur - $qty;
      } else { // adjust
        if ($qty === 0) {
          throw new Exception("Số điều chỉnh không hợp lệ.");
        }
        $new = $cur + $qty;
        if ($new < 0) {
          throw new Exception("Tồn kho sau điều chỉnh < 0.");
        }
      }

      $st2 = $pdo->prepare("
        UPDATE product_variants
        SET stock = ?
        WHERE id = ?
      ");
      $st2->execute([$new, $variantId]);

      // DB hiện tại dùng inventory_logs, không dùng inventory_movements
      // Dùng bộ cột tối thiểu đã thấy trong code/SQL: product_id, type, qty, note, created_at
      $st3 = $pdo->prepare("
        INSERT INTO inventory_logs
          (product_id, type, qty, note, created_at)
        VALUES
          (?, ?, ?, ?, NOW())
      ");
      $st3->execute([
        $productId,
        $type,
        $qty,
        $note
      ]);

      $id = (int)$pdo->lastInsertId();
      $pdo->commit();

      return $id;
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function history(int $limit, int $offset, ?string $q = null, ?string $type = null): array
  {
    $where = ["1=1"];
    $p = [];

    if ($type && in_array($type, ['in', 'out', 'adjust'], true)) {
      $where[] = "l.type = ?";
      $p[] = $type;
    }

    if ($q) {
      $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ? OR l.note LIKE ?)";
      $p[] = "%$q%";
      $p[] = "%$q%";
      $p[] = "%$q%";
      $p[] = "%$q%";
    }

    $sql = "
      SELECT
        l.id,
        l.product_id,
        l.type,
        l.qty,
        l.note,
        l.created_at,
        p.name AS product_name,
        p.sku AS product_sku,
        NULL AS size,
        NULL AS color
      FROM inventory_logs l
      JOIN products p ON p.id = l.product_id
      WHERE " . implode(" AND ", $where) . "
      ORDER BY l.id DESC
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

    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function historyCount(?string $q = null, ?string $type = null): int
  {
    $where = ["1=1"];
    $p = [];

    if ($type && in_array($type, ['in', 'out', 'adjust'], true)) {
      $where[] = "l.type = ?";
      $p[] = $type;
    }

    if ($q) {
      $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ? OR l.note LIKE ?)";
      $p[] = "%$q%";
      $p[] = "%$q%";
      $p[] = "%$q%";
      $p[] = "%$q%";
    }

    $sql = "
      SELECT COUNT(*)
      FROM inventory_logs l
      JOIN products p ON p.id = l.product_id
      WHERE " . implode(" AND ", $where) . "
    ";

    $st = db()->prepare($sql);
    $st->execute($p);

    return (int)$st->fetchColumn();
  }
}