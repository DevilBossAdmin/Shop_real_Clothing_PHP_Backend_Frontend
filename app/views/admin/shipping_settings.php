<?php // app/views/admin/shipping_settings.php ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Cấu hình vận chuyển</h4>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('/admin/shipping/settings/submit')) ?>" class="card">
  <div class="card-body">
    <?= csrf_field(); ?>

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Phí vận chuyển mặc định (đ)</label>
        <input class="form-control" type="number" name="default_fee" value="<?= e((string)($settings['default_fee'] ?? 0)) ?>" min="0">
      </div>

      <div class="col-md-4">
        <label class="form-label">Miễn phí vận chuyển từ (đ)</label>
        <input class="form-control" type="number" name="free_ship_min" value="<?= e((string)($settings['free_ship_min'] ?? 0)) ?>" min="0">
      </div>

      <div class="col-md-4">
        <label class="form-label">GHN hoạt động</label>
        <select class="form-select" name="ghn_enabled">
          <option value="1" <?= ((int)($settings['ghn_enabled'] ?? 0) === 1 ? 'selected' : '') ?>>Bật</option>
          <option value="0" <?= ((int)($settings['ghn_enabled'] ?? 0) === 0 ? 'selected' : '') ?>>Tắt</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">GHN Base URL</label>
        <input class="form-control" name="ghn_base_url" value="<?= e($settings['ghn_base_url'] ?? '') ?>" placeholder="https://dev-online-gateway.ghn.vn">
      </div>

      <div class="col-md-3">
        <label class="form-label">GHN Token</label>
        <input class="form-control" name="ghn_token" value="<?= e($settings['ghn_token'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">GHN Shop ID</label>
        <input class="form-control" name="ghn_shop_id" value="<?= e($settings['ghn_shop_id'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">GHTK hoạt động</label>
        <select class="form-select" name="ghtk_enabled">
          <option value="1" <?= ((int)($settings['ghtk_enabled'] ?? 0) === 1 ? 'selected' : '') ?>>Bật</option>
          <option value="0" <?= ((int)($settings['ghtk_enabled'] ?? 0) === 0 ? 'selected' : '') ?>>Tắt</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">GHTK Base URL</label>
        <input class="form-control" name="ghtk_base_url" value="<?= e($settings['ghtk_base_url'] ?? '') ?>" placeholder="https://services.giaohangtietkiem.vn">
      </div>

      <div class="col-md-4">
        <label class="form-label">GHTK Token</label>
        <input class="form-control" name="ghtk_token" value="<?= e($settings['ghtk_token'] ?? '') ?>">
      </div>
    </div>

    <button class="btn btn-dark mt-3">Lưu cấu hình</button>
  </div>
</form>
