<?php // app/views/admin/shipments.php
$shippingLabels = [
  '' => '--',
  'picking' => 'Đang chuẩn bị hàng',
  'shipping' => 'Đang vận chuyển',
  'delivered' => 'Đã giao hàng',
  'returned' => 'Hoàn trả',
  'cancelled' => 'Đã hủy giao',
];
$carrierLabels = [
  'manual' => 'Thủ công',
  'ghn' => 'GHN',
  'ghtk' => 'GHTK',
  'jt' => 'J&T',
  'vnpost' => 'VNPost',
];
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Vận đơn</h4>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/shipments')) ?>">
  <div class="col-auto">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo ID / tên / SĐT / mã vận đơn">
  </div>
  <div class="col-auto">
    <button class="btn btn-dark">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Khách</th>
        <th>SĐT</th>
        <th>Đơn vị VC</th>
        <th>Mã vận đơn</th>
        <th>Trạng thái giao</th>
        <th>Phí ship</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="text-muted">Không có dữ liệu.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['order_id'] ?></td>
            <td><?= e($r['customer_name'] ?? '') ?></td>
            <td><?= e($r['customer_phone'] ?? '') ?></td>
            <td style="min-width:140px;">
              <form class="d-flex gap-2" method="post" action="<?= e(url('/admin/shipments/save')) ?>">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int)$r['order_id'] ?>">
                <?php $curCarrier = $r['shipping_carrier'] ?: ($r['s_carrier'] ?? 'manual'); ?>
                <select class="form-select form-select-sm" name="shipping_carrier">
                  <?php foreach ($carrierLabels as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $curCarrier === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
            </td>
            <td style="min-width:220px;">
                <?php $code = $r['shipping_tracking_code'] ?: ($r['s_tracking_code'] ?? ''); ?>
                <input class="form-control form-control-sm" name="tracking_code" value="<?= e($code) ?>" placeholder="VD: J&T123...">
            </td>
            <td style="min-width:190px;">
                <?php $cur = $r['shipping_status'] ?: ($r['s_status'] ?? ''); ?>
                <select class="form-select form-select-sm" name="shipping_status">
                  <?php foreach ($shippingLabels as $st => $label): ?>
                    <option value="<?= e($st) ?>" <?= ($cur===$st?'selected':'') ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
            </td>
            <td style="min-width:120px;">
                <input class="form-control form-control-sm" type="number" min="0" name="shipping_fee" value="<?= e((string)($r['shipping_fee'] ?? 0)) ?>">
            </td>
            <td class="text-end">
                <button class="btn btn-sm btn-dark">Lưu</button>
              </form>
              <a class="btn btn-sm btn-outline-secondary mt-1" href="<?= e(url('/admin/orders/view?id=' . (int)$r['order_id'])) ?>">Xem đơn</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
