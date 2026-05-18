<?php
$rows = $rows ?? [];
$q = $q ?? '';
$status = $status ?? '';
$flash = $flash ?? null;
$pager = $pager ?? ['page' => 1, 'pages' => 1];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Quản lý thanh toán</h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/settings/payment')) ?>">Cấu hình nhận tiền</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= e(is_array($flash) ? ($flash['type'] ?? 'success') : 'success') ?>">
    <?= e(is_array($flash) ? ($flash['msg'] ?? '') : $flash) ?>
  </div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/payments')) ?>">
  <div class="col-12 col-md-3">
    <select class="form-select" name="status">
      <option value="">Tất cả</option>
      <?php foreach (['unpaid','paid','failed','refunded'] as $st): ?>
        <option value="<?= e($st) ?>" <?= ($status === $st ? 'selected' : '') ?>>
          <?= e($st) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12 col-md-3">
    <input
      type="text"
      class="form-control"
      name="q"
      value="<?= e($q) ?>"
      placeholder="Tìm đơn, khách, SĐT..."
    >
  </div>

  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary" type="submit">Lọc</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>Đơn</th>
        <th>Khách</th>
        <th>PTTT</th>
        <th>TTTT</th>
        <th>Tổng</th>
        <th>Ref</th>
        <th class="text-end">Cập nhật</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-4">Không có dữ liệu thanh toán</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>#<?= (int)$r['id'] ?></td>

            <td>
              <div class="fw-semibold"><?= e($r['customer_name'] ?? '-') ?></div>
              <div class="small text-muted"><?= e($r['customer_phone'] ?? '-') ?></div>
            </td>

            <td><?= e($r['payment_method'] ?? '-') ?></td>

            <td>
              <span class="badge text-bg-secondary">
                <?= e($r['payment_status'] ?? '-') ?>
              </span>
            </td>

            <td class="fw-semibold"><?= money((int)($r['total'] ?? 0)) ?></td>

            <td><?= e($r['payment_ref'] ?? '-') ?></td>

            <td class="text-end">
              <form method="post" action="<?= e(url('/admin/payments/update')) ?>" class="d-inline-flex gap-2">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                <select class="form-select form-select-sm" name="payment_status" style="min-width:140px;">
                  <option value="unpaid" <?= (($r['payment_status'] ?? '') === 'unpaid' ? 'selected' : '') ?>>Chưa</option>
                  <option value="paid" <?= (($r['payment_status'] ?? '') === 'paid' ? 'selected' : '') ?>>Đã</option>
                  <option value="failed" <?= (($r['payment_status'] ?? '') === 'failed' ? 'selected' : '') ?>>Lỗi</option>
                  <option value="refunded" <?= (($r['payment_status'] ?? '') === 'refunded' ? 'selected' : '') ?>>Hoàn</option>
                </select>

                <input
                  type="text"
                  class="form-control form-control-sm"
                  name="payment_ref"
                  value="<?= e($r['payment_ref'] ?? '') ?>"
                  placeholder="Mã GD"
                  style="min-width:130px;"
                >

                <button type="submit" class="btn btn-outline-secondary btn-sm">Lưu</button>
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