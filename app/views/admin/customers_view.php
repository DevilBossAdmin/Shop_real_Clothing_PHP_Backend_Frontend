<?php
$customer = $customer ?? [];
$orders = $orders ?? [];
$flash = $flash ?? null;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Chi tiết khách hàng</h1>
  <a href="<?= e(url('/admin/customers')) ?>" class="btn btn-outline-secondary btn-sm">Quay lại</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= e(is_array($flash) ? ($flash['type'] ?? 'success') : 'success') ?>">
    <?= e(is_array($flash) ? ($flash['msg'] ?? '') : $flash) ?>
  </div>
<?php endif; ?>

<div class="card mb-4">
  <div class="card-body">
    <div class="mb-2"><strong>ID:</strong> #<?= (int)($customer['id'] ?? 0) ?></div>
    <div class="mb-2"><strong>Họ tên:</strong> <?= e($customer['name'] ?? '-') ?></div>
    <div class="mb-2"><strong>Email:</strong> <?= e($customer['email'] ?? '-') ?></div>
    <div class="mb-2"><strong>SĐT:</strong> <?= e($customer['phone'] ?? '-') ?></div>
    <div class="mb-2"><strong>Địa chỉ:</strong> <?= e($customer['address'] ?? '-') ?></div>
    <div class="mb-2"><strong>Ngày tạo:</strong> <?= e($customer['created_at'] ?? '-') ?></div>
    <div>
      <strong>Trạng thái:</strong>
      <?php if ((int)($customer['is_locked'] ?? 0) === 1): ?>
        <span class="badge text-bg-danger">Đã khóa</span>
      <?php else: ?>
        <span class="badge text-bg-success">Hoạt động</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <strong>Đơn hàng gần đây</strong>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tổng</th>
            <th>Thanh toán</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-3">Khách hàng chưa có đơn hàng</td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td>#<?= (int)$o['id'] ?></td>
                <td><?= money((int)($o['total'] ?? 0)) ?></td>
                <td>
                  <?= e($o['payment_method'] ?? '-') ?> /
                  <?= e($o['payment_status'] ?? '-') ?>
                </td>
                <td><?= e($o['status'] ?? '-') ?></td>
                <td><?= e($o['created_at'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>