<h1 class="h5 mb-3"><?= $mode==='create' ? 'Thêm sản phẩm' : 'Sửa sản phẩm' ?></h1>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card p-3 shadow-sm">
  <form method="post" enctype="multipart/form-data" action="<?= e($action) ?>">

    <div class="row g-3">
      <div class="col-md-8">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <label class="form-label">Tên sản phẩm</label>
        <input class="form-control" name="name" value="<?= e($form['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">SKU</label>
        <input class="form-control" name="sku" value="<?= e($form['sku'] ?? '') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input class="form-control" name="slug" value="<?= e($form['slug'] ?? '') ?>" placeholder="để trống sẽ tự tạo">
      </div>
      <div class="col-md-6">
        <label class="form-label">Danh mục</label>
        <select class="form-select" name="category_id" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ((int)($form['category_id']??0)===(int)$c['id']?'selected':'') ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Giá</label>
        <input class="form-control" type="number" min="0" name="price" value="<?= e($form['price'] ?? '0') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Giá gạch</label>
        <input class="form-control" type="number" min="0" name="compare_at_price" value="<?= e($form['compare_at_price'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Trạng thái</label>
        <select class="form-select" name="status">
          <option value="active" <?= (($form['status']??'active')==='active'?'selected':'') ?>>active</option>
          <option value="draft" <?= (($form['status']??'active')==='draft'?'selected':'') ?>>draft</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Ảnh thumbnail</label>
        <input class="form-control" type="file" name="thumbnail_file" accept="image/*">
        <?php if (!empty($form['thumbnail'])): ?>
          <div class="small text-muted mt-1">Hiện tại: <a href="<?= e($form['thumbnail']) ?>" target="_blank">mở ảnh</a></div>
        <?php endif; ?>
      </div>

      <div class="col-md-6">
        <label class="form-label">Đánh dấu NEW</label>
        <select class="form-select" name="is_new">
          <option value="1" <?= ((string)($form['is_new']??'1')==='1'?'selected':'') ?>>Có</option>
          <option value="0" <?= ((string)($form['is_new']??'1')==='0'?'selected':'') ?>>Không</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Mô tả</label>
        <textarea class="form-control" rows="5" name="description"><?= e($form['description'] ?? '') ?></textarea>
      </div>

      <div class="col-12">
        <div class="fw-semibold mb-2">Variants (size / màu / tồn)</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Size</th><th>Màu</th><th>Tồn</th><th></th></tr></thead>
            <tbody id="variantRows">
              <?php foreach ($variants as $v): ?>
                <tr>
                  <td><input class="form-control form-control-sm" name="v_size[]" value="<?= e($v['size']) ?>"></td>
                  <td><input class="form-control form-control-sm" name="v_color[]" value="<?= e($v['color']) ?>"></td>
                  <td><input class="form-control form-control-sm" type="number" min="0" name="v_stock[]" value="<?= (int)$v['stock'] ?>"></td>
                  <td><button class="btn btn-outline-danger btn-sm" type="button" onclick="this.closest('tr').remove()">Xóa</button></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($variants)): ?>
                <tr>
                  <td><input class="form-control form-control-sm" name="v_size[]" value="M"></td>
                  <td><input class="form-control form-control-sm" name="v_color[]" value="Đen"></td>
                  <td><input class="form-control form-control-sm" type="number" min="0" name="v_stock[]" value="10"></td>
                  <td><button class="btn btn-outline-danger btn-sm" type="button" onclick="this.closest('tr').remove()">Xóa</button></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="addVariantRow()">+ Thêm variant</button>
      </div>

      <div class="col-12 d-grid">
        <button class="btn btn-dark">Lưu</button>
      </div>
    </div>
  </form>
</div>

<script>
function addVariantRow() {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input class="form-control form-control-sm" name="v_size[]" value=""></td>
    <td><input class="form-control form-control-sm" name="v_color[]" value=""></td>
    <td><input class="form-control form-control-sm" type="number" min="0" name="v_stock[]" value="0"></td>
    <td><button class="btn btn-outline-danger btn-sm" type="button" onclick="this.closest('tr').remove()">Xóa</button></td>
  `;
  document.getElementById('variantRows').appendChild(tr);
}
</script>
