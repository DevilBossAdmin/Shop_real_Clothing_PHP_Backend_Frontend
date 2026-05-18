<div class="container">
  <div class="row g-4">
    <div class="col-md-5">
      <div class="ratio ratio-1x1 bg-body-tertiary rounded-4 overflow-hidden">
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
      </div>
    </div>
    <div class="col-md-7">
      <h1 class="h5"><?= e($p['name']) ?></h1>

      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="fs-4 fw-bold"><?= money((int)$p['price']) ?></div>
        <?php if (!empty($p['compare_at_price'])): ?>
          <div class="text-muted text-decoration-line-through"><?= money((int)$p['compare_at_price']) ?></div>
        <?php endif; ?>
        <?php if (!empty($p['sku'])): ?>
          <span class="badge text-bg-light border">SKU: <?= e($p['sku']) ?></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($p['description'])): ?>
        <p class="text-muted"><?= nl2br(e($p['description'])) ?></p>
      <?php endif; ?>

      <div class="card p-3 mb-3">
        <div class="fw-semibold mb-2">Chọn phân loại (size/màu)</div>

        <form class="row g-2" method="post" action="<?= e(url('/cart/add_variant')) ?>">
          <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">

          <div class="col-6">
            <label class="form-label small text-muted">Size</label>
            <select class="form-select" name="size" required>
              <option value="">Chọn size</option>
              <?php foreach ($sizes as $s): ?>
                <option value="<?= e($s) ?>"><?= e($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small text-muted">Màu</label>
            <select class="form-select" name="color" required>
              <option value="">Chọn màu</option>
              <?php foreach ($colors as $c): ?>
                <option value="<?= e($c) ?>"><?= e($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small text-muted">Số lượng</label>
            <input class="form-control" type="number" min="1" name="qty" value="1" required>
          </div>
          <div class="col-12 d-grid">
            <button class="btn btn-dark mt-2">Thêm vào giỏ</button>
          </div>

          <div class="small text-muted mt-2">
            * Demo: chưa khóa theo tồn kho. Bạn có thể mở rộng theo stock.
          </div>
        </form>
      </div>

      <a class="btn btn-outline-secondary" href="<?= e(url('/cart')) ?>">Xem giỏ hàng</a>
    </div>
  </div>

  <div class="mt-4">
    <div class="fw-semibold mb-2">Bảng tồn kho (demo)</div>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead><tr><th>Size</th><th>Màu</th><th>Tồn</th></tr></thead>
        <tbody>
        <?php foreach ($variants as $v): ?>
          <tr><td><?= e($v['size']) ?></td><td><?= e($v['color']) ?></td><td><?= (int)$v['stock'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
