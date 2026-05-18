<?php
$from = $from ?? date('Y-m-01');
$to = $to ?? date('Y-m-d');
$groupBy = $groupBy ?? 'day';

$revenueTotal = (int)($revenueTotal ?? 0);
$revenueSeries = $revenueSeries ?? [];
$topProducts = $topProducts ?? [];
$inventorySummary = $inventorySummary ?? [];
$profit = $profit ?? ['revenue' => 0, 'cost' => 0, 'profit' => 0];
$newCustomers = (int)($newCustomers ?? 0);
$cancelRate = $cancelRate ?? ['total_orders' => 0, 'cancelled_orders' => 0, 'rate' => 0];
$orderStatusPie = $orderStatusPie ?? [];

$barLabels = [];
$barValues = [];
foreach ($revenueSeries as $row) {
    $barLabels[] = $row['label'] ?? '';
    $barValues[] = (int)($row['revenue'] ?? 0);
}

$pieLabels = [];
$pieValues = [];
$pieTotal = 0;
foreach ($orderStatusPie as $row) {
    $pieLabels[] = $row['status'] ?? '';
    $pieValues[] = (int)($row['total'] ?? 0);
    $pieTotal += (int)($row['total'] ?? 0);
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Báo cáo tổng quan</h1>
  <a
    href="<?= e(url('/admin/reports/export?from=' . urlencode($from) . '&to=' . urlencode($to) . '&group_by=' . urlencode($groupBy))) ?>"
    class="btn btn-outline-secondary btn-sm"
  >
    Tải báo cáo
  </a>
</div>

<form method="get" action="<?= e(url('/admin/reports')) ?>" class="card p-3 mb-4">
  <div class="row g-3">
    <div class="col-12 col-md-3">
      <label class="form-label">Từ ngày</label>
      <input type="date" name="from" class="form-control" value="<?= e($from) ?>">
    </div>

    <div class="col-12 col-md-3">
      <label class="form-label">Đến ngày</label>
      <input type="date" name="to" class="form-control" value="<?= e($to) ?>">
    </div>

    <div class="col-12 col-md-3">
      <label class="form-label">Gộp dữ liệu</label>
      <select name="group_by" class="form-select">
        <option value="day" <?= $groupBy === 'day' ? 'selected' : '' ?>>Theo ngày</option>
        <option value="month" <?= $groupBy === 'month' ? 'selected' : '' ?>>Theo tháng</option>
      </select>
    </div>

    <div class="col-12 col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-dark w-100">Xem báo cáo</button>
    </div>
  </div>
</form>

<div class="row g-3 mb-4">
  <div class="col-12 col-md-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Doanh thu hoàn tất</div>
        <div class="fs-4 fw-bold"><?= money($revenueTotal) ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Lợi nhuận tạm tính</div>
        <div class="fs-4 fw-bold"><?= money((int)($profit['profit'] ?? 0)) ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Khách hàng mới</div>
        <div class="fs-4 fw-bold"><?= (int)$newCustomers ?></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Tỷ lệ hủy đơn</div>
        <div class="fs-4 fw-bold"><?= e((string)($cancelRate['rate'] ?? 0)) ?>%</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <strong>Biểu đồ cột doanh thu</strong>
      </div>
      <div class="card-body">
        <?php if (empty($barLabels)): ?>
          <div class="text-muted">Không có dữ liệu doanh thu trong khoảng thời gian này.</div>
        <?php else: ?>
          <canvas id="revenueBarChart" height="110"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <strong>Biểu đồ tròn trạng thái đơn</strong>
      </div>
      <div class="card-body">
        <?php if (empty($pieLabels)): ?>
          <div class="text-muted">Không có dữ liệu trạng thái đơn.</div>
        <?php else: ?>
          <canvas id="orderStatusPieChart" height="220"></canvas>
          <div class="mt-3 small">
            <?php foreach ($orderStatusPie as $row): ?>
              <?php
                $count = (int)($row['total'] ?? 0);
                $percent = $pieTotal > 0 ? round(($count / $pieTotal) * 100, 2) : 0;
              ?>
              <div class="d-flex justify-content-between border-bottom py-1">
                <span><?= e($row['status'] ?? '-') ?></span>
                <span><?= $count ?> đơn - <?= $percent ?>%</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-12 col-lg-7">
    <div class="card shadow-sm">
      <div class="card-header">
        <strong>Top sản phẩm bán chạy</strong>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>SKU</th>
                <th>SL bán</th>
                <th>Doanh thu</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($topProducts)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-3">Không có dữ liệu</td>
                </tr>
              <?php else: ?>
                <?php foreach ($topProducts as $p): ?>
                  <tr>
                    <td><?= e($p['product_name'] ?? '-') ?></td>
                    <td><?= e($p['sku'] ?? '-') ?></td>
                    <td><?= (int)($p['total_qty'] ?? 0) ?></td>
                    <td><?= money((int)($p['total_revenue'] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-header">
        <strong>Tồn kho thấp</strong>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>SKU</th>
                <th>Tồn</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($inventorySummary)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-3">Không có dữ liệu</td>
                </tr>
              <?php else: ?>
                <?php foreach ($inventorySummary as $inv): ?>
                  <tr>
                    <td><?= e($inv['name'] ?? '-') ?></td>
                    <td><?= e($inv['sku'] ?? '-') ?></td>
                    <td><?= (int)($inv['total_stock'] ?? 0) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header">
        <strong>Thông tin thêm</strong>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-4">
            <div><strong>Tổng đơn trong kỳ:</strong> <?= (int)($cancelRate['total_orders'] ?? 0) ?></div>
          </div>
          <div class="col-12 col-md-4">
            <div><strong>Đơn bị hủy:</strong> <?= (int)($cancelRate['cancelled_orders'] ?? 0) ?></div>
          </div>
          <div class="col-12 col-md-4">
            <div><strong>Giá vốn tạm tính:</strong> <?= money((int)($profit['cost'] ?? 0)) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const revenueBarLabels = <?= json_encode($barLabels, JSON_UNESCAPED_UNICODE) ?>;
  const revenueBarValues = <?= json_encode($barValues, JSON_UNESCAPED_UNICODE) ?>;

  const pieLabels = <?= json_encode($pieLabels, JSON_UNESCAPED_UNICODE) ?>;
  const pieValues = <?= json_encode($pieValues, JSON_UNESCAPED_UNICODE) ?>;

  const revenueCtx = document.getElementById('revenueBarChart');
  if (revenueCtx && revenueBarLabels.length > 0) {
    new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: revenueBarLabels,
        datasets: [{
          label: 'Doanh thu',
          data: revenueBarValues,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.raw) + ' đ';
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }

  const pieCtx = document.getElementById('orderStatusPieChart');
  if (pieCtx && pieLabels.length > 0) {
    new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: pieLabels,
        datasets: [{
          data: pieValues
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                const value = context.raw || 0;
                const percent = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                return context.label + ': ' + value + ' đơn (' + percent + '%)';
              }
            }
          }
        }
      }
    });
  }
</script>