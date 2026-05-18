<?php
$rows = $rows ?? [];
$q = $q ?? '';
$flash = $flash ?? null;
$pager = $pager ?? ['page' => 1, 'pages' => 1];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Quản lý vận đơn</h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/shipping/settings')) ?>">Cấu hình</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/shipping/zones')) ?>">Zone</a>
  </div>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= e(is_array($flash) ? ($flash['type'] ?? 'info') : 'info') ?>">
    <?= e(is_array($flash) ? ($flash['msg'] ?? '') : $flash) ?>
  </div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/shipments')) ?>">
  <div class="col-12 col-md-6">
    <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Tìm theo mã đơn / vận đơn / sđt...">
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary" type="submit">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>Đơn</th>
        <th>Khách</th>
        <th>Phí ship</th>
        <th>Vận đơn</th>
        <th>Tracking</th>
        <th class="text-end">Gán / Theo dõi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">Chưa có vận đơn nào</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <?php
            $shipmentId = (int)($r['id'] ?? 0);
            $oid = (int)($r['order_id'] ?? 0);
            $code = $r['tracking_code'] ?? $r['s_tracking_code'] ?? $r['shipping_tracking_code'] ?? null;
            $carrier = $r['carrier'] ?? $r['s_carrier'] ?? $r['shipping_carrier'] ?? 'manual';
            $st = $r['status'] ?? $r['s_status'] ?? $r['shipping_status'] ?? 'created';
          ?>
          <tr>
            <td>
              <b>#<?= $oid ?></b>
              <div class="small text-muted"><?= e($r['order_status'] ?? '') ?></div>
            </td>

            <td>
              <div class="fw-semibold"><?= e($r['customer_name'] ?? '-') ?></div>
              <div class="small text-muted"><?= e($r['customer_phone'] ?? '-') ?></div>
            </td>

            <td class="fw-semibold"><?= money((int)($r['shipping_fee'] ?? 0)) ?></td>

            <td class="small">
              <div>Carrier: <b><?= e($carrier ?: '-') ?></b></div>
              <div>Code: <b><?= e($code ?: '-') ?></b></div>
            </td>

            <td>
              <span class="badge text-bg-secondary"><?= e($st) ?></span>
            </td>

            <td class="text-end">
              <form class="d-inline-flex gap-2" method="post" action="<?= e(url('/admin/shipments/update')) ?>">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= $shipmentId ?>">

                <select class="form-select form-select-sm" name="carrier">
                  <option value="manual" <?= $carrier === 'manual' ? 'selected' : '' ?>>Manual</option>
                  <option value="ghn" <?= $carrier === 'ghn' ? 'selected' : '' ?>>GHN</option>
                  <option value="ghtk" <?= $carrier === 'ghtk' ? 'selected' : '' ?>>GHTK</option>
                  <option value="vnpay" <?= $carrier === 'vnpay' ? 'selected' : '' ?>>VNPAY</option>
                </select>

                <input
                  class="form-control form-control-sm"
                  name="tracking_code"
                  placeholder="Mã vận đơn"
                  value="<?= e($code ?? '') ?>"
                  style="width:140px"
                >

                <select class="form-select form-select-sm" name="status">
                  <?php foreach (['created','confirmed','picking','shipping','delivered','cancelled','returned'] as $statusOpt): ?>
                    <option value="<?= e($statusOpt) ?>" <?= $st === $statusOpt ? 'selected' : '' ?>>
                      <?= e($statusOpt) ?>
                    </option>
                  <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-sm btn-outline-dark">Lưu</button>
              </form>

              <form class="d-inline" method="post" action="<?= e(url('/admin/shipments/track')) ?>">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= $shipmentId ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary" <?= empty($code) ? 'disabled' : '' ?>>
                  Theo dõi
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if (($pager['pages'] ?? 1) > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php for ($i = 1; $i <= (int)($pager['pages'] ?? 1); $i++): ?>
        <?php
          $qs = $_GET;
          $qs['page'] = $i;
        ?>
        <li class="page-item <?= ($i === (int)($pager['page'] ?? 1) ? 'active' : '') ?>">
          <a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>