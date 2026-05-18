<?php

require_once __DIR__ . '/../db.php';

class Order
{
  public static function create(
    ?int $userId,
    string $name,
    string $email,
    string $phone,
    string $address,
    ?string $note,
    int $subtotal,
    int $discount,
    ?string $couponCode,
    int $total,
    array $items,
    string $paymentMethod = 'cod',
    string $paymentStatus = 'unpaid',
    ?string $paymentRef = null,
    int $shippingFee = 0,
    ?int $shippingZoneId = null,
    string $shippingCarrier = 'manual'
  ): int {
    $allowedMethods = ['cod', 'bank_transfer', 'qr', 'bank', 'card'];
    if (!in_array($paymentMethod, $allowedMethods, true)) $paymentMethod = 'cod';

    $allowedPayStatuses = ['unpaid', 'paid', 'failed'];
    if (!in_array($paymentStatus, $allowedPayStatuses, true)) $paymentStatus = 'unpaid';

    $subtotal = max(0, (int)$subtotal);
    $discount = max(0, (int)$discount);
    if ($discount > $subtotal) $discount = $subtotal;
    $shippingFee = max(0, (int)$shippingFee);
    if ($total <= 0) {
      $total = max(0, $subtotal - $discount + $shippingFee);
    } else {
      $total = max(0, (int)$total);
    }

    $allowedCarrier = ['manual', 'ghn', 'ghtk', 'jt', 'vnpost'];
    if (!in_array($shippingCarrier, $allowedCarrier, true)) $shippingCarrier = 'manual';

    $pdo = db();
    $pdo->beginTransaction();

    try {
      $st = $pdo->prepare("
        INSERT INTO orders
          (
            user_id, customer_name, customer_email, customer_phone, customer_address, note,
            subtotal, discount, shipping_fee, coupon_code, total,
            status, payment_method, payment_status, payment_ref,
            shipping_carrier, shipping_zone_id, shipping_status,
            created_at, updated_at
          )
        VALUES
          (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
      ");

      $st->execute([
        $userId, $name, $email, $phone, $address, $note,
        $subtotal, $discount, $shippingFee, $couponCode, $total,
        'pending', $paymentMethod, $paymentStatus, $paymentRef,
        $shippingCarrier, $shippingZoneId, null
      ]);

      $orderId = (int)$pdo->lastInsertId();

      $st2 = $pdo->prepare("
        INSERT INTO order_items
          (order_id, product_id, variant_id, price, qty, size, color)
        VALUES
          (?,?,?,?,?,?,?)
      ");

      foreach ($items as $it) {
        $st2->execute([
          $orderId,
          (int)$it['product_id'],
          isset($it['variant_id']) && $it['variant_id'] !== null ? (int)$it['variant_id'] : null,
          (int)$it['price'],
          (int)$it['qty'],
          $it['size'] ?? null,
          $it['color'] ?? null,
        ]);
      }

      self::logStatusChange($pdo, $orderId, null, 'pending', null, null, 'Khách đặt đơn hàng');

      $pdo->commit();
      return $orderId;
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function findById(int $id): ?array
  {
    $st = db()->prepare("SELECT * FROM orders WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  public static function findWithItems(int $id): ?array
  {
    $pdo = db();
    $st = $pdo->prepare("SELECT * FROM orders WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $order = $st->fetch(PDO::FETCH_ASSOC);
    if (!$order) return null;

    $st2 = $pdo->prepare("
      SELECT oi.*, p.name AS product_name, p.slug AS product_slug, p.thumbnail AS product_thumbnail
      FROM order_items oi
      JOIN products p ON p.id = oi.product_id
      WHERE oi.order_id=?
      ORDER BY oi.id ASC
    ");
    $st2->execute([$id]);
    $order['items'] = $st2->fetchAll(PDO::FETCH_ASSOC);

    return $order;
  }

  public static function listByUser(int $userId, int $limit = 50): array
  {
    $st = db()->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT ?");
    $st->bindValue(1, $userId, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function findWithItemsByUser(int $orderId, int $userId): ?array
  {
    $pdo = db();
    $st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");
    $st->execute([$orderId, $userId]);
    $order = $st->fetch(PDO::FETCH_ASSOC);
    if (!$order) return null;

    $st2 = $pdo->prepare("
      SELECT oi.*, p.name AS product_name, p.slug AS product_slug, p.thumbnail AS product_thumbnail
      FROM order_items oi
      JOIN products p ON p.id = oi.product_id
      WHERE oi.order_id=?
      ORDER BY oi.id ASC
    ");
    $st2->execute([$orderId]);
    $order['items'] = $st2->fetchAll(PDO::FETCH_ASSOC);

    return $order;
  }

  public static function updatePayment(int $id, string $method, string $status, ?string $ref = null): void
  {
    $allowedMethods = ['cod', 'bank_transfer', 'qr', 'bank', 'card'];
    if (!in_array($method, $allowedMethods, true)) $method = 'cod';

    $allowedPayStatuses = ['unpaid', 'paid', 'failed'];
    if (!in_array($status, $allowedPayStatuses, true)) $status = 'unpaid';

    $st = db()->prepare("UPDATE orders SET payment_method=?, payment_status=?, payment_ref=?, updated_at=NOW() WHERE id=?");
    $st->execute([$method, $status, $ref, $id]);
  }

  public static function updateStatus(int $id, string $status): void
  {
    self::updateAdminState($id, ['status' => $status], 'system');
  }

  public static function recentByUser(int $userId, int $limit = 10): array
  {
    $st = db()->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT ?");
    $st->bindValue(1, $userId, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function adminCount(?string $status = null): int
  {
    $where = ["1=1"];
    $p = [];
    if ($status) {
      $where[] = "o.status=?";
      $p[] = $status;
    }
    $st = db()->prepare("SELECT COUNT(*) FROM orders o WHERE " . implode(" AND ", $where));
    $st->execute($p);
    return (int)$st->fetchColumn();
  }

  public static function cancelByUser(int $orderId, int $userId): bool
  {
    $st = db()->prepare("UPDATE orders SET status='cancelled', shipping_status='cancelled', updated_at=NOW() WHERE id=? AND user_id=? AND status='pending'");
    $st->execute([$orderId, $userId]);
    return $st->rowCount() === 1;
  }

  public static function adminListByUser(int $userId, int $limit = 50): array
  {
    $st = db()->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT ?");
    $st->bindValue(1, $userId, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function adminSumByUser(int $userId): int
  {
    $st = db()->prepare("SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id=? AND status <> 'cancelled'");
    $st->execute([$userId]);
    return (int)$st->fetchColumn();
  }

  public static function adminList($limit = 100, $status = null, $q = null): array
  {
    $db = db();
    $sql = "
      SELECT
        o.*,
        COALESCE(o.customer_email, u.email) AS resolved_email
      FROM orders o
      LEFT JOIN users u ON u.id = o.user_id
      WHERE 1=1
    ";
    $params = [];

    if ($status !== null && $status !== '') {
      $sql .= " AND o.status = ?";
      $params[] = $status;
    }

    if ($q !== null && $q !== '') {
      $sql .= " AND (
        o.customer_name LIKE ?
        OR o.customer_phone LIKE ?
        OR o.customer_address LIKE ?
        OR o.customer_email LIKE ?
        OR o.tracking_code LIKE ?
        OR CAST(o.id AS CHAR) LIKE ?
      )";
      $like = "%" . $q . "%";
      $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
    }

    $sql .= " ORDER BY o.id DESC LIMIT " . (int)$limit;
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function adminFindWithItems($id): ?array
  {
    $db = db();

    $st = $db->prepare("
      SELECT
        o.*,
        u.name AS user_name,
        u.email AS user_email,
        z.name AS shipping_zone_name
      FROM orders o
      LEFT JOIN users u ON u.id = o.user_id
      LEFT JOIN shipping_zones z ON z.id = o.shipping_zone_id
      WHERE o.id = ?
      LIMIT 1
    ");
    $st->execute([(int)$id]);
    $order = $st->fetch(PDO::FETCH_ASSOC);
    if (!$order) return null;

    $st2 = $db->prepare("
      SELECT oi.*, p.name AS product_name, p.thumbnail AS product_thumbnail
      FROM order_items oi
      LEFT JOIN products p ON p.id = oi.product_id
      WHERE oi.order_id = ?
      ORDER BY oi.id ASC
    ");
    $st2->execute([(int)$id]);
    $order['items'] = $st2->fetchAll(PDO::FETCH_ASSOC);

    $hasLogs = false;
    try {
      $db->query("SELECT 1 FROM order_status_logs LIMIT 1");
      $hasLogs = true;
    } catch (Throwable $e) {
      $hasLogs = false;
    }

    if ($hasLogs) {
      $st3 = $db->prepare("SELECT * FROM order_status_logs WHERE order_id=? ORDER BY id DESC LIMIT 20");
      $st3->execute([(int)$id]);
      $order['status_logs'] = $st3->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $order['status_logs'] = [];
    }

    return $order;
  }

  public static function setStatus($id, $status): void
  {
    self::updateAdminState((int)$id, ['status' => (string)$status], 'admin');
  }

  public static function updateAdminState(int $id, array $data, string $actor = 'admin'): bool
  {
    $db = db();
    $current = self::findById($id);
    if (!$current) {
      return false;
    }

    $status = trim((string)($data['status'] ?? $current['status'] ?? 'pending'));
    $shippingStatus = trim((string)($data['shipping_status'] ?? ($current['shipping_status'] ?? '')));
    $paymentStatus = trim((string)($data['payment_status'] ?? ($current['payment_status'] ?? 'unpaid')));
    $trackingCode = trim((string)($data['tracking_code'] ?? ($current['tracking_code'] ?? '')));
    $shippingCarrier = trim((string)($data['shipping_carrier'] ?? ($current['shipping_carrier'] ?? 'manual')));
    $customerEmail = trim((string)($data['customer_email'] ?? ($current['customer_email'] ?? '')));
    $shippingFee = isset($data['shipping_fee']) ? (int)$data['shipping_fee'] : (int)($current['shipping_fee'] ?? 0);
    $shippingZoneId = isset($data['shipping_zone_id']) && $data['shipping_zone_id'] !== '' ? (int)$data['shipping_zone_id'] : ($current['shipping_zone_id'] ?? null);

    $allowedStatus = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
    $allowedShip = ['', 'picking', 'shipping', 'delivered', 'returned', 'cancelled'];
    $allowedPayment = ['unpaid', 'paid', 'failed'];
    $allowedCarrier = ['manual', 'ghn', 'ghtk', 'jt', 'vnpost'];

    if (!in_array($status, $allowedStatus, true)) $status = 'pending';
    if (!in_array($shippingStatus, $allowedShip, true)) $shippingStatus = '';
    if (!in_array($paymentStatus, $allowedPayment, true)) $paymentStatus = 'unpaid';
    if (!in_array($shippingCarrier, $allowedCarrier, true)) $shippingCarrier = 'manual';

    if ($shippingStatus === 'shipping' && in_array($status, ['pending', 'confirmed', 'picking'], true)) {
      $status = 'shipping';
    } elseif ($shippingStatus === 'delivered') {
      $status = 'completed';
    } elseif ($shippingStatus === 'cancelled') {
      $status = 'cancelled';
    } elseif ($shippingStatus === 'picking' && $status === 'pending') {
      $status = 'confirmed';
    }

    if ($status === 'shipping' && $shippingStatus === '') {
      $shippingStatus = 'shipping';
    } elseif ($status === 'completed' && $shippingStatus === '') {
      $shippingStatus = 'delivered';
    } elseif ($status === 'cancelled' && $shippingStatus === '') {
      $shippingStatus = 'cancelled';
    } elseif ($status === 'confirmed' && $shippingStatus === '') {
      $shippingStatus = 'picking';
    }

    $hasShipmentTable = false;
    try {
      $db->query("SELECT 1 FROM shipments LIMIT 1");
      $hasShipmentTable = true;
    } catch (Throwable $e) {
      $hasShipmentTable = false;
    }

    $db->beginTransaction();
    try {
      $st = $db->prepare("
        UPDATE orders
        SET status = ?,
            shipping_status = ?,
            payment_status = ?,
            tracking_code = ?,
            shipping_carrier = ?,
            customer_email = ?,
            shipping_fee = ?,
            shipping_zone_id = ?,
            updated_at = NOW()
        WHERE id = ?
      ");
      $st->execute([
        $status,
        ($shippingStatus !== '' ? $shippingStatus : null),
        $paymentStatus,
        ($trackingCode !== '' ? $trackingCode : null),
        $shippingCarrier,
        ($customerEmail !== '' ? $customerEmail : null),
        $shippingFee,
        $shippingZoneId ?: null,
        $id
      ]);

      if ($hasShipmentTable) {
        $exists = $db->prepare("SELECT id FROM shipments WHERE order_id=? LIMIT 1");
        $exists->execute([$id]);
        $shipmentId = $exists->fetchColumn();

        if ($shipmentId) {
          $st2 = $db->prepare("
            UPDATE shipments
            SET carrier=?, tracking_code=?, status=?, shipping_zone_id=?, updated_at=NOW()
            WHERE order_id=?
          ");
          $st2->execute([
            $shippingCarrier,
            ($trackingCode !== '' ? $trackingCode : null),
            ($shippingStatus !== '' ? $shippingStatus : 'created'),
            $shippingZoneId ?: null,
            $id
          ]);
        } else {
          $st2 = $db->prepare("
            INSERT INTO shipments(order_id, carrier, tracking_code, status, shipping_zone_id, created_at, updated_at)
            VALUES(?,?,?,?,?,NOW(),NOW())
          ");
          $st2->execute([
            $id,
            $shippingCarrier,
            ($trackingCode !== '' ? $trackingCode : null),
            ($shippingStatus !== '' ? $shippingStatus : 'created'),
            $shippingZoneId ?: null
          ]);
        }
      }

      self::logStatusChange(
        $db,
        $id,
        $current['status'] ?? null,
        $status,
        $current['shipping_status'] ?? null,
        ($shippingStatus !== '' ? $shippingStatus : null),
        trim((string)($data['note'] ?? 'Cập nhật từ quản trị viên')),
        $actor
      );

      $db->commit();
      return true;
    } catch (Throwable $e) {
      $db->rollBack();
      throw $e;
    }
  }

  private static function logStatusChange(PDO $db, int $orderId, ?string $oldStatus, ?string $newStatus, ?string $oldShippingStatus, ?string $newShippingStatus, ?string $note = null, string $actor = 'system'): void
  {
    try {
      $db->query("SELECT 1 FROM order_status_logs LIMIT 1");
    } catch (Throwable $e) {
      return;
    }

    $adminId = $_SESSION['admin']['id'] ?? null;
    $st = $db->prepare("
      INSERT INTO order_status_logs
        (order_id, old_status, new_status, old_shipping_status, new_shipping_status, note, changed_by_admin_id, changed_by_type, created_at)
      VALUES
        (?,?,?,?,?,?,?,?,NOW())
    ");
    $st->execute([
      $orderId,
      $oldStatus,
      $newStatus,
      $oldShippingStatus,
      $newShippingStatus,
      $note,
      $adminId,
      $actor
    ]);
  }
}
