<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Đổi mật khẩu Admin</h1>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('/admin/change-password/submit')) ?>" class="bg-white border rounded shadow-sm p-3" style="max-width:520px">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

  <div class="mb-3">
    <label class="form-label">Mật khẩu hiện tại</label>
    <input class="form-control" type="password" name="old_password" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Mật khẩu mới</label>
    <input class="form-control" type="password" name="new_password" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Nhập lại mật khẩu mới</label>
    <input class="form-control" type="password" name="new_password2" required>
  </div>

  <button class="btn btn-dark">Đổi mật khẩu</button>
</form>