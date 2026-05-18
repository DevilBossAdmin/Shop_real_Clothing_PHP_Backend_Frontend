<?php // app/views/admin/shipping_zones.php ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Zone ship</h4>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<div class="card mb-3">
  <div class="card-body">
    <form class="row g-2" method="post" action="<?= e(url('/admin/shipping/zones/save')) ?>">
      <?= csrf_field(); ?>
      <div class="col-md-3">
        <label class="form-label">Tên zone</label>
        <input class="form-control" name="name" placeholder="VD: Nội thành Hà Nội" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tỉnh/thành áp dụng</label>
        <textarea class="form-control" rows="2" name="provinces_text" placeholder="Mỗi dòng một tỉnh/thành"></textarea>
      </div>
      <div class="col-md-2">
        <label class="form-label">Phí ship (đ)</label>
        <input class="form-control" type="number" name="fee" value="0" min="0">
      </div>
      <div class="col-md-1">
        <label class="form-label">STT</label>
        <input class="form-control" type="number" name="sort_order" value="0" min="0">
      </div>
      <div class="col-md-1">
        <label class="form-label">Bật</label>
        <select class="form-select" name="is_active">
          <option value="1">Có</option>
          <option value="0">Không</option>
        </select>
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-dark w-100">Thêm</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên zone</th>
        <th>Tỉnh/thành</th>
        <th>Phí</th>
        <th>STT</th>
        <th>Bật</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($zones)): ?>
        <tr><td colspan="7" class="text-muted">Chưa có zone.</td></tr>
      <?php else: ?>
        <?php foreach ($zones as $z): ?>
          <tr>
            <td><?= (int)$z['id'] ?></td>
            <td><?= e($z['name']) ?></td>
            <td><div style="white-space:pre-line"><?= e($z['provinces_text'] ?? '') ?></div></td>
            <td><?= number_format((int)$z['fee']) ?> đ</td>
            <td><?= (int)($z['sort_order'] ?? 0) ?></td>
            <td><?= (int)($z['is_active'] ?? 1) === 1 ? 'Có' : 'Không' ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-danger" href="<?= e(url('/admin/shipping/zones/delete?id=' . (int)$z['id'])) ?>" onclick="return confirm('Xóa zone này?')">Xóa</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
