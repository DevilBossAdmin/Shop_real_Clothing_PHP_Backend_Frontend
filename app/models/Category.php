<?php
require_once __DIR__ . '/../db.php';

class Category {
  public static function all(): array {
    return db()->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll();
  }

  public static function tree(): array {
    $cats = self::all();
    $byId = [];
    foreach ($cats as $c) { $c['children'] = []; $byId[(int)$c['id']] = $c; }
    $root = [];
    foreach ($byId as $id => $c) {
      $pid = $c['parent_id'] ? (int)$c['parent_id'] : null;
      if ($pid && isset($byId[$pid])) $byId[$pid]['children'][] = &$byId[$id];
      else $root[] = &$byId[$id];
    }
    return $root;
  }

  public static function bySlug(string $slug): ?array {
    $st = db()->prepare("SELECT * FROM categories WHERE slug=? LIMIT 1");
    $st->execute([$slug]);
    $r = $st->fetch();
    return $r ?: null;
  }

  public static function create($parentId, $name, $slug, $sort) {
    $st = db()->prepare("INSERT INTO categories (parent_id, name, slug, sort_order) VALUES (?, ?, ?, ?)");
    $st->execute([$parentId, $name, $slug, $sort]);
    return (int)db()->lastInsertId();
}

  public static function update($id, $parentId, $name, $slug, $sort) {
    $st = db()->prepare("UPDATE categories SET parent_id=?, name=?, slug=?, sort_order=? WHERE id=?");
    $st->execute([$parentId, $name, $slug, $sort, $id]);
}

  public static function delete(int $id): void {
    $st = db()->prepare("DELETE FROM categories WHERE id=?");
    $st->execute([$id]);
  }

  public static function byId($id) {
  $db = db();
  $st = $db->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
  $st->execute([(int)$id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  return $row ? $row : null;
}
}
