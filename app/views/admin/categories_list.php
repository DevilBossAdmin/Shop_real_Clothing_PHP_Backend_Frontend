<?php
// Expected variables: $rows, $pager, $status, $flash

$statusLabel = function($st) {
  return match($st) {
    'pending'   => 'Chờ xác nhận',
    'picking'   => 'Chờ lấy hàng',
    'shipping'  => 'Chờ giao hàng',
    'delivered' => 'Đã giao',
    default     => 'Không xác định'
  };
};

$paymentLabel = function($pm) {
  return match($pm) {
    'cod'  => 'COD',
    'qr'   => 'QR',
    'bank' => 'Chuyển khoản',
    'card' => 'VNPAY',
    default => 'N/A'
  };
};

$payStatusLabel = function($ps) {
  return match($ps) {
    'paid'   => 'Đã thanh toán',
    'unpaid' => 'Chưa thanh toán',
    'failed' => 'Thất bại',
    default  => 'N/A'
  };
};

function build_admin_orders_url(?string $status, int $page = 1): string {
  $qs = [];
  if ($status) $qs['status'] = $status;
  if ($page > 1) $qs['page'] = $page;
  $q = $qs ? ('?' . http_build_query($qs)) : '';
  return url('/admin/orders' . $q);
}
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Đơn hàng</h1>

    <form class="d-flex gap-2" method="get" action="<?= e(url('/admin/orders')) ?>">
      <select class="form-select form-select-sm" name="status" style="max-width:220px">
        <option value="" <?= empty($status) ? 'selected' : '' ?>>Tất cả trạng thái</option>
        <option value="pending"   <?= ($status==='pending') ? 'selected' : '' ?>>Chờ xác nhận</option>
        <option value="picking"   <?= ($status==='picking') ? 'selected' : '' ?>>Chờ lấy hàng</option>
        <option value="shipping"  <?= ($status==='shipping') ? 'selected' : '' ?>>Chờ giao hàng</option>
        <option value="delivered" <?= ($status==='delivered') ? 'selected' : '' ?>>Đã giao</option>
      </select>
      <button class="btn btn-sm btn-dark" type="submit">Lọc</button>
    </form>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
  <?php endif; ?>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">Chưa có đơn hàng.</div>
  <?php else: ?>

    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th style="width:90px">Mã</th>
            <th>Khách hàng</th>
            <th>Thanh toán</th>
            <th style="width:220px">Trạng thái đơn</th>
            <th style="width:140px" class="text-end">Tổng</th>
            <th style="width:160px">Ngày tạo</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td class="fw-semibold">#<?= (int)$row['id'] ?></td>

              <td>
                <div class="fw-semibold"><?= e($row['customer_name'] ?? '') ?></div>
                <div class="text-muted small"><?= e($row['customer_phone'] ?? '') ?></div>
                <div class="text-muted small"><?= e($row['customer_address'] ?? '') ?></div>
                <?php if (!empty($row['user_email'])): ?>
                  <div class="text-muted small">User: <?= e($row['user_email']) ?></div>
                <?php endif; ?>
              </td>

              <td>
                <div><span class="badge bg-secondary"><?= e($paymentLabel($row['payment_method'] ?? '')) ?></span></div>
                <div class="text-muted small"><?= e($payStatusLabel($row['payment_status'] ?? '')) ?></div>
                <?php if (!empty($row['payment_ref'])): ?>
                  <div class="text-muted small">Ref: <?= e($row['payment_ref']) ?></div>
                <?php endif; ?>
              </td>

              <td>
                <!-- ✅ Form cập nhật trạng thái đơn hàng -->
                <form method="post" action="<?= e(url('/admin/orders/status')) ?>" class="d-flex gap-2 align-items-center">
                  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

                  <select name="status" class="form-select form-select-sm" style="max-width: 180px;">
                    <option value="pending"   <?= ($row['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                    <option value="picking"   <?= ($row['status'] ?? '') === 'picking' ? 'selected' : '' ?>>Chờ lấy hàng</option>
                    <option value="shipping"  <?= ($row['status'] ?? '') === 'shipping' ? 'selected' : '' ?>>Chờ giao hàng</option>
                    <option value="delivered" <?= ($row['status'] ?? '') === 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                  </select>

                  <button class="btn btn-sm btn-dark" type="submit">Cập nhật</button>
                </form>

                <div class="text-muted small mt-1">
                  Hiện tại: <b><?= e($statusLabel($row['status'] ?? 'pending')) ?></b>
                </div>
              </td>

              <td class="text-end fw-semibold"><?= money((int)($row['total'] ?? 0)) ?></td>

              <td class="text-muted small"><?= e($row['created_at'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if (!empty($pager) && (int)($pager['totalPages'] ?? 1) > 1): ?>
      <?php
        $page = (int)($pager['page'] ?? 1);
        $totalPages = (int)($pager['totalPages'] ?? 1);
      ?>
      <nav aria-label="Orders pagination">
        <ul class="pagination">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(build_admin_orders_url($status, max(1, $page-1))) ?>">←</a>
          </li>

          <?php
            // show a compact range
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
          ?>

          <?php if ($start > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= e(build_admin_orders_url($status, 1)) ?>">1</a></li>
            <?php if ($start > 2): ?>
              <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
          <?php endif; ?>

          <?php for ($p = $start; $p <= $end; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= e(build_admin_orders_url($status, $p)) ?>"><?= (int)$p ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?>
              <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <li class="page-item"><a class="page-link" href="<?= e(build_admin_orders_url($status, $totalPages)) ?>"><?= (int)$totalPages ?></a></li>
          <?php endif; ?>

          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(build_admin_orders_url($status, min($totalPages, $page+1))) ?>">→</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>

  <?php endif; ?>
</div>