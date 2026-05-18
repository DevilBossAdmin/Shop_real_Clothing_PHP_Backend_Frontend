<div class="container">
  <div class="hero p-4 p-md-5 rounded-4 bg-body-tertiary mb-4">
    <div class="row g-3 align-items-center">
      <div class="col-md-7">
        <h1 class="h3 fw-bold mb-2">Nâng tầm phong cách của bạn</h1>
        <p class="text-muted mb-3">Phong cách là cách để nói lên bạn là ai mà không cần lời nói</p>
        <a class="btn btn-dark" href="<?= e(url('/search')) ?>">Xem sản phẩm</a>
        <a class="btn btn-outline-secondary ms-2" href="<?= e(url('/cart')) ?>">Giỏ hàng</a>
      </div>
      <div class="col-md-5 text-md-end">
        <div class="badge text-bg-dark p-3">NEW ARRIVALS</div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Sản phẩm mới</h2>
    <a class="text-decoration-none" href="<?= e(url('/search')) ?>">Xem tất cả</a>
  </div>

  <div class="row g-3">
    <?php foreach ($products as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100 product-card">
          <a href="<?= e(url('/p/'.$p['slug'])) ?>" class="ratio ratio-1x1 bg-body-tertiary">
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
          </a>
          <div class="card-body">
            <div class="fw-semibold small mb-1"><?= e($p['name']) ?></div>
            <div class="d-flex align-items-center gap-2">
              <div class="fw-bold"><?= money((int)$p['price']) ?></div>
              <?php if (!empty($p['compare_at_price'])): ?>
                <div class="text-muted small text-decoration-line-through"><?= money((int)$p['compare_at_price']) ?></div>
              <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-2">
              <a class="btn btn-dark btn-sm" href="<?= e(url('/cart/add?id='.$p['id'])) ?>">Mua nhanh</a>
              <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/p/'.$p['slug'])) ?>">Chi tiết</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
