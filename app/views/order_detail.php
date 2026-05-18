<?php
// $order

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
    'cod'  => 'Thanh toán khi nhận hàng',
    'qr'   => 'QR Code',
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

// map tiến độ
$steps = [
  'pending'  => ['label'=>'Chờ xác nhận', 'icon'=>'🧾'],
  'picking'  => ['label'=>'Chờ lấy hàng', 'icon'=>'📦'],
  'shipping' => ['label'=>'Chờ giao hàng', 'icon'=>'🚚'],
  'delivered'=> ['label'=>'Đã giao', 'icon'=>'⭐'],
];

$current = $order['status'] ?? 'pending';
$orderKeys = array_keys($steps);
$curIndex = array_search($current, $orderKeys, true);
if ($curIndex === false) $curIndex = 0;

$isDone = function(int $i) use ($curIndex) { return $i <= $curIndex; };
?>

<style>
.order-stepper{
  display:flex; gap:18px; align-items:center; justify-content:space-between;
  padding:14px 10px; border:1px solid #e9ecef; border-radius:12px;
}
.order-step{
  flex:1; text-align:center; position:relative;
  color:#6c757d;
}
.order-step .icon{
  width:44px; height:44px; border-radius:999px; margin:0 auto 6px;
  display:flex; align-items:center; justify-content:center;
  border:2px solid #e9ecef;
  background:#fff;
  font-size:18px;
}
.order-step.active, .order-step.done{ color:#111; }
.order-step.active .icon, .order-step.done .icon{
  border-color:#111;
}
.order-step .label{
  font-size:12px; font-weight:600;
}
.order-step:after{
  content:""; position:absolute; top:22px; right:-9px; width:18px; height:2px;
  background:#e9ecef;
}
.order-step:last-child:after{ display:none; }
.order-step.done:after{ background:#111; }
</style>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h5 m-0">Đơn #<?= (int)$order['id'] ?></h1>
      <div class="text-muted small"><?= e($order['created_at'] ?? '') ?></div>
    </div>
    <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/orders')) ?>">← Quay lại</a>
  </div>

  <!-- ✅ Thanh trạng thái giống hình -->
  <div class="order-stepper mb-3">
    <?php $i=0; foreach ($steps as $key => $info): ?>
      <?php
        $cls = $isDone($i) ? 'done' : '';
        if ($i === $curIndex) $cls = 'active';
        if ($i < $curIndex) $cls = 'done';
      ?>
      <div class="order-step <?= e($cls) ?>">
        <div class="icon"><?= e($info['icon']) ?></div>
        <div class="label"><?= e($info['label']) ?></div>
      </div>
    <?php $i++; endforeach; ?>
  </div>

  <div class="card p-3 mb-3">
    <div class="fw-semibold mb-2">Thông tin nhận hàng</div>
    <div><span class="text-muted">Họ tên:</span> <b><?= e($order['customer_name'] ?? '') ?></b></div>
    <div><span class="text-muted">SĐT:</span> <b><?= e($order['customer_phone'] ?? '') ?></b></div>
    <div><span class="text-muted">Địa chỉ:</span> <b><?= e($order['customer_address'] ?? '') ?></b></div>
    <?php if (!empty($order['note'])): ?>
      <div><span class="text-muted">Ghi chú:</span> <?= e($order['note']) ?></div>
    <?php endif; ?>
  </div>

  <div class="card p-3 mb-3">
    <div class="fw-semibold mb-2">Thanh toán</div>
    <div><span class="text-muted">Phương thức:</span> <b><?= e($paymentLabel($order['payment_method'] ?? '')) ?></b></div>
    <div><span class="text-muted">Trạng thái:</span> <b><?= e($payStatusLabel($order['payment_status'] ?? '')) ?></b></div>
    <?php if (!empty($order['payment_ref'])): ?>
      <div><span class="text-muted">Mã giao dịch:</span> <b><?= e($order['payment_ref']) ?></b></div>
    <?php endif; ?>
  </div>

  <div class="card p-3">
    <div class="fw-semibold mb-2">Sản phẩm</div>

    <div class="vstack gap-2">
      <?php foreach (($order['items'] ?? []) as $it): ?>
        <?php
          $sub = ((int)$it['price']) * ((int)$it['qty']);
          $thumb = $it['product_thumbnail'] ?? null;
          $plink = !empty($it['product_slug']) ? url('/p/'.$it['product_slug']) : '#';
        ?>
        <div class="d-flex justify-content-between align-items-center border rounded p-2">
          <div class="d-flex gap-2 align-items-center">
            <?php if ($thumb): ?>
              <?php
$thumb = $p['thumbnail'] ?? '';
if ($thumb) {
  // nếu là filename (không bắt đầu bằng / hoặc http) -> map vào /uploads/
  if ($thumb[0] !== '/' && !str_starts_with($thumb, 'http')) {
    $thumb = url('/uploads/' . $thumb);
  } else {
    // nếu là /assets/... hoặc /uploads/... thì vẫn gắn base đúng
    $thumb = url($thumb);
  }
} else {
  $thumb = 'https://via.placeholder.com/600x600?text=Product';
}
?>
<img src="<?= e($thumb) ?>" class="card-img-top object-fit-cover" alt="">
                   style="width:48px;height:48px;object-fit:cover;border-radius:8px"
                   onerror="this.style.display='none'">
            <?php endif; ?>
            <div>
              <div class="fw-semibold">
                <a href="<?= e($plink) ?>" class="text-decoration-none">
                  <?= e($it['product_name'] ?? ('SP#'.$it['product_id'])) ?>
                </a>
              </div>
              <div class="text-muted small">
                <?= e($it['size'] ?? '-') ?>/<?= e($it['color'] ?? '-') ?> • x<?= (int)$it['qty'] ?>
              </div>
            </div>
          </div>
          <div class="fw-semibold"><?= money($sub) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <hr class="my-3">
    <div class="d-flex justify-content-between">
      <div class="text-muted">Tổng thanh toán</div>
      <div class="fw-bold fs-5"><?= money((int)$order['total']) ?></div>
    </div>
  </div>
</div>