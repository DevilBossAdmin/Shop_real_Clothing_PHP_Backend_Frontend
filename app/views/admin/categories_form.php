<?php
$isEdit = ($mode ?? 'create') === 'edit';
$form = $form ?? ['name' => '', 'slug' => '', 'parent_id' => '', 'sort_order' => 0];
$allCategories = $allCategories ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2><?= $isEdit ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2>
  <a class="btn btn-outline-secondary" href="<?= e(url('/admin/categories')) ?>">Quay lại</a>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" class="card p-3">
  <?= csrf_field() ?>

  <div class="mb-3">
    <label class="form-label">Tên danh mục</label>
    <input type="text" name="name" class="form-control" value="<?= e($form['name'] ?? '') ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Slug</label>
    <input type="text" name="slug" class="form-control" value="<?= e($form['slug'] ?? '') ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Danh mục cha</label>
    <select name="parent_id" class="form-select">
      <option value="">-- Danh mục cấp lớn --</option>
      <?php foreach ($allCategories as $cat): ?>
        <?php
          if ($isEdit && (int)$cat['id'] === (int)($form['id'] ?? 0)) continue;
          $selected = ((string)($form['parent_id'] ?? '') !== '' && (int)$form['parent_id'] === (int)$cat['id']) ? 'selected' : '';
          $prefix = !empty($cat['parent_id']) ? '— ' : '';
        ?>
        <option value="<?= (int)$cat['id'] ?>" <?= $selected ?>>
          <?= e($prefix . $cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <div class="form-text">Để trống nếu đây là danh mục lớn.</div>
  </div>

  <div class="mb-3">
    <label class="form-label">Thứ tự sắp xếp</label>
    <input type="number" name="sort_order" class="form-control" value="<?= (int)($form['sort_order'] ?? 0) ?>">
  </div>

  <button type="submit" class="btn btn-dark">
    <?= $isEdit ? 'Cập nhật danh mục' : 'Thêm danh mục' ?>
  </button>
</form>