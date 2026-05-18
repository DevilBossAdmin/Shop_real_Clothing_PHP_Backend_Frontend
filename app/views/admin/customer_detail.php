<?php
// app/views/admin/customer_detail.php
$c = $customer;
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Khách hàng #<?= (int)$c['id'] ?></h4>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/admin/customers')) ?>">← Quay lại</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div><b>Tên:</b> <?= e($c['name'] ?? '') ?></div>
    <div><b>Email:</b> <?= e($c['email'] ?? '') ?></div>
    <div><b>SĐT:</b> <?= e($c['phone'] ?? '') ?></div>
    <div><b>Địa chỉ:</b> <?= e($c['address'] ?? '') ?></div>
    <div><b>Ngày tạo:</b> <?= e($c['created_at'] ?? '') ?></div>
  </div>
</div>

<h5 class="mb-2">Đơn hàng gần đây</h5>
<div class="table-responsive">
  <table class="table table-bordered align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Tổng</th>
        <th>Trạng thái</th>
        <th>Ngày</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($orders)): ?>
        <tr><td colspan="5" class="text-muted">Chưa có đơn hàng.</td></tr>
      <?php else: ?>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= number_format((int)($o['total'] ?? 0)) ?> đ</td>
            <td><?= e($o['status'] ?? '') ?></td>
            <td><?= e($o['created_at'] ?? '') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-dark"
                 href="<?= e(url('/admin/orders/view?id=' . (int)$o['id'])) ?>">
                Xem đơn
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>