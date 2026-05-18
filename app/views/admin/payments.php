<?php // app/views/admin/payments.php ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Thanh toán</h4>
</div>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/payments')) ?>">
  <div class="col-auto">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo ID / tên / SĐT">
  </div>
  <div class="col-auto">
    <select class="form-select" name="status">
      <option value="">-- Tất cả trạng thái đơn --</option>
      <?php
        $opts = ['unpaid'=>'unpaid','paid'=>'paid','processing'=>'processing','shipped'=>'shipped','completed'=>'completed','cancelled'=>'cancelled'];
        $cur = $status ?? '';
      ?>
      <?php foreach ($opts as $k=>$label): ?>
        <option value="<?= e($k) ?>" <?= ($cur===$k?'selected':'') ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto">
    <button class="btn btn-dark">Lọc</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Khách</th>
        <th>SĐT</th>
        <th class="text-end">Tổng</th>
        <th>Phương thức</th>
        <th>TT thanh toán</th>
        <th>Trạng thái đơn</th>
        <th>Ngày</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="9" class="text-muted">Không có dữ liệu.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= e($r['customer_name'] ?? '') ?></td>
            <td><?= e($r['customer_phone'] ?? '') ?></td>
            <td class="text-end"><?= number_format((int)($r['total'] ?? 0)) ?> đ</td>
            <td><?= e($r['payment_method'] ?? '-') ?></td>
            <td><?= e($r['payment_status'] ?? '-') ?></td>
            <td><?= e($r['status'] ?? '-') ?></td>
            <td><?= e($r['created_at'] ?? '') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-dark"
                 href="<?= e(url('/admin/orders/view?id=' . (int)$r['id'])) ?>">
                Xem đơn
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>