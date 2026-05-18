<?php
$rows = $rows ?? [];
$q = $q ?? '';
$flash = $flash ?? null;
$pager = $pager ?? ['page' => 1, 'pages' => 1];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Khách hàng</h1>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= e(is_array($flash) ? ($flash['type'] ?? 'success') : 'success') ?>">
    <?= e(is_array($flash) ? ($flash['msg'] ?? '') : $flash) ?>
  </div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/customers')) ?>">
  <div class="col-12 col-md-5">
    <input
      type="text"
      class="form-control"
      name="q"
      value="<?= e($q) ?>"
      placeholder="Tìm theo tên hoặc email..."
    >
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary" type="submit">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>ID</th>
        <th>Khách</th>
        <th>Đơn hàng</th>
        <th>Tổng chi tiêu</th>
        <th>Lần mua gần nhất</th>
        <th>Trạng thái</th>
        <th class="text-end">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-4">Không có khách hàng nào</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>#<?= (int)$r['id'] ?></td>

            <td>
              <div class="fw-semibold"><?= e($r['name'] ?? '-') ?></div>
              <div class="small text-muted"><?= e($r['email'] ?? '-') ?></div>
            </td>

            <td><?= (int)($r['orders_count'] ?? 0) ?></td>

            <td class="fw-semibold">
              <?= money((int)($r['total_spent'] ?? 0)) ?>
            </td>

            <td class="text-muted small">
              <?= e($r['last_order_at'] ?? '-') ?>
            </td>

            <td>
              <?php if ((int)($r['is_locked'] ?? 0) === 1): ?>
                <span class="badge text-bg-danger">Đã khóa</span>
              <?php else: ?>
                <span class="badge text-bg-success">Hoạt động</span>
              <?php endif; ?>
            </td>

            <td class="text-end">
              <div class="d-inline-flex gap-2">
                <a
                  class="btn btn-sm btn-outline-secondary"
                  href="<?= e(url('/admin/customers/view?id=' . (int)$r['id'])) ?>"
                >
                  Xem
                </a>

                <form method="post" action="<?= e(url('/admin/customers/toggle-lock')) ?>" class="d-inline">
                  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button
                    type="submit"
                    class="btn btn-sm <?= ((int)($r['is_locked'] ?? 0) === 1) ? 'btn-outline-success' : 'btn-outline-danger' ?>"
                    onclick="return confirm('Bạn có chắc muốn thay đổi trạng thái khóa khách hàng này?')"
                  >
                    <?= ((int)($r['is_locked'] ?? 0) === 1) ? 'Mở khóa' : 'Khóa' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if (($pager['pages'] ?? 1) > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php for ($i = 1; $i <= (int)($pager['pages'] ?? 1); $i++): ?>
        <?php
          $qs = $_GET;
          $qs['page'] = $i;
          $link = '?' . http_build_query($qs);
        ?>
        <li class="page-item <?= ($i === (int)($pager['page'] ?? 1) ? 'active' : '') ?>">
          <a class="page-link" href="<?= e($link) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>