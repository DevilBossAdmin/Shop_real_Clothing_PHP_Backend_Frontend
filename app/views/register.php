<div class="container" style="max-width: 520px;">
  <h1 class="h5 mb-3">Đăng ký</h1>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
  <?php endif; ?>

  <div class="card p-3">
    <form method="post" action="<?= e(url('/register/submit')) ?>">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

      <div class="mb-3">
        <label class="form-label">Họ tên</label>
        <input class="form-control" name="name" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Mật khẩu</label>
        <input class="form-control" type="password" name="password" minlength="6" required>
      </div>

      <button class="btn btn-dark w-100">Gửi OTP đăng ký</button>
    </form>

    <div class="small text-muted mt-3">
      Đã có tài khoản? <a href="<?= e(url('/login')) ?>">Đăng nhập</a>
    </div>
  </div>
</div>
