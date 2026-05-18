<?php
$categories = $categories ?? [];
$flash = $flash ?? null;

$parents = [];
$childrenMap = [];

foreach ($categories as $cat) {
    $parentId = $cat['parent_id'] ?? null;

    if ($parentId === null || $parentId === '' || (int)$parentId === 0) {
        $parents[] = $cat;
    } else {
        $childrenMap[(int)$parentId][] = $cat;
    }
}

usort($parents, function ($a, $b) {
    return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
});

foreach ($childrenMap as $pid => $items) {
    usort($items, function ($a, $b) {
        return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
    });
    $childrenMap[$pid] = $items;
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Danh mục</h2>
  <a class="btn btn-dark" href="<?= e(url('/admin/categories/create')) ?>">+ Thêm danh mục</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-success">
    <?= is_array($flash) ? e($flash['msg'] ?? '') : e($flash) ?>
  </div>
<?php endif; ?>

<?php if (empty($parents)): ?>
  <div class="card p-4 text-center">
    Chưa có danh mục lớn nào.
  </div>
<?php else: ?>
  <?php foreach ($parents as $parent): ?>
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1"><?= e($parent['name']) ?></h4>
          <div class="text-muted small">
            Slug: <?= e($parent['slug']) ?> | ID: <?= (int)$parent['id'] ?>
          </div>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/admin/categories/edit?id=' . (int)$parent['id'])) ?>">Sửa</a>
          <a class="btn btn-sm btn-outline-danger"
             href="<?= e(url('/admin/categories/delete?id=' . (int)$parent['id'])) ?>"
             onclick="return confirm('Xóa danh mục này?')">Xóa</a>
        </div>
      </div>

      <div class="card-body">
        <?php $children = $childrenMap[(int)$parent['id']] ?? []; ?>

        <?php if (empty($children)): ?>
          <div class="text-muted">Chưa có danh mục con.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 80px;">ID</th>
                  <th>Tên danh mục con</th>
                  <th>Slug</th>
                  <th style="width: 100px;">Thứ tự</th>
                  <th style="width: 180px;" class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($children as $child): ?>
                  <tr>
                    <td><?= (int)$child['id'] ?></td>
                    <td><?= e($child['name']) ?></td>
                    <td><?= e($child['slug']) ?></td>
                    <td><?= (int)($child['sort_order'] ?? 0) ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/admin/categories/edit?id=' . (int)$child['id'])) ?>">Sửa</a>
                      <a class="btn btn-sm btn-outline-danger"
                         href="<?= e(url('/admin/categories/delete?id=' . (int)$child['id'])) ?>"
                         onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>