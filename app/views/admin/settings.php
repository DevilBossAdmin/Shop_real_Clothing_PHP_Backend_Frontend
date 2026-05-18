<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Cài đặt hệ thống</h1>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('/admin/settings/submit')) ?>" enctype="multipart/form-data"
      class="bg-white border rounded shadow-sm p-3" style="max-width:720px">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

  <div class="mb-3">
    <label class="form-label">Tên cửa hàng</label>
    <input class="form-control" name="shop_name" value="<?= e($setting['shop_name'] ?? '') ?>">
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Hotline</label>
      <input class="form-control" name="hotline" value="<?= e($setting['hotline'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Email</label>
      <input class="form-control" name="email" value="<?= e($setting['email'] ?? '') ?>">
    </div>
  </div>

  <div class="mb-3 mt-3">
    <label class="form-label">Địa chỉ</label>
    <input class="form-control" name="address" value="<?= e($setting['address'] ?? '') ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Logo</label>
    <input class="form-control" type="file" name="logo_file" accept="image/*">
    <?php if (!empty($setting['logo_path'])): ?>
      <div class="mt-2">
        <img src="<?= e(url('/uploads/'.$setting['logo_path'])) ?>" style="max-height:70px;border-radius:8px" alt="logo">
      </div>
    <?php endif; ?>
  </div>

  <button class="btn btn-dark">Lưu cài đặt</button>
</form>