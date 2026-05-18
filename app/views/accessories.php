<?php // app/views/accessories.php ?>
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Phụ kiện</h3>
    <div class="text-muted small">Tổng: <?= (int)($total ?? 0) ?> sản phẩm</div>
  </div>

  <?php if (empty($products)): ?>
    <div class="alert alert-info">Chưa có sản phẩm phụ kiện.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($products as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100">
            <?php if (!empty($p['thumbnail'])): ?>
              <a href="<?= e(url('/product?slug=' . ($p['slug'] ?? ''))) ?>">
                <img src="<?= e($p['thumbnail']) ?>" class="card-img-top" alt="<?= e($p['name'] ?? '') ?>" style="object-fit:cover; height:180px;">
              </a>
            <?php endif; ?>

            <div class="card-body">
              <div class="fw-semibold" style="min-height:44px;">
                <a class="text-decoration-none text-dark" href="<?= e(url('/product?slug=' . ($p['slug'] ?? ''))) ?>">
                  <?= e($p['name'] ?? '') ?>
                </a>
              </div>

              <div class="mt-2">
                <span class="fw-bold"><?= number_format((int)($p['price'] ?? 0)) ?> đ</span>
                <?php if (!empty($p['compare_at_price']) && (int)$p['compare_at_price'] > (int)$p['price']): ?>
                  <span class="text-muted text-decoration-line-through ms-2">
                    <?= number_format((int)$p['compare_at_price']) ?> đ
                  </span>
                <?php endif; ?>
              </div>

              <div class="d-flex gap-2 mt-3">
                <a class="btn btn-outline-dark btn-sm w-100" href="<?= e(url('/product?slug=' . ($p['slug'] ?? ''))) ?>">
                  Xem
                </a>

                <!-- Nếu project bạn có add-to-cart theo GET -->
                <a class="btn btn-dark btn-sm w-100"
                   href="<?= e(url('/cart/add?id=' . (int)($p['id'] ?? 0) . '&qty=1')) ?>">
                  Mua
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (($pages ?? 1) > 1): ?>
      <nav class="mt-4">
        <ul class="pagination">
          <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= ($i === (int)$page ? 'active' : '') ?>">
              <a class="page-link" href="<?= e(url('/phu-kien?page=' . $i)) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>