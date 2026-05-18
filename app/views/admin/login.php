<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:520px;">
  <h1 class="h5 mb-3">Đăng nhập Admin</h1>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="card p-3">
    <form method="post" action="<?= e(url('/admin/login/submit')) ?>">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required>
      </div>
      <button class="btn btn-dark w-100">Đăng nhập</button>
      <div class="small text-muted mt-2">
        Demo: admin / admin123 (đổi trong app/config.php)
      </div>
    </form>
  </div>
</div>
</body>
</html>
