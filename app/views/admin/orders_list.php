<?php
$rows = $rows ?? [];
$q = $q ?? '';
$status = $status ?? '';
$flash = $flash ?? null;
$pager = $pager ?? ['page' => 1, 'pages' => 1];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Đơn hàng</h1>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= e(is_array($flash) ? ($flash['type'] ?? 'success') : 'success') ?>">
    <?= e(is_array($flash) ? ($flash['msg'] ?? '') : $flash) ?>
  </div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/orders')) ?>">
  <div class="col-12 col-md-4">
    <select class="form-select" name="status">
      <option value="">Tất cả trạng thái</option>
      <?php foreach (['pending','confirmed','shipping','completed','cancelled'] as $st): ?>
        <option value="<?= e($st) ?>" <?= ($status === $st ? 'selected' : '') ?>>
          <?= e($st) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary" type="submit">Lọc</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>ID</th>
        <th>Khách</th>
        <th>Liên hệ</th>
        <th>Tổng</th>
        <th>Thanh toán</th>
        <th>Trạng thái</th>
        <th>Ngày</th>
        <th class="text-end"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="8" class="text-center text-muted py-4">Chưa có đơn hàng nào</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>#<?= (int)$r['id'] ?></td>

            <td class="fw-semibold">
              <?= e($r['customer_name'] ?? '-') ?>
            </td>

            <td>
              <div><?= e($r['customer_phone'] ?? '-') ?></div>
              <div class="small text-muted"><?= e($r['customer_address'] ?? '-') ?></div>
            </td>

            <td class="fw-semibold">
              <?= money((int)($r['total'] ?? 0)) ?>
            </td>

            <td class="small">
              <div class="fw-semibold"><?= e($r['payment_method'] ?? '-') ?></div>
              <div class="text-muted"><?= e($r['payment_status'] ?? '-') ?></div>
            </td>

            <td>
              <span class="badge text-bg-secondary">
                <?= e($r['status'] ?? '-') ?>
              </span>
            </td>

            <td class="text-muted small">
              <?= e($r['created_at'] ?? '-') ?>
            </td>

            <td class="text-end">
              <form
                class="d-flex gap-2 justify-content-end"
                method="post"
                action="<?= e(url('/admin/orders/update-status')) ?>"
              >
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                <select class="form-select form-select-sm" name="status" style="min-width: 140px;">
                  <?php foreach (['pending','confirmed','shipping','completed','cancelled'] as $st): ?>
                    <option value="<?= e($st) ?>" <?= (($r['status'] ?? '') === $st ? 'selected' : '') ?>>
                      <?= e($st) ?>
                    </option>
                  <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-outline-secondary btn-sm">
                  Lưu
                </button>
              </form>
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
      <?php for ($i = 1; $i <= (int)$pager['pages']; $i++): ?>
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