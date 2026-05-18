<div class="container">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <div>
      <div class="text-muted small">Danh mục</div>
      <h1 class="h5 mb-0"><?= e($cat['name']) ?></h1>
    </div>
  </div>

  <div class="card p-3 mb-3">
    <form class="row g-2" method="get" action="">
      <div class="col-6 col-md-4">
        <label class="form-label small text-muted">Size</label>
        <select class="form-select" name="size">
          <option value="">Tất cả</option>
          <?php foreach ($sizes as $s): ?>
            <option value="<?= e($s) ?>" <?= ($filters['size'] === $s ? 'selected' : '') ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-6 col-md-4">
        <label class="form-label small text-muted">Màu</label>
        <select class="form-select" name="color">
          <option value="">Tất cả</option>
          <?php foreach ($colors as $c): ?>
            <option value="<?= e($c) ?>" <?= ($filters['color'] === $c ? 'selected' : '') ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 col-md-4 d-grid align-items-end">
        <button class="btn btn-dark mt-4 mt-md-0">Lọc</button>
      </div>
    </form>
  </div>

  <?php if (!$products): ?>
    <div class="alert alert-info">Chưa có sản phẩm phù hợp.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($products as $p): ?>
        <?php
          $thumb = $p['thumbnail'] ?? '';

          if ($thumb) {
              if ($thumb[0] !== '/' && !str_starts_with($thumb, 'http')) {
                  $thumb = url('/uploads/' . $thumb);
              } else {
                  $thumb = url($thumb);
              }
          } else {
              $thumb = 'https://via.placeholder.com/600x600?text=Product';
          }
        ?>

        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100 product-card">
            <a href="<?= e(url('/p/' . $p['slug'])) ?>" class="ratio ratio-1x1 bg-body-tertiary">
              <img src="<?= e($thumb) ?>" class="card-img-top object-fit-cover" alt="<?= e($p['name']) ?>">
            </a>

            <div class="card-body">
              <div class="fw-semibold small mb-1"><?= e($p['name']) ?></div>
              <div class="fw-bold"><?= money((int)$p['price']) ?></div>

              <div class="d-flex gap-2 mt-2 flex-wrap">
                <a class="btn btn-dark btn-sm" href="<?= e(url('/cart/add?id=' . $p['id'])) ?>">Thêm vào giỏ</a>
                <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/p/' . $p['slug'])) ?>">Mua nhanh</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($pager['pages'] > 1): ?>
      <nav class="mt-4">
        <ul class="pagination">
          <?php for ($i = 1; $i <= $pager['pages']; $i++): ?>
            <?php
              $qs = $_GET;
              $qs['page'] = $i;
              $link = '?' . http_build_query($qs);
            ?>
            <li class="page-item <?= ($i === $pager['page'] ? 'active' : '') ?>">
              <a class="page-link" href="<?= e($link) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>