<?php // inventory_history.php ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Lịch sử nhập / xuất kho</h4>
</div>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/inventory/history')) ?>">
  <div class="col-auto">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo tên sản phẩm">
  </div>
  <div class="col-auto">
    <button class="btn btn-dark">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Sản phẩm</th>
        <th>Loại</th>
        <th>Số lượng</th>
        <th>Ghi chú</th>
        <th>Ngày</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-muted">Chưa có dữ liệu.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= e($r['product_name']) ?></td>
            <td>
              <?= $r['type'] === 'in' ? 'Nhập kho' : 'Xuất kho' ?>
            </td>
            <td><?= (int)$r['qty'] ?></td>
            <td><?= e($r['note'] ?? '') ?></td>
            <td><?= e($r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>