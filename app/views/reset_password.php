<div class="container py-5" style="max-width:500px">
  <h4 class="mb-3">Đặt lại mật khẩu</h4>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e(url('/reset-password/submit')) ?>">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="mb-3">
      <label class="form-label">Mật khẩu mới</label>
      <div class="form-text">Mật khẩu tối thiểu 6 ký tự.</div>
      <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3 mt-3">
      <label class="form-label">Nhập lại mật khẩu mới</label>
      <input type="password" name="password_confirm" class="form-control" required>
    </div>

    <button class="btn btn-dark w-100">Đổi mật khẩu</button>
  </form>
</div>