<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Nhật ký hoạt động</h1>
</div>

<form class="row g-2 mb-3" method="get" action="<?= e(url('/admin/logs')) ?>">
  <div class="col-12 col-md-6">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Tìm action/entity/ip...">
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-secondary">Tìm</button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle bg-white border rounded shadow-sm">
    <thead>
      <tr>
        <th>Thời gian</th>
        <th>Admin</th>
        <th>Action</th>
        <th>Entity</th>
        <th>IP</th>
        <th>Meta</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="small"><?= e($r['created_at']) ?></td>
          <td><?= e($r['username'] ?? '-') ?></td>
          <td class="fw-semibold"><?= e($r['action']) ?></td>
          <td class="small"><?= e(($r['entity'] ?? '-') . ($r['entity_id'] ? ('#'.$r['entity_id']) : '')) ?></td>
          <td class="small"><?= e($r['ip'] ?? '-') ?></td>
          <td class="small text-muted" style="max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <?= e($r['meta_json'] ?? '') ?>
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