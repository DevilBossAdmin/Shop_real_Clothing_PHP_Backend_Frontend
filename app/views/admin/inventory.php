<?php // app/views/admin/inventory.php ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Tồn kho</h4>
</div>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/inventory')) ?>">
  <div class="col-auto">
    <!-- GET không cần CSRF, nhưng giữ cũng không sao -->
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo tên / SKU">
  </div>
  <div class="col-auto">
    <button class="btn btn-dark">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>SP</th>
        <th>SKU</th>
        <th>Variant</th>
        <th class="text-end">Tồn</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="4" class="text-muted">Không có dữ liệu tồn kho.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <?php
            // ✅ Hỗ trợ cả 2 dạng:
            // - inventoryList(): id, name, sku, stock
            // - adminInventoryFallback(): product_id, product_name, product_sku, size, color, stock
            $pid  = (int)($r['product_id'] ?? $r['id'] ?? 0);
            $name = (string)($r['product_name'] ?? $r['name'] ?? '');
            $sku  = (string)($r['product_sku'] ?? $r['sku'] ?? '');

            $size  = trim((string)($r['size'] ?? ''));
            $color = trim((string)($r['color'] ?? ''));

            $variant = '';
            if ($size !== '' || $color !== '') {
              $variant = $size;
              if ($color !== '') $variant .= ($variant !== '' ? ' / ' : '') . $color;
            }

            $stock = (int)($r['stock'] ?? 0);
          ?>

          <tr>
            <td>
              #<?= $pid ?> - <?= e($name) ?>
            </td>
            <td><?= e($sku) ?></td>
            <td><?= e($variant !== '' ? $variant : '-') ?></td>
            <td class="text-end"><?= $stock ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>