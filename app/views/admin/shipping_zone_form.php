<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0"><?= $mode==='create'?'Thêm zone':'Sửa zone' ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('/admin/shipping/zones')) ?>">← Quay lại</a>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" class="bg-white border rounded shadow-sm p-3">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Tên zone</label>
      <input class="form-control" name="name" value="<?= e($form['name'] ?? '') ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Phí</label>
      <input class="form-control" type="number" name="fee" value="<?= (int)($form['fee'] ?? 0) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Ưu tiên</label>
      <input class="form-control" type="number" name="sort_order" value="<?= (int)($form['sort_order'] ?? 0) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Active</label>
      <select class="form-select" name="is_active">
        <option value="1" <?= !empty($form['is_active'])?'selected':'' ?>>On</option>
        <option value="0" <?= empty($form['is_active'])?'selected':'' ?>>Off</option>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Danh sách tỉnh/thành (mỗi dòng 1 tỉnh/TP)</label>
      <textarea class="form-control" name="provinces_text" rows="8" required><?= e($form['provinces_text'] ?? '') ?></textarea>
      <div class="small text-muted mt-1">
        Ví dụ: Hà Nội ↵ Hải Phòng ↵ Bắc Ninh ...
      </div>
    </div>
  </div>

  <hr class="my-3">
  <button class="btn btn-dark">Lưu</button>
</form>