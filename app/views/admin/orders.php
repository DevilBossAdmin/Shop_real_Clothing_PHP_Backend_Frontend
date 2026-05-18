<?php
$statusLabels = [
  'pending' => 'Chờ xác nhận',
  'confirmed' => 'Đã xác nhận',
  'shipping' => 'Đang giao hàng',
  'completed' => 'Hoàn tất',
  'cancelled' => 'Đã hủy',
];
$shippingLabels = [
  'picking' => 'Đang chuẩn bị hàng',
  'shipping' => 'Đang vận chuyển',
  'delivered' => 'Đã giao hàng',
  'returned' => 'Hoàn trả',
  'cancelled' => 'Đã hủy giao',
];
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Đơn hàng</h4>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/orders')) ?>">
  <div class="col-md-4">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo tên / SĐT / email / mã vận đơn / mã đơn">
  </div>
  <div class="col-md-3">
    <select class="form-select" name="status">
      <option value="">-- Tất cả trạng thái --</option>
      <?php $cur = $status ?? ''; ?>
      <?php foreach ($statusLabels as $k=>$label): ?>
        <option value="<?= e($k) ?>" <?= ($cur===$k?'selected':'') ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto">
    <button class="btn btn-dark">Lọc</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Khách</th>
        <th>Liên hệ</th>
        <th>Tổng</th>
        <th>Trạng thái đơn</th>
        <th>Trạng thái giao</th>
        <th>Mã vận đơn</th>
        <th>Ngày</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($orders)): ?>
        <tr><td colspan="9" class="text-muted">Chưa có đơn hàng.</td></tr>
      <?php else: ?>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= e($o['customer_name'] ?? '') ?></td>
            <td>
              <div><?= e($o['customer_phone'] ?? '') ?></div>
              <div class="text-muted small"><?= e($o['customer_email'] ?: ($o['resolved_email'] ?? '')) ?></div>
            </td>
            <td><?= number_format((int)($o['total'] ?? 0)) ?> đ</td>
            <td><?= e($statusLabels[$o['status'] ?? 'pending'] ?? ($o['status'] ?? '')) ?></td>
            <td><?= e(($o['shipping_status'] ?? '') !== '' ? ($shippingLabels[$o['shipping_status']] ?? $o['shipping_status']) : '—') ?></td>
            <td><?= e($o['tracking_code'] ?? '—') ?></td>
            <td><?= e($o['created_at'] ?? '') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-dark" href="<?= e(url('/admin/orders/view?id=' . (int)$o['id'])) ?>">Xem</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
