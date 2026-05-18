<?php
require_once __DIR__ . '/../db.php';

class Product {

  public static function countActive(?int $categoryId = null, ?string $size=null, ?string $color=null, ?string $q=null): int {
    $where = ["p.status='active'"];
    $params = [];

    if ($categoryId) { $where[] = "p.category_id=?"; $params[] = $categoryId; }
    if ($q) {
      $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
      $params[] = "%$q%";
      $params[] = "%$q%";
    }

    $join = "";
    if ($size || $color) {
      $join = "JOIN product_variants v ON v.product_id=p.id";
      if ($size) { $where[] = "v.size=?"; $params[] = $size; }
      if ($color) { $where[] = "v.color=?"; $params[] = $color; }
    }

    $sql = "SELECT COUNT(DISTINCT p.id) c FROM products p $join WHERE " . implode(" AND ", $where);
    $st = db()->prepare($sql);
    $st->execute($params);
    return (int)$st->fetchColumn();
  }

  public static function listActive(int $limit, int $offset, ?int $categoryId = null, ?string $size=null, ?string $color=null, ?string $q=null): array {
    $where = ["p.status='active'"];
    $params = [];

    if ($categoryId) { $where[] = "p.category_id=?"; $params[] = $categoryId; }
    if ($q) {
      $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
      $params[] = "%$q%";
      $params[] = "%$q%";
    }

    $join = "";
    if ($size || $color) {
      $join = "JOIN product_variants v ON v.product_id=p.id";
      if ($size) { $where[] = "v.size=?"; $params[] = $size; }
      if ($color) { $where[] = "v.color=?"; $params[] = $color; }
    }

    $sql = "SELECT DISTINCT p.* FROM products p $join
            WHERE " . implode(" AND ", $where) . "
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?";

    $st = db()->prepare($sql);
    foreach ($params as $i => $val) $st->bindValue($i + 1, $val);
    $st->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
    $st->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function bySlug(string $slug): ?array {
    $st = db()->prepare("SELECT * FROM products WHERE slug=? LIMIT 1");
    $st->execute([$slug]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
  }

  public static function byId(int $id): ?array {
    $st = db()->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
  }

  public static function variants(int $productId): array {
    $st = db()->prepare("SELECT * FROM product_variants WHERE product_id=? ORDER BY size ASC, color ASC");
    $st->execute([$productId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function distinctSizes(int $categoryId): array {
    $st = db()->prepare("SELECT DISTINCT v.size
                         FROM product_variants v
                         JOIN products p ON p.id=v.product_id
                         WHERE p.category_id=? AND p.status='active'
                         ORDER BY v.size ASC");
    $st->execute([$categoryId]);
    return array_values(array_filter(array_map(fn($r) => $r['size'], $st->fetchAll(PDO::FETCH_ASSOC))));
  }

  public static function distinctColors(int $categoryId): array {
    $st = db()->prepare("SELECT DISTINCT v.color
                         FROM product_variants v
                         JOIN products p ON p.id=v.product_id
                         WHERE p.category_id=? AND p.status='active'
                         ORDER BY v.color ASC");
    $st->execute([$categoryId]);
    return array_values(array_filter(array_map(fn($r) => $r['color'], $st->fetchAll(PDO::FETCH_ASSOC))));
  }

  public static function findManyByIds(array $ids): array {
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (!$ids) return [];

    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare("SELECT * FROM products WHERE id IN ($in)");
    $st->execute($ids);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $map = [];
    foreach ($rows as $r) $map[(int)$r['id']] = $r;
    return $map;
  }

  public static function suggest(string $q, int $limit = 8): array {
    $q = trim($q);
    if ($q === '') return [];

    $safe = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
    $like = '%' . $safe . '%';
    $likeStart = $safe . '%';

    $st = db()->prepare("
      SELECT id, name, slug, price, thumbnail
      FROM products
      WHERE status='active' AND name LIKE ? ESCAPE '\\\\'
      ORDER BY
        (name LIKE ? ESCAPE '\\\\') DESC,
        is_new DESC,
        id DESC
      LIMIT ?
    ");
    $st->bindValue(1, $like, PDO::PARAM_STR);
    $st->bindValue(2, $likeStart, PDO::PARAM_STR);
    $st->bindValue(3, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function adminCount(?string $q=null, ?string $status=null): int {
    $where = ["1=1"];
    $p = [];
    if ($q) { $where[] = "(name LIKE ? OR sku LIKE ?)"; $p[] = "%$q%"; $p[] = "%$q%"; }
    if ($status) { $where[] = "status=?"; $p[] = $status; }

    $st = db()->prepare("SELECT COUNT(*) FROM products WHERE " . implode(" AND ", $where));
    $st->execute($p);
    return (int)$st->fetchColumn();
  }

  public static function adminList(int $limit, int $offset, ?string $q = null, ?string $status = null): array {
  $where = ["1=1"];
  $p = [];

  if ($q) {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)";
    $p[] = "%$q%";
    $p[] = "%$q%";
    $p[] = "%$q%";
  }

  if ($status) {
    $where[] = "p.status = ?";
    $p[] = $status;
  }

  $sql = "
    SELECT
      p.*,
      c.id AS category_id,
      c.name AS category_name,
      c.parent_id AS category_parent_id,
      pc.id AS parent_category_id,
      pc.name AS parent_category_name
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN categories pc ON pc.id = c.parent_id
    WHERE " . implode(" AND ", $where) . "
    ORDER BY
      COALESCE(pc.sort_order, c.sort_order) ASC,
      COALESCE(pc.name, c.name) ASC,
      c.sort_order ASC,
      c.name ASC,
      p.id DESC
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

  public static function create(array $data): int {
    $st = db()->prepare("INSERT INTO products
      (category_id, name, slug, sku, price, compare_at_price, thumbnail, description, status, is_new, updated_at)
      VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
    $st->execute([
      $data['category_id'], $data['name'], $data['slug'], $data['sku'],
      $data['price'], $data['compare_at_price'], $data['thumbnail'],
      $data['description'], $data['status'], $data['is_new']
    ]);
    return (int)db()->lastInsertId();
  }

  public static function update(int $id, array $data): void {
    $st = db()->prepare("UPDATE products SET
      category_id=?, name=?, slug=?, sku=?, price=?, compare_at_price=?,
      thumbnail=?, description=?, status=?, is_new=?, updated_at=NOW()
      WHERE id=?");
    $st->execute([
      $data['category_id'], $data['name'], $data['slug'], $data['sku'],
      $data['price'], $data['compare_at_price'], $data['thumbnail'],
      $data['description'], $data['status'], $data['is_new'], $id
    ]);
  }

  public static function setStatus(int $id, string $status): void {
    $db = db();
    $st = $db->prepare("UPDATE products SET status = ? WHERE id = ?");
    $st->execute([$status, $id]);
  }

  public static function delete(int $id): void {
    db()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
  }

  public static function adminInventoryFallback($q = null, $limit = 500) {
    $db = db();

    $sql = "
      SELECT
        p.id AS product_id,
        p.name AS product_name,
        p.sku AS product_sku,
        v.id AS variant_id,
        v.size,
        v.color,
        v.stock
      FROM products p
      JOIN product_variants v ON v.product_id = p.id
      WHERE 1=1
    ";
    $params = [];

    if ($q !== null && $q !== '') {
      $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
      $like = "%" . $q . "%";
      $params[] = $like;
      $params[] = $like;
    }

    $sql .= " ORDER BY p.id DESC, v.id DESC LIMIT " . (int)$limit;

    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function setVariants(int $productId, array $variants): void {
    $pdo = db();
    $pdo->beginTransaction();
    try {
      $pdo->prepare("DELETE FROM product_variants WHERE product_id=?")->execute([$productId]);
      $ins = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, stock) VALUES (?,?,?,?)");

      foreach ($variants as $v) {
        $size = trim($v['size']);
        $color = trim($v['color']);
        $stock = (int)$v['stock'];
        if ($size === '' || $color === '') continue;
        $ins->execute([$productId, $size, $color, $stock]);
      }

      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function inventoryHistory($limit = 500, $q = null) {
    $db = db();

    $sql = "
      SELECT
        l.id,
        l.product_id,
        p.name AS product_name,
        l.type,
        l.qty,
        l.note,
        l.created_at
      FROM inventory_logs l
      JOIN products p ON p.id = l.product_id
      WHERE 1=1
    ";

    $params = [];

    if ($q !== null && $q !== '') {
      $sql .= " AND p.name LIKE ?";
      $params[] = "%" . $q . "%";
    }

    $sql .= " ORDER BY l.id DESC LIMIT " . (int)$limit;

    $st = $db->prepare($sql);
    $st->execute($params);

    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function inventoryList($limit = 500, $q = null) {
    $db = db();

    $sql = "
      SELECT
        p.id   AS product_id,
        p.name AS product_name,
        p.sku  AS product_sku,
        SUM(COALESCE(v.stock,0)) AS stock
      FROM products p
      LEFT JOIN product_variants v ON v.product_id = p.id
      WHERE 1=1
    ";

    $params = [];
    if ($q !== null && $q !== '') {
      $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
      $like = '%' . $q . '%';
      $params[] = $like;
      $params[] = $like;
    }

    $sql .= "
      GROUP BY p.id, p.name, p.sku
      ORDER BY p.id DESC
      LIMIT " . (int)$limit;

    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function listByCategorySlug($slug, $limit = 12, $offset = 0) {
    $db = db();
    $limit = (int)$limit;
    $offset = (int)$offset;

    $st = $db->prepare("
      SELECT p.*
      FROM products p
      JOIN categories c ON c.id = p.category_id
      WHERE c.slug = ?
      ORDER BY p.id DESC
      LIMIT $limit OFFSET $offset
    ");
    $st->execute([$slug]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function countByCategorySlug($slug) {
    $db = db();
    $st = $db->prepare("
      SELECT COUNT(*) AS cnt
      FROM products p
      JOIN categories c ON c.id = p.category_id
      WHERE c.slug = ?
    ");
    $st->execute([$slug]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return (int)($r['cnt'] ?? 0);
  }

  public static function countActiveByCategoryTree(int $categoryId, ?string $size=null, ?string $color=null, ?string $q=null): int {
    $where = ["p.status='active'"];
    $params = [$categoryId, $categoryId];

    $where[] = "p.category_id IN (
      SELECT id FROM categories WHERE id=?
      UNION
      SELECT id FROM categories WHERE parent_id=?
    )";

    if ($q) {
      $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
      $params[] = "%$q%";
      $params[] = "%$q%";
    }

    $join = "";
    if ($size || $color) {
      $join = "JOIN product_variants v ON v.product_id=p.id";
      if ($size) { $where[] = "v.size=?"; $params[] = $size; }
      if ($color) { $where[] = "v.color=?"; $params[] = $color; }
    }

    $sql = "SELECT COUNT(DISTINCT p.id) c FROM products p $join WHERE " . implode(" AND ", $where);
    $st = db()->prepare($sql);
    $st->execute($params);
    return (int)$st->fetchColumn();
  }

  public static function listActiveByCategoryTree(int $limit, int $offset, int $categoryId, ?string $size=null, ?string $color=null, ?string $q=null): array {
    $where = ["p.status='active'"];
    $params = [$categoryId, $categoryId];

    $where[] = "p.category_id IN (
      SELECT id FROM categories WHERE id=?
      UNION
      SELECT id FROM categories WHERE parent_id=?
    )";

    if ($q) {
      $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
      $params[] = "%$q%";
      $params[] = "%$q%";
    }

    $join = "";
    if ($size || $color) {
      $join = "JOIN product_variants v ON v.product_id=p.id";
      if ($size) { $where[] = "v.size=?"; $params[] = $size; }
      if ($color) { $where[] = "v.color=?"; $params[] = $color; }
    }

    $sql = "SELECT DISTINCT p.* FROM products p $join
            WHERE " . implode(" AND ", $where) . "
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?";

    $st = db()->prepare($sql);
    $i = 1;
    foreach ($params as $val) $st->bindValue($i++, $val);
    $st->bindValue($i++, $limit, PDO::PARAM_INT);
    $st->bindValue($i++, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function distinctSizesByCategoryTree(int $categoryId): array {
    $st = db()->prepare("
      SELECT DISTINCT v.size
      FROM product_variants v
      JOIN products p ON p.id = v.product_id
      WHERE p.status='active'
        AND p.category_id IN (
          SELECT id FROM categories WHERE id=?
          UNION
          SELECT id FROM categories WHERE parent_id=?
        )
      ORDER BY v.size ASC
    ");
    $st->execute([$categoryId, $categoryId]);
    return array_values(array_filter(array_map(fn($r) => $r['size'], $st->fetchAll(PDO::FETCH_ASSOC))));
  }

  public static function distinctColorsByCategoryTree(int $categoryId): array {
    $st = db()->prepare("
      SELECT DISTINCT v.color
      FROM product_variants v
      JOIN products p ON p.id = v.product_id
      WHERE p.status='active'
        AND p.category_id IN (
          SELECT id FROM categories WHERE id=?
          UNION
          SELECT id FROM categories WHERE parent_id=?
        )
      ORDER BY v.color ASC
    ");
    $st->execute([$categoryId, $categoryId]);
    return array_values(array_filter(array_map(fn($r) => $r['color'], $st->fetchAll(PDO::FETCH_ASSOC))));
  }

  public static function adminParentCategories(): array {
  $st = db()->query("
    SELECT id, name, slug, sort_order
    FROM categories
    WHERE parent_id IS NULL
    ORDER BY sort_order ASC, name ASC, id ASC
  ");
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

public static function adminCountByParentCategory(int $parentCategoryId, ?string $q = null, ?string $status = null): int {
  $where = [];
  $params = [$parentCategoryId, $parentCategoryId];

  $where[] = "p.category_id IN (
    SELECT id FROM categories WHERE id = ? OR parent_id = ?
  )";

  if ($q !== null && $q !== '') {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
  }

  if ($status !== null && $status !== '') {
    $where[] = "p.status = ?";
    $params[] = $status;
  }

  $sql = "
    SELECT COUNT(*)
    FROM products p
    WHERE " . implode(" AND ", $where);

  $st = db()->prepare($sql);
  $st->execute($params);
  return (int)$st->fetchColumn();
}

public static function adminListByParentCategory(
  int $parentCategoryId,
  int $limit,
  int $offset,
  ?string $q = null,
  ?string $status = null
): array {
  $where = [];
  $params = [$parentCategoryId, $parentCategoryId];

  $where[] = "p.category_id IN (
    SELECT id FROM categories WHERE id = ? OR parent_id = ?
  )";

  if ($q !== null && $q !== '') {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.slug LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
  }

  if ($status !== null && $status !== '') {
    $where[] = "p.status = ?";
    $params[] = $status;
  }

  $sql = "
    SELECT
      p.*,
      c.id AS category_id,
      c.name AS category_name,
      c.parent_id AS category_parent_id,
      pc.id AS parent_category_id,
      pc.name AS parent_category_name
    FROM products p
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN categories pc ON pc.id = c.parent_id
    WHERE " . implode(" AND ", $where) . "
    ORDER BY
      c.sort_order ASC,
      c.name ASC,
      p.id DESC
    LIMIT ? OFFSET ?
  ";

  $st = db()->prepare($sql);
  $i = 1;

  foreach ($params as $val) {
    $st->bindValue($i++, $val);
  }

  $st->bindValue($i++, $limit, PDO::PARAM_INT);
  $st->bindValue($i++, $offset, PDO::PARAM_INT);
  $st->execute();

  return $st->fetchAll(PDO::FETCH_ASSOC);
}
}