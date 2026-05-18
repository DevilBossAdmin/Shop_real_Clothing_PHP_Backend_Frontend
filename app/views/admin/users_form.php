<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0"><?= $mode==='create'?'Thêm tài khoản Admin':'Sửa tài khoản Admin' ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/users')) ?>">← Quay lại</a>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" class="bg-white border rounded shadow-sm p-3">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

  <?php if ($mode==='create'): ?>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input class="form-control" name="username" value="<?= e($form['username'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Mật khẩu (>=6 ký tự)</label>
      <input class="form-control" type="password" name="password" required>
    </div>
  <?php else: ?>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input class="form-control" value="<?= e($form['username'] ?? '') ?>" disabled>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Role</label>
      <select class="form-select" name="role">
        <option value="superadmin" <?= ($form['role'] ?? '')==='superadmin'?'selected':'' ?>>Admin tổng</option>
        <option value="sales" <?= ($form['role'] ?? 'sales')==='sales'?'selected':'' ?>>Nhân viên bán hàng</option>
        <option value="warehouse" <?= ($form['role'] ?? '')==='warehouse'?'selected':'' ?>>Nhân viên kho</option>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Trạng thái</label>
      <select class="form-select" name="is_active">
        <option value="1" <?= !empty($form['is_active'])?'selected':'' ?>>Hoạt động</option>
        <option value="0" <?= empty($form['is_active'])?'selected':'' ?>>Khóa</option>
      </select>
    </div>
  </div>

  <hr class="my-3">
  <button class="btn btn-dark">Lưu</button>
</form>