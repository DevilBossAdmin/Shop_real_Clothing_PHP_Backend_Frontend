<?php
$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$lowStockProducts = $lowStockProducts ?? [];

$statusLabel = function($st) {
  return match($st) {
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã huỷ',
    default     => $st ?: 'N/A'
  };
};

$shippingStatusLabel = function($st) {
  return match($st) {
    'picking'   => 'Đang lấy hàng',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'returned'  => 'Trả hàng',
    'cancelled' => 'Đã huỷ',
    default     => $st ?: 'N/A'
  };
};

$payStatusLabel = function($st) {
  return match($st) {
    'paid'   => 'Đã thanh toán',
    'unpaid' => 'Chưa thanh toán',
    'failed' => 'Thất bại',
    default  => $st ?: 'N/A'
  };
};

$payMethodLabel = function($st) {
  return match($st) {
    'cod'           => 'COD',
    'bank_transfer' => 'Chuyển khoản',
    'qr'            => 'QR',
    'bank'          => 'Ngân hàng',
    'card'          => 'Thẻ',
    default         => $st ?: 'N/A'
  };
};
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Dashboard</h3>
  </div>

  <?php if (!empty($flash)): ?>
    <?php
      $type = is_array($flash) ? ($flash['type'] ?? 'info') : 'info';
      $msg  = is_array($flash) ? ($flash['msg'] ?? '') : (string)$flash;
      $allow = ['primary','secondary','success','danger','warning','info','light','dark'];
      if (!in_array($type, $allow, true)) $type = 'info';
    ?>
    <div class="alert alert-<?= e($type) ?>"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Sản phẩm</div>
          <div class="fs-2 fw-bold"><?= (int)($stats['products'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Danh mục</div>
          <div class="fs-2 fw-bold"><?= (int)($stats['categories'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Đơn hàng</div>
          <div class="fs-2 fw-bold"><?= (int)($stats['orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Khách hàng</div>
          <div class="fs-2 fw-bold"><?= (int)($stats['users'] ?? 0) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-2">
      <div class="card border-warning h-100">
        <div class="card-body">
          <div class="text-muted small">Chờ xác nhận</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['pending_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card border-primary h-100">
        <div class="card-body">
          <div class="text-muted small">Đã xác nhận</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['confirmed_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card border-info h-100">
        <div class="card-body">
          <div class="text-muted small">Đang giao</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['shipping_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card border-success h-100">
        <div class="card-body">
          <div class="text-muted small">Hoàn thành</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['completed_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card border-danger h-100">
        <div class="card-body">
          <div class="text-muted small">Đã huỷ</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['cancelled_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-2">
      <div class="card border-dark h-100">
        <div class="card-body">
          <div class="text-muted small">Doanh thu</div>
          <div class="fs-6 fw-bold"><?= money((int)($stats['revenue'] ?? 0)) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-success h-100">
        <div class="card-body">
          <div class="text-muted small">Đã thanh toán</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['paid_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card border-secondary h-100">
        <div class="card-body">
          <div class="text-muted small">Chưa thanh toán</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['unpaid_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card border-danger h-100">
        <div class="card-body">
          <div class="text-muted small">Thanh toán thất bại</div>
          <div class="fs-4 fw-bold"><?= (int)($stats['failed_orders'] ?? 0) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-semibold">Đơn hàng mới nhất</div>
        <div class="card-body p-0">
          <?php if (empty($recentOrders)): ?>
            <div class="p-3 text-muted">Chưa có đơn hàng nào.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table mb-0 align-middle">
                <thead>
                  <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Tổng</th>
                    <th>Trạng thái đơn</th>
                    <th>Vận chuyển</th>
                    <th>Thanh toán</th>
                    <th>PTTT</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recentOrders as $o): ?>
                    <tr>
                      <td>#<?= (int)$o['id'] ?></td>
                      <td><?= e($o['customer_name'] ?? '') ?></td>
                      <td><?= e($o['customer_phone'] ?? '') ?></td>
                      <td class="fw-semibold"><?= money((int)($o['total'] ?? 0)) ?></td>
                      <td><?= e($statusLabel($o['status'] ?? '')) ?></td>
                      <td><?= e($shippingStatusLabel($o['shipping_status'] ?? '')) ?></td>
                      <td><?= e($payStatusLabel($o['payment_status'] ?? '')) ?></td>
                      <td><?= e($payMethodLabel($o['payment_method'] ?? '')) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-semibold">Sản phẩm sắp hết hàng</div>
        <div class="card-body p-0">
          <?php if (empty($lowStockProducts)): ?>
            <div class="p-3 text-muted">Không có sản phẩm tồn kho thấp.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table mb-0 align-middle">
                <thead>
                  <tr>
                    <th>Tên sản phẩm</th>
                    <th>SKU</th>
                    <th>Tồn</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lowStockProducts as $p): ?>
                    <tr>
                      <td><?= e($p['name'] ?? '') ?></td>
                      <td><?= e($p['sku'] ?? '') ?></td>
                      <td class="fw-bold <?= ((int)$p['total_stock'] <= 5 ? 'text-danger' : 'text-warning') ?>">
                        <?= (int)$p['total_stock'] ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>