<div class="container py-5" style="max-width:520px;">
  <h4 class="mb-3"><?= e($title ?? 'Xác thực OTP') ?></h4>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
  <?php endif; ?>

  <div class="card p-3">
    <div class="small text-muted mb-3">
      Mã OTP đã được gửi tới: <strong><?= e($email ?? '') ?></strong>
    </div>

    <form method="post" action="<?= e(url($submitPath ?? '/otp/verify')) ?>">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="email" value="<?= e($email ?? '') ?>">

      <div class="mb-3">
        <label class="form-label">Nhập mã OTP</label>
        <input type="text" name="otp" class="form-control" maxlength="6" inputmode="numeric" placeholder="Nhập 6 số OTP" required>
      </div>

      <button class="btn btn-dark w-100">Xác thực OTP</button>
    </form>

    <form method="post" action="<?= e(url($resendPath ?? '/otp/resend')) ?>" class="mt-3">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="email" value="<?= e($email ?? '') ?>">
      <button type="submit" class="btn btn-outline-secondary w-100">Gửi lại mã OTP</button>
    </form>
  </div>
</div>
