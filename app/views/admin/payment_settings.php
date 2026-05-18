<?php // app/views/admin/payment_settings.php ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Cấu hình nhận tiền</h4>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('/admin/settings/payment/submit')) ?>" enctype="multipart/form-data" class="card">
  <div class="card-body">
    <?php if (function_exists('csrf_field')): ?>
      <?php echo csrf_field(); ?>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">Ngân hàng</label>
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <input class="form-control" name="BANK_NAME" value="<?= e($settings['BANK_NAME'] ?? '') ?>" placeholder="VD: Vietcombank">
    </div>

    <div class="mb-3">
      <label class="form-label">Số tài khoản</label>
      <input class="form-control" name="BANK_ACCOUNT" value="<?= e($settings['BANK_ACCOUNT'] ?? '') ?>" placeholder="VD: 0123456789">
    </div>

    <div class="mb-3">
      <label class="form-label">Chủ tài khoản</label>
      <input class="form-control" name="BANK_OWNER" value="<?= e($settings['BANK_OWNER'] ?? '') ?>" placeholder="VD: NGUYEN VAN A">
    </div>

    <div class="mb-3">
      <label class="form-label">Ảnh QR (tuỳ chọn)</label>
      <input class="form-control" type="file" name="QR_IMAGE_FILE" accept="image/*">
      <?php if (!empty($settings['QR_IMAGE'])): ?>
        <div class="mt-2">
          <img src="<?= e($settings['QR_IMAGE']) ?>" style="max-width:220px; height:auto;" alt="QR">
        </div>
      <?php endif; ?>
    </div>

    <button class="btn btn-dark">Lưu</button>
  </div>
</form>