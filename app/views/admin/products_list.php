<?php
$productGroups = $productGroups ?? [];
$q = $q ?? '';
$status = $status ?? '';
$flash = $flash ?? null;
$perPage = $perPage ?? 30;

$buildGroupUrl = function (int $targetParentId, int $targetPage) use ($productGroups, $q, $status, $perPage) {
    $params = [
        'q' => $q,
        'status' => $status,
        'per_page' => $perPage,
    ];

    foreach ($productGroups as $group) {
        $parentId = (int)($group['parent']['id'] ?? 0);
        $currentPage = (int)($group['pager']['page'] ?? 1);
        $params['page_group_' . $parentId] = $parentId === $targetParentId ? $targetPage : $currentPage;
    }

    return url('/admin/products?' . http_build_query($params));
};
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Sản phẩm</h2>
  <a class="btn btn-dark" href="<?= e(url('/admin/products/create')) ?>">+ Thêm sản phẩm</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= e(is_array($flash) ? ($flash['type'] ?? 'success') : 'success') ?>">
    <?= e(is_array($flash) ? ($flash['msg'] ?? '') : $flash) ?>
  </div>
<?php endif; ?>

<form method="get" action="<?= e(url('/admin/products')) ?>" class="card p-3 mb-4">
  <div class="row g-3">
    <div class="col-md-5">
      <label class="form-label">Tìm kiếm</label>
      <input
        type="text"
        name="q"
        class="form-control"
        value="<?= e($q) ?>"
        placeholder="Tên sản phẩm, SKU, slug..."
      >
    </div>

    <div class="col-md-3">
      <label class="form-label">Trạng thái</label>
      <select name="status" class="form-select">
        <option value="">Tất cả</option>
        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Hiển thị</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Nháp</option>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label">Mỗi nhóm / trang</label>
      <select name="per_page" class="form-select">
        <option value="20" <?= (int)$perPage === 20 ? 'selected' : '' ?>>20</option>
        <option value="30" <?= (int)$perPage === 30 ? 'selected' : '' ?>>30</option>
        <option value="50" <?= (int)$perPage === 50 ? 'selected' : '' ?>>50</option>
        <option value="100" <?= (int)$perPage === 100 ? 'selected' : '' ?>>100</option>
      </select>
    </div>

    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">Lọc</button>
    </div>
  </div>
</form>

<?php if (empty($productGroups)): ?>
  <div class="card p-4 text-center">
    Chưa có nhóm sản phẩm nào.
  </div>
<?php else: ?>
  <?php foreach ($productGroups as $group): ?>
    <?php
      $parent = $group['parent'];
      $rows = $group['rows'] ?? [];
      $pager = $group['pager'] ?? [];
      $parentId = (int)($parent['id'] ?? 0);
      $currentPage = (int)($pager['page'] ?? 1);
      $totalPages = (int)($pager['totalPages'] ?? 1);
      $total = (int)($pager['total'] ?? 0);
      $from = $total > 0 ? ((int)($pager['offset'] ?? 0) + 1) : 0;
      $to = min((int)($pager['offset'] ?? 0) + (int)($pager['perPage'] ?? 0), $total);
    ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0"><?= e($parent['name'] ?? 'Chưa có tên nhóm') ?></h4>
          <small class="text-muted">
            Hiển thị <?= $from ?> - <?= $to ?> / <?= $total ?> sản phẩm
          </small>
        </div>
      </div>

      <div class="card-body p-0">
        <?php if (empty($rows)): ?>
          <div class="p-4 text-muted">Không có sản phẩm trong nhóm này.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
              <thead>
                <tr>
                  <th style="width:70px;">ID</th>
                  <th style="width:90px;">Ảnh</th>
                  <th>Tên sản phẩm</th>
                  <th style="width:170px;">Danh mục con</th>
                  <th style="width:120px;">SKU</th>
                  <th style="width:140px;">Giá</th>
                  <th style="width:120px;">Trạng thái</th>
                  <th style="width:190px;" class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $p): ?>
                  <tr>
                    <td><?= (int)$p['id'] ?></td>

                    <td>
                      <?php if (!empty($p['thumbnail'])): ?>
                        <img
                          src="<?= e($p['thumbnail']) ?>"
                          alt="<?= e($p['name']) ?>"
                          style="width:60px;height:60px;object-fit:cover;border-radius:8px;"
                        >
                      <?php else: ?>
                        <span class="text-muted small">Không ảnh</span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <div><strong><?= e($p['name']) ?></strong></div>
                      <div class="text-muted small">Slug: <?= e($p['slug'] ?? '') ?></div>
                    </td>

                    <td><?= e($p['category_name'] ?? 'Chưa có') ?></td>
                    <td><?= e($p['sku'] ?? '') ?></td>
                    <td><?= number_format((int)($p['price'] ?? 0), 0, ',', '.') ?>đ</td>

                    <td>
                      <?php if (($p['status'] ?? '') === 'active'): ?>
                        <span class="badge bg-success">Hiển thị</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Nháp</span>
                      <?php endif; ?>
                    </td>

                    <td class="text-end">
                      <a
                        class="btn btn-sm btn-outline-secondary"
                        href="<?= e(url('/admin/products/edit?id=' . (int)$p['id'])) ?>"
                      >
                        Sửa
                      </a>

                      <a
                        class="btn btn-sm btn-outline-danger"
                        href="<?= e(url('/admin/products/delete?id=' . (int)$p['id'])) ?>"
                        onclick="return confirm('Xóa sản phẩm này?')"
                      >
                        Xóa
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="card-footer">
          <nav>
            <ul class="pagination mb-0 flex-wrap">
              <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e($buildGroupUrl($parentId, max(1, $currentPage - 1))) ?>">«</a>
              </li>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                  <a class="page-link" href="<?= e($buildGroupUrl($parentId, $i)) ?>">
                    <?= $i ?>
                  </a>
                </li>
              <?php endfor; ?>

              <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e($buildGroupUrl($parentId, min($totalPages, $currentPage + 1))) ?>">»</a>
              </li>
            </ul>
          </nav>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>