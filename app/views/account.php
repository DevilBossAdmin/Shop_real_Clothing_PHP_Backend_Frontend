<?php
// app/views/account.php
// Expected: $u, $orders

$statusLabel = function($st) {
  return match($st) {
    'pending'   => 'Chờ xác nhận',
    'picking'   => 'Chờ lấy hàng',
    'shipping'  => 'Chờ giao hàng',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã huỷ',
    default     => 'Không xác định'
  };
};

$payLabel = function($pm) {
  return match($pm) {
    'cod'  => 'COD',
    'qr'   => 'QR Code',
    'bank' => 'Chuyển khoản',
    'card' => 'Thẻ / VNPAY',
    default => '-'
  };
};

$payStatusLabel = function($ps) {
  return match($ps) {
    'paid'   => 'Đã thanh toán',
    'unpaid' => 'Chưa thanh toán',
    'failed' => 'Thất bại',
    default  => '-'
  };
};
?>

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Tài khoản</h4>
      <div class="text-muted small">
        Xin chào, <b><?= e($u['name'] ?? '') ?></b> (<?= e($u['email'] ?? '') ?>)
      </div>
    </div>
    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/logout')) ?>">Đăng xuất</a>
  </div>

  <?php if (!empty($_SESSION['_flash_account'])): ?>
    <div class="alert alert-info"><?= e($_SESSION['_flash_account']) ?></div>
    <?php unset($_SESSION['_flash_account']); ?>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <div class="fw-semibold mb-2">Đơn hàng của bạn</div>
      <a class="btn btn-dark btn-sm" href="<?= e(url('/orders')) ?>">
  Xem danh sách đơn hàng
</a>
      <?php if (empty($orders)): ?>
        <div class="text-muted">Bạn chưa có đơn hàng nào.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr class="text-muted small">
                <th>Mã đơn</th>
                <th>Ngày</th>
                <th>Thanh toán</th>
                <th class="text-end">Tổng</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
                <?php
                  $oid = (int)($o['id'] ?? 0);
                  $st  = (string)($o['status'] ?? 'pending');
                  $created = $o['created_at'] ?? ($o['createdAt'] ?? null);
                  $pm  = (string)($o['payment_method'] ?? '');
                  $ps  = (string)($o['payment_status'] ?? '');
                  $total = (int)($o['total'] ?? 0);
                ?>
                <tr>
                  <td><b>#<?= $oid ?></b></td>
                  <td class="text-muted small">
                    <?= $created ? e($created) : '-' ?>
                  </td>

                  <td class="small">
                    <div><?= e($payLabel($pm)) ?></div>
                    <div class="text-muted"><?= e($payStatusLabel($ps)) ?></div>
                  </td>

                  <td class="text-end fw-semibold"><?= money($total) ?></td>

                  <td>
                    <?php
                      $badge = match($st) {
                        'pending' => 'bg-warning text-dark',
                        'picking' => 'bg-info text-dark',
                        'shipping' => 'bg-primary',
                        'delivered' => 'bg-success',
                        'cancelled' => 'bg-secondary',
                        default => 'bg-light text-dark'
                      };
                    ?>
                    <span class="badge <?= e($badge) ?>"><?= e($statusLabel($st)) ?></span>
                  </td>

                  <td class="text-end">
                    <!-- Nếu bạn đã có trang chi tiết đơn thì mở lại dòng dưới -->
                    <!-- <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/orders/view?id='.$oid)) ?>">Xem</a> -->

                    <?php if ($st === 'pending'): ?>
                      <form
                        method="post"
                        action="<?= e(url('/orders/cancel')) ?>"
                        style="display:inline"
                        onsubmit="return confirm('Bạn chắc chắn muốn huỷ đơn #<?= $oid ?>?');"
                      >
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= $oid ?>">
                        <button class="btn btn-outline-danger btn-sm">Huỷ đơn</button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="text-muted small mt-2">
          * Bạn chỉ có thể huỷ đơn khi trạng thái là <b>Chờ xác nhận</b>.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>