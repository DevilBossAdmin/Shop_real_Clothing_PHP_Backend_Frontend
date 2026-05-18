<?php
// Expected from index.php:
// $lines, $total, $u, $form, $error, $success, $order_id, $order (optional)
// $subtotal, $discount, $payable, $coupon, $flash_msg

$pm = $form['payment_method'] ?? 'cod';

$subtotal = (int)($subtotal ?? $total ?? 0);
$discount = (int)($discount ?? 0);
$payable  = (int)($payable ?? max(0, $subtotal - $discount));
if ($payable < 0) $payable = 0;

$coupon = $coupon ?? null;
$flash_msg = $flash_msg ?? null;

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
    'cod'  => 'Thanh toán khi nhận hàng (COD)',
    'qr'   => 'Thanh toán QR Code',
    'bank' => 'Chuyển khoản ngân hàng',
    'card' => 'Thẻ / VNPAY',
    default => 'N/A'
  };
};

$payStatusLabel = function($ps) {
  return match($ps) {
    'paid'   => 'Đã thanh toán',
    'unpaid' => 'Chưa thanh toán',
    'failed' => 'Thanh toán thất bại',
    default  => 'N/A'
  };
};
?>

<div class="container my-4">

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($flash_msg)): ?>
    <?php
      $type = 'info';
      $msg  = $flash_msg;

      if (is_array($flash_msg)) {
        $type = $flash_msg['type'] ?? 'info';
        $msg  = $flash_msg['msg'] ?? '';
      }

      $allow = ['primary','secondary','success','danger','warning','info','light','dark'];
      if (!in_array($type, $allow, true)) $type = 'info';
    ?>
    <div class="alert alert-<?= e($type) ?>"><?= e((string)$msg) ?></div>
  <?php endif; ?>

  <?php if (!empty($success) && !empty($order)): ?>
    <div class="alert alert-success d-flex justify-content-between align-items-center">
      <div>
        Đặt hàng thành công! Mã đơn: <b>#<?= (int)$order['id'] ?></b>
        <div class="small text-muted mt-1">
          Trạng thái: <b><?= e($statusLabel($order['status'] ?? 'pending')) ?></b> •
          Thanh toán: <b><?= e($payLabel($order['payment_method'] ?? '')) ?></b>
          (<?= e($payStatusLabel($order['payment_status'] ?? 'unpaid')) ?>)
        </div>
      </div>
      <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/orders/view?id=' . (int)$order['id'])) ?>">
        Xem chi tiết
      </a>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card p-3 mb-3">
        <div class="fw-semibold mb-2">Thông tin nhận hàng</div>

        <form method="post" action="index.php?r=/checkout/submit">
          <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Họ tên</label>
            <input
              class="form-control"
              name="name"
              value="<?= e($form['name'] ?? ($u['name'] ?? '')) ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label">Email nhận thông báo</label>
            <input
              class="form-control"
              name="email"
              type="email"
              value="<?= e($form['email'] ?? ($u['email'] ?? '')) ?>"
              required
            >
            <div class="form-text">
              Hệ thống sẽ gửi xác nhận đơn hàng và cập nhật giao hàng về email này.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Số điện thoại</label>
            <input
              class="form-control"
              name="phone"
              value="<?= e($form['phone'] ?? '') ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label">Địa chỉ</label>
            <input
              class="form-control"
              name="address"
              value="<?= e($form['address'] ?? '') ?>"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label">Ghi chú (tuỳ chọn)</label>
            <textarea class="form-control" name="note" rows="2"><?= e($form['note'] ?? '') ?></textarea>
          </div>

          <div class="card p-3 mb-3">
            <div class="fw-semibold mb-2">Phương thức thanh toán</div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="cod" <?= $pm === 'cod' ? 'checked' : '' ?>>
              <label class="form-check-label" for="pm_cod">Thanh toán khi nhận hàng (COD)</label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_qr" value="qr" <?= $pm === 'qr' ? 'checked' : '' ?>>
              <label class="form-check-label" for="pm_qr">Thanh toán QR Code</label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_bank" value="bank" <?= $pm === 'bank' ? 'checked' : '' ?>>
              <label class="form-check-label" for="pm_bank">Chuyển khoản ngân hàng</label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_card" value="card" <?= $pm === 'card' ? 'checked' : '' ?>>
              <label class="form-check-label" for="pm_card">Thẻ / VNPAY</label>
            </div>

            <div id="box_qr" class="border rounded p-3 mt-3" style="display:none">
              <div class="fw-semibold mb-2">Quét mã QR để thanh toán</div>
              <div class="text-muted small mb-2">
                Đặt ảnh QR tại <b>public/assets/qr.png</b>.
              </div>

              <img
                src="<?= e(url('/assets/qr.png')) ?>"
                alt="QR Payment"
                style="max-width:220px;width:100%;border-radius:10px"
                onerror="this.style.display='none'"
              >

              <div class="mt-2 small">
                <div><b>Số tiền:</b> <?= money((int)$payable) ?></div>
                <div><b>Nội dung:</b> SHOP - SDT của bạn</div>
              </div>
            </div>

            <div id="box_bank" class="border rounded p-3 mt-3" style="display:none">
              <div class="fw-semibold mb-2">Thông tin chuyển khoản</div>
              <div class="small">
                <div><span class="text-muted">Ngân hàng:</span> <b>MBbank</b></div>
                <div><span class="text-muted">Số tài khoản:</span> <b>0676222309999</b></div>
                <div><span class="text-muted">Chủ tài khoản:</span> <b>NGUYEN THANH TAI</b></div>
                <div><span class="text-muted">Số tiền:</span> <b><?= money((int)$payable) ?></b></div>
                <div><span class="text-muted">Nội dung:</span> <b>SHOP <?= date('Ymd') ?> - SDT</b></div>
                <div class="text-muted mt-2">
                  Chuyển khoản xong bạn có thể gửi ảnh bill cho shop để xác nhận nhanh.
                </div>
              </div>
            </div>

            <div id="box_card" class="border rounded p-3 mt-3" style="display:none">
              <div class="fw-semibold mb-2">Thanh toán qua VNPAY</div>
              <div class="text-muted small">
                Sau khi bấm <b>Tiến hành thanh toán / Đặt hàng</b>, hệ thống sẽ chuyển bạn sang cổng thanh toán.
              </div>
            </div>
          </div>

          <button class="btn btn-dark w-100" type="submit">
            Tiến hành thanh toán / Đặt hàng
          </button>
        </form>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card p-3">
        <div class="fw-semibold mb-2">Đơn hàng</div>

        <?php if (empty($lines)): ?>
          <div class="text-muted">Giỏ hàng trống.</div>
        <?php else: ?>
          <div class="vstack gap-2">
            <?php foreach ($lines as $it): ?>
              <div class="d-flex justify-content-between align-items-center border rounded p-2">
                <div>
                  <div class="fw-semibold"><?= e($it['name']) ?></div>
                  <div class="text-muted small">
                    <?= e($it['size'] ?: '-') ?>/<?= e($it['color'] ?: '-') ?> • x<?= (int)$it['qty'] ?>
                  </div>
                </div>
                <div class="fw-semibold"><?= money((int)$it['sub']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <hr class="my-3">

          <div class="border rounded p-2">
            <div class="fw-semibold mb-2">Mã giảm giá</div>

            <?php if (!empty($coupon['code']) && $discount > 0): ?>
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div><b><?= e($coupon['code']) ?></b></div>
                  <div class="small text-muted">Giảm <?= money((int)$discount) ?></div>
                </div>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/coupon/remove')) ?>">Gỡ</a>
              </div>
            <?php else: ?>
              <form method="post" action="<?= e(url('/coupon/apply')) ?>" class="d-flex gap-2">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input class="form-control" name="code" placeholder="Nhập mã (VD: SALE10)">
                <button class="btn btn-dark" type="submit">Áp dụng</button>
              </form>
            <?php endif; ?>
          </div>

          <hr class="my-3">

          <div class="d-flex justify-content-between">
            <div class="text-muted">Tạm tính</div>
            <div class="fw-semibold"><?= money((int)$subtotal) ?></div>
          </div>

          <?php if ($discount > 0): ?>
            <div class="d-flex justify-content-between mt-1">
              <div class="text-muted">Giảm giá</div>
              <div class="fw-semibold">-<?= money((int)$discount) ?></div>
            </div>
          <?php endif; ?>

          <hr class="my-3">

          <div class="d-flex justify-content-between">
            <div class="text-muted">Tổng thanh toán</div>
            <div class="fw-bold fs-5"><?= money((int)$payable) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function togglePayBoxes() {
  const pm = document.querySelector('input[name="payment_method"]:checked')?.value || 'cod';
  const boxQR = document.getElementById('box_qr');
  const boxBank = document.getElementById('box_bank');
  const boxCard = document.getElementById('box_card');

  if (boxQR) boxQR.style.display = (pm === 'qr') ? 'block' : 'none';
  if (boxBank) boxBank.style.display = (pm === 'bank') ? 'block' : 'none';
  if (boxCard) boxCard.style.display = (pm === 'card') ? 'block' : 'none';
}

document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
  radio.addEventListener('change', togglePayBoxes);
});

togglePayBoxes();
</script>