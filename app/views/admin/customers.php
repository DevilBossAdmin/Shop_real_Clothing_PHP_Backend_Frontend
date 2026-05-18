<?php // app/views/admin/customers.php ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Khách hàng</h4>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/customers')) ?>">
  <div class="col-auto">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo tên / email / SĐT">
  </div>
  <div class="col-auto">
    <button class="btn btn-dark">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Tên</th>
        <th>Email</th>
        <th>SĐT</th>
        <th>Ngày tạo</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($customers)): ?>
        <tr><td colspan="6" class="text-muted">Chưa có khách hàng.</td></tr>
      <?php else: ?>
        <?php foreach ($customers as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= e($c['name'] ?? '') ?></td>
            <td><?= e($c['email'] ?? '') ?></td>
            <td><?= e($c['phone'] ?? '') ?></td>
            <td><?= e($c['created_at'] ?? '') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-dark"
                 href="<?= e(url('/admin/customers/view?id=' . (int)$c['id'])) ?>">
                Xem
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>