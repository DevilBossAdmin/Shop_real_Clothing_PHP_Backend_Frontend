<?php

require_once __DIR__ . '/db.php';

/*
|--------------------------------------------------------------------------
| Basic helpers
|--------------------------------------------------------------------------
*/

function e($s): string {
  return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

function money(int $v): string {
  return number_format($v, 0, ',', '.') . '₫';
}

function base_path(): string {
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  $base = rtrim(str_replace('/index.php', '', $script), '/');
  return $base;
}

function url(string $path): string {
  if ($path === '') {
    return base_path() . '/';
  }

  if (preg_match('~^https?://~i', $path)) {
    return $path;
  }

  $path = '/' . ltrim($path, '/');

  if (
    str_starts_with($path, '/assets/') ||
    str_starts_with($path, '/uploads/') ||
    str_starts_with($path, '/index.php') ||
    str_starts_with($path, '/backend/')
  ) {
    return base_path() . $path;
  }

  $route = $path;
  $query = '';

  if (str_contains($path, '?')) {
    [$route, $query] = explode('?', $path, 2);
  }

  $url = base_path() . '/index.php?r=' . $route;

  if ($query !== '') {
    $url .= '&' . $query;
  }

  return $url;
}

function redirect(string $path): void {
  header('Location: ' . url($path));
  exit;
}

function slugify(string $text): string {
  $text = trim($text);
  $text = mb_strtolower($text, 'UTF-8');

  $map = [
    'a'=>'áàảãạâấầẩẫậăắằẳẵặ',
    'e'=>'éèẻẽẹêếềểễệ',
    'i'=>'íìỉĩị',
    'o'=>'óòỏõọôốồổỗộơớờởỡợ',
    'u'=>'úùủũụưứừửữự',
    'y'=>'ýỳỷỹỵ',
    'd'=>'đ'
  ];

  foreach ($map as $to => $from) {
    $text = preg_replace('/['.$from.']/u', $to, $text);
  }

  $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
  $text = trim($text, '-');

  return $text ?: 'n-a';
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

function csrf_token(): string {
  if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['_csrf'];
}

function csrf_field(): string {
  return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void {
  $t = $_POST['_csrf'] ?? '';
  if (!$t || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $t)) {
    http_response_code(419);
    exit('CSRF token invalid');
  }
}

/*
|--------------------------------------------------------------------------
| Auth - Customer
|--------------------------------------------------------------------------
*/

function auth_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function require_login(): void {
  if (!auth_user()) {
    redirect('/login');
  }
}

/*
|--------------------------------------------------------------------------
| Auth - Admin (bản gốc đơn giản)
|--------------------------------------------------------------------------
*/

function require_admin(): void {
  if (empty($_SESSION['admin'])) {
    redirect('/admin/login');
  }
}

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

function cart(): array {
  return $_SESSION['cart'] ?? [];
}

function cart_count(): int {
  $c = cart();
  $n = 0;
  foreach ($c as $row) {
    $n += (int)($row['qty'] ?? 0);
  }
  return $n;
}

/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/

function upload_image(string $field, string $prefix = 'img'): ?string {
  if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
    return null;
  }

  $tmp = $_FILES[$field]['tmp_name'];
  $name = $_FILES[$field]['name'] ?? '';

  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
    return null;
  }

  $dir = __DIR__ . '/../public/uploads';
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }

  $file = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = $dir . '/' . $file;

  if (!move_uploaded_file($tmp, $dest)) {
    return null;
  }

  return $file;
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function paginate_meta(int $total, int $page, int $perPage): array {
  $page = max(1, $page);
  $pages = (int)ceil($total / max(1, $perPage));
  $pages = max(1, $pages);
  $offset = ($page - 1) * $perPage;

  return [
    'total' => $total,
    'page' => $page,
    'perPage' => $perPage,
    'pages' => $pages,
    'offset' => $offset
  ];
}