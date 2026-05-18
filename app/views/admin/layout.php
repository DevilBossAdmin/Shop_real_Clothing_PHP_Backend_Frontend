<?php
$cfg = require __DIR__ . '/../../config.php';
$admin = admin_user();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin • <?= e($cfg['site']['name'] ?? 'Shop') ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= e(url('/assets/css/style.css')) ?>">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= e(url('/admin')) ?>">Admin</a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/')) ?>">Về shop</a>
      <?php if ($admin): ?>
        <a class="btn btn-outline-danger btn-sm" href="<?= e(url('/admin/logout')) ?>">Đăng xuất</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="row g-3">

    <!-- SIDEBAR -->
    <div class="col-lg-3">
      <div class="list-group shadow-sm">
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin')) ?>">Dashboard</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/products')) ?>">Sản phẩm</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/categories')) ?>">Danh mục</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/orders')) ?>">Đơn hàng</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/customers')) ?>">Khách hàng</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/inventory')) ?>">Tồn kho</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/inventory/history')) ?>">Lịch sử nhập/xuất</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/payments')) ?>">Thanh toán</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/settings/payment')) ?>">Cấu hình nhận tiền</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/shipping/settings')) ?>">Vận chuyển</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/shipping/zones')) ?>">Zone ship</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/shipments')) ?>">Vận đơn</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/reports')) ?>">Báo cáo</a>
        <a class="list-group-item list-group-item-action" href="<?= e(url('/admin/backup')) ?>">Backup dữ liệu</a>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="col-lg-9">

      <?php if (!empty($flash)): ?>
        <?php
          // $flash có thể là string hoặc array (type/msg) tuỳ bạn set
          $flashType = 'success';
          $flashMsg = '';

          if (is_array($flash)) {
            $flashType = $flash['type'] ?? 'success';
            $flashMsg  = $flash['msg'] ?? '';
          } else {
            $flashMsg = (string)$flash;
          }
        ?>
        <?php if ($flashMsg !== ''): ?>
          <div class="alert alert-<?= e($flashType) ?>"><?= e($flashMsg) ?></div>
        <?php endif; ?>
      <?php endif; ?>

      <?php
        // $view_file được set ở render_admin()
        // ví dụ: render_admin(__DIR__.'/../app/views/admin/categories.php', ...)
        require $view_file;
      ?>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>