<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Quản lý tồn kho</h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/inventory/history')) ?>">Lịch sử nhập/xuất</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/inventory')) ?>">
  <div class="col-12 col-md-6">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo tên/SP/SKU...">
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>SP</th>
        <th>Variant</th>
        <th class="text-end">Tồn</th>
        <th style="width:420px">Nhập/Xuất/Điều chỉnh</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($r['product_name']) ?></div>
            <div class="small text-muted">SKU: <?= e($r['product_sku'] ?? '-') ?> • #<?= (int)$r['product_id'] ?></div>
          </td>
          <td class="small">
            Size: <b><?= e($r['size'] ?? '-') ?></b> • Màu: <b><?= e($r['color'] ?? '-') ?></b><br>
            Variant ID: <b>#<?= (int)$r['variant_id'] ?></b>
          </td>
          <td class="text-end fw-semibold"><?= (int)$r['stock'] ?></td>

          <td>
            <form class="row g-2" method="post" action="<?= e(url('/admin/inventory/move')) ?>">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="product_id" value="<?= (int)$r['product_id'] ?>">
              <input type="hidden" name="variant_id" value="<?= (int)$r['variant_id'] ?>">

              <div class="col-4">
                <select class="form-select form-select-sm" name="type">
                  <option value="in">Nhập</option>
                  <option value="out">Xuất</option>
                  <option value="adjust">Điều chỉnh (+/-)</option>
                </select>
              </div>

              <div class="col-3">
                <input class="form-control form-control-sm" name="qty" type="number" placeholder="SL" required>
              </div>

              <div class="col-5">
                <input class="form-control form-control-sm" name="note" placeholder="Ghi chú (vd: Nhập từ NCC)">
              </div>

              <div class="col-12 d-grid">
                <button class="btn btn-sm btn-outline-dark"
                        onclick="return confirm('Xác nhận tạo phiếu kho cho variant #<?= (int)$r['variant_id'] ?>?');">
                  Thực hiện
                </button>
              </div>
            </form>

            <div class="small text-muted mt-1">
              * “Điều chỉnh” cho phép nhập số âm (vd -2).
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($pager['pages'] > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php for ($i=1; $i<=$pager['pages']; $i++): ?>
        <?php $qs = $_GET; $qs['page']=$i; ?>
        <li class="page-item <?= ($i===$pager['page']?'active':'') ?>">
          <a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>