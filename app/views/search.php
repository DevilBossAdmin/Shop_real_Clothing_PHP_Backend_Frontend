<div class="container">
  <h1 class="h5 mb-3">Tìm kiếm</h1>

  <form class="row g-2 mb-3" method="get" action="<?= e(url('/search')) ?>">
    <div class="col-12 col-md-10">
      <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Nhập tên sản phẩm...">
    </div>
    <div class="col-12 col-md-2 d-grid">
      <button class="btn btn-dark">Tìm</button>
    </div>
  </form>

  <?php if ($q !== '' && !$products): ?>
    <div class="alert alert-warning">Không tìm thấy sản phẩm phù hợp.</div>
  <?php endif; ?>

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
<img src="<?= e($thumb) ?>" class="card-img-top object-fit-cover" alt="">
          </a>
          <div class="card-body">
            <div class="fw-semibold small mb-1"><?= e($p['name']) ?></div>
            <div class="fw-bold"><?= money((int)$p['price']) ?></div>
            <a class="btn btn-dark btn-sm mt-2" href="<?= e(url('/p/'.$p['slug'])) ?>">Chọn phân loại</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($pager['pages'] > 1): ?>
    <nav class="mt-4">
      <ul class="pagination">
        <?php for ($i=1; $i<=$pager['pages']; $i++): ?>
          <?php
            $qs = $_GET;
            $qs['page'] = $i;
            $link = '?' . http_build_query($qs);
          ?>
          <li class="page-item <?= ($i===$pager['page']?'active':'') ?>">
            <a class="page-link" href="<?= e($link) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>
