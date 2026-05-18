<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h5 mb-0">Backup dữ liệu</h1>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<div class="bg-white border rounded shadow-sm p-3" style="max-width:720px">
  <div class="text-muted mb-2">
    Backup sẽ xuất file <b>.sql</b>. Nếu chạy XAMPP Windows và bị lỗi, bạn cần cấu hình đường dẫn <b>mysqldump</b>.
  </div>

  <form method="post" action="<?= e(url('/admin/backup/run')) ?>">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <button class="btn btn-dark">Tải file backup</button>
  </form>
</div>