<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Zone phí ship theo khu vực</h1>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/shipping/settings')) ?>">Cấu hình</a>
    <a class="btn btn-dark btn-sm" href="<?= e(url('/admin/shipping/zones/create')) ?>">+ Thêm zone</a>
  </div>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/shipping/zones')) ?>">
  <div class="col-12 col-md-6">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm theo tên zone...">
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>Zone</th>
        <th>Phí</th>
        <th>Active</th>
        <th>Ưu tiên</th>
        <th class="text-end">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($r['name']) ?></div>
            <div class="small text-muted">Tỉnh/TP: <?= e(substr(str_replace(["\r","\n"], ', ', $r['provinces_text']), 0, 120)) ?>...</div>
          </td>
          <td class="fw-semibold"><?= money((int)$r['fee']) ?></td>
          <td><?= !empty($r['is_active']) ? '<span class="badge text-bg-success">On</span>' : '<span class="badge text-bg-secondary">Off</span>' ?></td>
          <td><?= (int)$r['sort_order'] ?></td>
          <td class="text-end">
            <a class="btn btn-outline-dark btn-sm" href="<?= e(url('/admin/shipping/zones/edit?id='.(int)$r['id'])) ?>">Sửa</a>
            <a class="btn btn-outline-danger btn-sm"
               onclick="return confirm('Xóa zone #<?= (int)$r['id'] ?>?');"
               href="<?= e(url('/admin/shipping/zones/delete?id='.(int)$r['id'])) ?>">Xóa</a>
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