<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Quản lý tài khoản Admin</h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/change-password')) ?>">Đổi mật khẩu</a>
    <a class="btn btn-dark btn-sm" href="<?= e(url('/admin/users/create')) ?>">+ Thêm tài khoản</a>
  </div>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/users')) ?>">
  <div class="col-12 col-md-6">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm username...">
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary">Tìm</button>
  </div>
  <div class="col-12 col-md-4 text-end small text-muted align-self-center">
    Role: superadmin / sales / warehouse
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Active</th>
        <th class="text-end">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td class="fw-semibold"><?= e($r['username']) ?></td>
          <td><span class="badge text-bg-secondary"><?= e($r['role']) ?></span></td>
          <td><?= (int)$r['is_active']===1 ? '<span class="badge text-bg-success">On</span>' : '<span class="badge text-bg-danger">Off</span>' ?></td>
          <td class="text-end">
            <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/admin/users/edit?id='.(int)$r['id'])) ?>">Sửa</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($pager['pages'] > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php for ($i=1; $i<=$pager['pages']; $i++): $qs=$_GET; $qs['page']=$i; ?>
        <li class="page-item <?= ($i===$pager['page']?'active':'') ?>">
          <a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>