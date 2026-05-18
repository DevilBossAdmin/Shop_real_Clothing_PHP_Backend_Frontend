<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Giỏ hàng</h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/')) ?>">Tiếp tục mua</a>
  </div>

  <?php if (!$cart): ?>
    <div class="alert alert-info">Chưa có sản phẩm nào trong giỏ.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Phân loại</th>
            <th class="text-end">Giá</th>
            <th style="width:180px;">Số lượng</th>
            <th class="text-end">Tạm tính</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php $total = 0; ?>
        <?php foreach ($cart as $key => $row): ?>
          <?php $id = (int)$row['product_id']; ?>
          <?php $p = $map[$id] ?? null; if (!$p) continue; ?>
          <?php $qty = (int)$row['qty']; ?>
          <?php $sub = (int)$p['price'] * $qty; $total += $sub; ?>

          <?php
            $thumb = $p['thumbnail'] ?? '';
            if ($thumb) {
              if ($thumb[0] !== '/' && !str_starts_with($thumb, 'http')) {
                $thumb = url('/uploads/' . $thumb);
              } else {
                $thumb = url($thumb);
              }
            } else {
              $thumb = 'https://via.placeholder.com/120';
            }
          ?>

          <tr>
            <td>
              <div class="d-flex gap-3 align-items-center">
                <img src="<?= e($thumb) ?>" width="56" height="56" class="rounded object-fit-cover" alt="<?= e($p['name']) ?>">
                <div>
                  <div class="fw-semibold"><?= e($p['name']) ?></div>
                  <a class="small text-decoration-none" href="<?= e(url('/p/' . $p['slug'])) ?>">Xem chi tiết</a>
                </div>
              </div>
            </td>

            <td>
              <span class="badge text-bg-light border"><?= e($row['size'] ?? '-') ?></span>
              <span class="badge text-bg-light border"><?= e($row['color'] ?? '-') ?></span>
            </td>

            <td class="text-end"><?= money((int)$p['price']) ?></td>

            <td>
              <form class="d-flex gap-2" method="post" action="<?= e(url('/cart/update')) ?>">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="key" value="<?= e($key) ?>">
                <input class="form-control form-control-sm" type="number" min="1" name="qty" value="<?= $qty ?>">
                <button class="btn btn-outline-secondary btn-sm">Cập nhật</button>
              </form>
            </td>

            <td class="text-end fw-semibold"><?= money($sub) ?></td>

            <td class="text-end">
              <a class="btn btn-outline-danger btn-sm" href="<?= e(url('/cart/remove?key=' . urlencode($key))) ?>">Xóa</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-end">
      <div class="card p-3 w-100" style="max-width: 420px;">
        <div class="d-flex justify-content-between">
          <div class="text-muted">Tổng cộng</div>
          <div class="fw-bold fs-5"><?= money($total) ?></div>
        </div>

        <a class="btn btn-dark mt-3" href="index.php?r=/checkout">Tiến hành thanh toán</a>
      </div>
    </div>
  <?php endif; ?>
</div>