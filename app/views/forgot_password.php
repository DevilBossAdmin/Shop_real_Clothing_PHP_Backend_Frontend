<div class="container py-5" style="max-width:500px">
  <h4 class="mb-3">Quên mật khẩu</h4>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e(url('/forgot-password/submit')) ?>">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Nhập email tài khoản</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <button class="btn btn-dark w-100">Gửi OTP về email</button>
  </form>
</div>