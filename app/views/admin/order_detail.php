<?php
$o = $order;
$items = $o['items'] ?? [];
$logs = $o['status_logs'] ?? [];

$statusLabels = [
  'pending' => 'Chờ xác nhận',
  'confirmed' => 'Đã xác nhận',
  'shipping' => 'Đang giao hàng',
  'completed' => 'Hoàn tất',
  'cancelled' => 'Đã hủy',
];
$shippingLabels = [
  '' => '-- Chưa cập nhật --',
  'picking' => 'Đang chuẩn bị hàng',
  'shipping' => 'Đang vận chuyển',
  'delivered' => 'Đã giao hàng',
  'returned' => 'Hoàn trả',
  'cancelled' => 'Đã hủy giao',
];
$paymentLabels = [
  'unpaid' => 'Chưa thanh toán',
  'paid' => 'Đã thanh toán',
  'failed' => 'Thanh toán thất bại',
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
  <h4 class="mb-0">Đơn #<?= (int)$o['id'] ?></h4>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/admin/orders')) ?>">← Quay lại</a>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-6"><b>Khách:</b> <?= e($o['customer_name'] ?? '') ?></div>
          <div class="col-md-6"><b>Email:</b> <?= e($o['customer_email'] ?: ($o['user_email'] ?? '')) ?></div>
          <div class="col-md-6"><b>SĐT:</b> <?= e($o['customer_phone'] ?? '') ?></div>
          <div class="col-md-6"><b>Địa chỉ:</b> <?= e($o['customer_address'] ?? '') ?></div>
          <div class="col-md-6"><b>Zone ship:</b> <?= e($o['shipping_zone_name'] ?? 'Chưa gán') ?></div>
          <div class="col-md-6"><b>Phí ship:</b> <?= number_format((int)($o['shipping_fee'] ?? 0)) ?> đ</div>
          <div class="col-md-6"><b>Tạm tính:</b> <?= number_format((int)($o['subtotal'] ?? 0)) ?> đ</div>
          <div class="col-md-6"><b>Giảm giá:</b> <?= number_format((int)($o['discount'] ?? 0)) ?> đ</div>
          <div class="col-md-6"><b>Tổng:</b> <?= number_format((int)($o['total'] ?? 0)) ?> đ</div>
          <div class="col-md-6"><b>Thanh toán:</b> <?= e($paymentLabels[$o['payment_status'] ?? 'unpaid'] ?? ($o['payment_status'] ?? '')) ?></div>
          <div class="col-md-6"><b>Trạng thái đơn:</b> <?= e($statusLabels[$o['status'] ?? 'pending'] ?? ($o['status'] ?? '')) ?></div>
          <div class="col-md-6"><b>Trạng thái giao:</b> <?= e($shippingLabels[$o['shipping_status'] ?? ''] ?? ($o['shipping_status'] ?? '')) ?></div>
          <div class="col-md-6"><b>Mã vận đơn:</b> <?= e($o['tracking_code'] ?: 'Chưa cập nhật') ?></div>
          <div class="col-12"><b>Ghi chú:</b> <?= e($o['note'] ?? '') ?></div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <form method="post" action="<?= e(url('/admin/orders/status')) ?>" class="row g-3">
          <?= csrf_field(); ?>
          <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
          <div class="col-md-6">
            <label class="form-label">Trạng thái đơn hàng</label>
            <select class="form-select" name="status" required>
              <?php $curStatus = $o['status'] ?? 'pending'; ?>
              <?php foreach ($statusLabels as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $curStatus === $key ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Trạng thái giao hàng</label>
            <select class="form-select" name="shipping_status">
              <?php $curShipping = $o['shipping_status'] ?? ''; ?>
              <?php foreach ($shippingLabels as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $curShipping === $key ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Trạng thái thanh toán</label>
            <select class="form-select" name="payment_status">
              <?php $curPayment = $o['payment_status'] ?? 'unpaid'; ?>
              <?php foreach ($paymentLabels as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $curPayment === $key ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Đơn vị vận chuyển</label>
            <select class="form-select" name="shipping_carrier">
              <?php $curCarrier = $o['shipping_carrier'] ?? 'manual'; ?>
              <?php foreach ($carrierLabels as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $curCarrier === $key ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Mã vận đơn</label>
            <input class="form-control" name="tracking_code" value="<?= e($o['tracking_code'] ?? '') ?>" placeholder="Nhập mã vận đơn">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email nhận thông báo</label>
            <input class="form-control" name="customer_email" value="<?= e($o['customer_email'] ?: ($o['user_email'] ?? '')) ?>" placeholder="email khách hàng">
          </div>
          <div class="col-md-4">
            <label class="form-label">ID zone ship</label>
            <input class="form-control" type="number" min="1" name="shipping_zone_id" value="<?= e((string)($o['shipping_zone_id'] ?? '')) ?>" placeholder="VD: 1">
          </div>
          <div class="col-md-4">
            <label class="form-label">Phí ship</label>
            <input class="form-control" type="number" min="0" name="shipping_fee" value="<?= e((string)($o['shipping_fee'] ?? 0)) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Ghi chú cập nhật</label>
            <input class="form-control" name="note" placeholder="Ví dụ: đã bàn giao cho shipper">
          </div>
          <div class="col-12">
            <button class="btn btn-dark">Lưu cập nhật và gửi mail</button>
          </div>
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Size</th>
            <th>Màu</th>
            <th>Giá</th>
            <th>SL</th>
            <th>Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr><td colspan="6" class="text-muted">Không có items.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $it): ?>
              <?php $line = (int)($it['price'] ?? 0) * (int)($it['qty'] ?? 0); ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= e($it['product_name'] ?? ('Sản phẩm #' . (int)($it['product_id'] ?? 0))) ?></div>
                  <div class="text-muted small">Product ID: <?= (int)($it['product_id'] ?? 0) ?></div>
                </td>
                <td><?= e($it['size'] ?? '') ?></td>
                <td><?= e($it['color'] ?? '') ?></td>
                <td><?= number_format((int)($it['price'] ?? 0)) ?> đ</td>
                <td><?= (int)($it['qty'] ?? 0) ?></td>
                <td><?= number_format($line) ?> đ</td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><b>Lịch sử trạng thái</b></div>
      <div class="card-body">
        <?php if (empty($logs)): ?>
          <div class="text-muted">Chưa có lịch sử trạng thái.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($logs as $log): ?>
              <div class="list-group-item px-0">
                <div><b>Đơn:</b> <?= e($statusLabels[$log['new_status'] ?? ''] ?? ($log['new_status'] ?? '')) ?></div>
                <div><b>Giao:</b> <?= e($shippingLabels[$log['new_shipping_status'] ?? ''] ?? ($log['new_shipping_status'] ?? '')) ?></div>
                <div class="small text-muted"><?= e($log['note'] ?? '') ?></div>
                <div class="small text-muted"><?= e($log['created_at'] ?? '') ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
