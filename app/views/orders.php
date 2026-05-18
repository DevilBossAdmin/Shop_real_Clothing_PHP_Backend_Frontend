<?php
// $orders, $u
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
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 m-0">Đơn hàng của bạn</h1>
    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/account')) ?>">Tài khoản</a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
  <?php else: ?>

    <div class="vstack gap-2">
      <?php foreach ($orders as $o): ?>
        <a class="text-decoration-none text-dark" href="<?= e(url('/orders/view?id='.(int)$o['id'])) ?>">
          <div class="border rounded p-3 d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">Đơn #<?= (int)$o['id'] ?></div>
              <div class="text-muted small">
                <?= e($o['created_at'] ?? '') ?> • <?= e($paymentLabel($o['payment_method'] ?? '')) ?>
              </div>
            </div>
            <div class="text-end">
              <div class="fw-semibold"><?= money((int)$o['total']) ?></div>
              <span class="badge bg-dark"><?= e($statusLabel($o['status'] ?? 'pending')) ?></span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>
</div>