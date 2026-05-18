<?php

session_start();
require_once __DIR__ . '/../backend/bootstrap.php';

if (empty($_SESSION['_csrf'])) {
  $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../app/helpers.php';

require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Order.php';
require_once __DIR__ . '/../app/models/Inventory.php';
require_once __DIR__ . '/../app/models/Shipping.php';
require_once __DIR__ . '/../app/models/Shipment.php';
require_once __DIR__ . '/../app/models/PaymentSetting.php';
require_once __DIR__ . '/../app/models/CarrierApi.php';

if (file_exists(__DIR__ . '/../app/models/Coupon.php')) {
  require_once __DIR__ . '/../app/models/Coupon.php';
}

if (file_exists(__DIR__ . '/../app/models/Report.php')) {
  require_once __DIR__ . '/../app/models/Report.php';
}

require_once __DIR__ . '/../app/models/AdminUser.php';
require_once __DIR__ . '/../app/models/AdminLog.php';
require_once __DIR__ . '/../app/models/SystemSetting.php';

function render_front(string $view, array $data = []): void
{
  extract($data);
  require __DIR__ . '/../app/views/layout/header.php';
  require __DIR__ . '/../app/views/' . $view . '.php';
  require __DIR__ . '/../app/views/layout/footer.php';
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = '/shop_real/public';

if ($base !== '' && strpos($path, $base) === 0) {
  $path = substr($path, strlen($base));
}

$route = rtrim($path, '/');
if ($route === '') $route = '/';

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$route = '/' . ltrim(substr($path, strlen($base)), '/');

if ($route === '//') $route = '/';
$route = rtrim($route, '/');
if ($route === '') $route = '/';

function render_admin(string $viewFile, array $data = []): void
{
  extract($data);

  $view_file = realpath($viewFile);
  if (!$view_file) {
    die("View not found: " . $viewFile);
  }

  require __DIR__ . '/../app/views/admin/layout.php';
}

function path(): string
{
  $u = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

  $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
  $base = rtrim($base, '/');
  if ($base === '/') $base = '';

  if ($base !== '' && strpos($path, $base) === 0) {
    $path = substr($path, strlen($base));
  }

  $route = rtrim($path, '/');
  if ($route === '') $route = '/';
  $base = base_path();

  if ($base && str_starts_with($u, $base)) $u = substr($u, strlen($base));
  return $u ?: '/';
}

function route(): string
{
  if (!empty($_GET['r'])) return (string)$_GET['r'];
  return path();
}

if (!function_exists('admin_user')) {
  function admin_user(): ?array
  {
    return $_SESSION['admin_user'] ?? null;
  }
}

if (!function_exists('require_admin_auth')) {
  function require_admin_auth(): void
  {
    if (!admin_user()) redirect('/admin/login');
  }
}

if (!function_exists('admin_can')) {
  function admin_can(string $perm): bool
  {
    $a = admin_user();
    $role = $a['role'] ?? '';

    if ($role === 'superadmin') return true;

    if ($role === 'sales') {
      return in_array($perm, [
        'dashboard.view',
        'products.view',
        'orders.view', 'orders.edit',
        'categories.view',
        'reports.view',
      ], true);
    }

    if ($role === 'warehouse') {
      return in_array($perm, [
        'dashboard.view',
        'products.view',
        'inventory.view', 'inventory.edit',
        'orders.view',
        'reports.view',
        'categories.view',
      ], true);
    }

    return false;
  }
}

if (!function_exists('require_perm')) {
  function require_perm(string $perm): void
  {
    require_admin_auth();
    if (!admin_can($perm)) {
      http_response_code(403);
      echo "403 Forbidden";
      exit;
    }
  }
}

try {
  $route = route();

  if ($route === '/test') {
    header('Content-Type: text/plain; charset=utf-8');

    $plain = 'admin123';
    $hash  = password_hash($plain, PASSWORD_BCRYPT);

    echo "plain: $plain\n";
    echo "hash:  $hash\n";
    echo "verify(admin123): ";
    var_dump(password_verify('admin123', $hash));

    exit;
  }

  // ======================
  // FRONT
  // ======================

  if ($route === '/' || $route === '/index.php') {
    $products = Product::listActive(12, 0, null, null, null, null);
    render_front('home', compact('products'));
    exit;
  }

  // ======================
  // CATEGORY
  // ======================
  if (preg_match('~^/c/([^/]+)$~', $route, $m)) {
    $cat = Category::bySlug($m[1]);
    if (!$cat) {
      http_response_code(404);
      echo "Not found";
      exit;
    }

    $size = trim($_GET['size'] ?? '') ?: null;
    $color = trim($_GET['color'] ?? '') ?: null;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 24;

    $parentSlugs = ['ao-xuan-he', 'quan', 'phu-kien'];
    $isParentCategory = in_array($cat['slug'], $parentSlugs, true);

    if ($isParentCategory) {
      $total = Product::countActiveByCategoryTree((int)$cat['id'], $size, $color, null);
      $pager = paginate_meta($total, $page, $perPage);

      $products = Product::listActiveByCategoryTree(
        $pager['perPage'],
        $pager['offset'],
        (int)$cat['id'],
        $size,
        $color,
        null
      );

      $sizes = Product::distinctSizesByCategoryTree((int)$cat['id']);
      $colors = Product::distinctColorsByCategoryTree((int)$cat['id']);
    } else {
      $total = Product::countActive((int)$cat['id'], $size, $color, null);
      $pager = paginate_meta($total, $page, $perPage);

      $products = Product::listActive(
        $pager['perPage'],
        $pager['offset'],
        (int)$cat['id'],
        $size,
        $color,
        null
      );

      $sizes = Product::distinctSizes((int)$cat['id']);
      $colors = Product::distinctColors((int)$cat['id']);
    }

    $filters = ['size' => $size, 'color' => $color];

    render_front('category', compact('cat', 'products', 'pager', 'sizes', 'colors', 'filters'));
    exit;
  }

  if (preg_match('~^/p/([^/]+)$~', $route, $m)) {
    $p = Product::bySlug($m[1]);
    if (!$p || $p['status'] !== 'active') {
      http_response_code(404);
      echo "Not found";
      exit;
    }

    $variants = Product::variants((int)$p['id']);
    $sizes = array_values(array_unique(array_map(fn($v) => $v['size'], $variants)));
    $colors = array_values(array_unique(array_map(fn($v) => $v['color'], $variants)));

    render_front('product', compact('p', 'variants', 'sizes', 'colors'));
    exit;
  }

  if ($route === '/search') {
    $q = trim($_GET['q'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 24;

    $total = Product::countActive(null, null, null, $q ?: null);
    $pager = paginate_meta($total, $page, $perPage);
    $products = Product::listActive($pager['perPage'], $pager['offset'], null, null, null, $q ?: null);

    render_front('search', compact('q', 'products', 'pager'));
    exit;
  }

  if ($route === '/search/suggest') {
    header('Content-Type: application/json; charset=utf-8');

    $q = trim($_GET['q'] ?? '');
    if ($q === '' || mb_strlen($q) < 1) {
      echo json_encode([]);
      exit;
    }

    $rows = Product::suggest($q, 8);

    $out = array_map(function ($r) {
      return [
        'id' => (int)$r['id'],
        'name' => $r['name'],
        'slug' => $r['slug'],
        'price' => (int)$r['price'],
        'thumbnail' => $r['thumbnail'] ?? null,
      ];
    }, $rows);

    echo json_encode($out);
    exit;
  }

  if ($route === '/cart') {
    $cart = cart();
    $ids = array_values(array_unique(array_map(fn($r) => (int)$r['product_id'], $cart)));
    $map = Product::findManyByIds($ids);
    render_front('cart', compact('cart', 'map'));
    exit;
  }

  if ($route === '/cart/add') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
      $key = (string)$id . '||-||-';

      if (!isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key] = [
          'product_id' => $id,
          'variant_id' => null,
          'size' => null,
          'color' => null,
          'qty' => 1
        ];
      } else {
        $_SESSION['cart'][$key]['qty'] = (int)$_SESSION['cart'][$key]['qty'] + 1;
      }
    }

    if (($_GET['redirect'] ?? '') === 'checkout') {
      redirect('/checkout');
    }

    redirect('/cart');
  }

  if ($route === '/cart/add_variant') {
    csrf_check();

    $productId = (int)($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    if ($productId <= 0 || $size === '' || $color === '') redirect('/cart');

    $variants = Product::variants($productId);
    $variantId = null;
    foreach ($variants as $v) {
      if ($v['size'] === $size && $v['color'] === $color) {
        $variantId = (int)$v['id'];
        break;
      }
    }

    $key = $productId . '||' . ($variantId ?? '0') . '||' . $size . '||' . $color;
    if (!isset($_SESSION['cart'][$key])) {
      $_SESSION['cart'][$key] = [
        'product_id' => $productId,
        'variant_id' => $variantId,
        'size' => $size,
        'color' => $color,
        'qty' => $qty
      ];
    } else {
      $_SESSION['cart'][$key]['qty'] = (int)$_SESSION['cart'][$key]['qty'] + $qty;
    }
    redirect('/cart');
  }

  if ($route === '/cart/remove') {
    $key = (string)($_GET['key'] ?? '');
    if ($key && isset($_SESSION['cart'][$key])) unset($_SESSION['cart'][$key]);
    redirect('/cart');
  }

  if ($route === '/cart/update') {
    csrf_check();
    $key = (string)($_POST['key'] ?? '');
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    if ($key && isset($_SESSION['cart'][$key])) $_SESSION['cart'][$key]['qty'] = $qty;
    redirect('/cart');
  }

  if ($route === '/coupon/apply') {
    csrf_check();

    $code = trim($_POST['code'] ?? '');
    $code = Coupon::normalizeCode($code);

    if ($code === '') {
      $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Vui lòng nhập mã giảm giá.'];
      redirect('/checkout');
    }

    $c = Coupon::findActiveByCode($code);
    if (!$c) {
      unset($_SESSION['coupon_code']);
      $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'];
      redirect('/checkout');
    }

    $_SESSION['coupon_code'] = $code;
    $_SESSION['_flash'] = ['type' => 'success', 'msg' => "Đã áp dụng mã: {$code}"];
    redirect('/checkout');
  }

  if ($route === '/coupon/remove') {
    unset($_SESSION['coupon_code']);
    $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã gỡ mã giảm giá.'];
    redirect('/checkout');
  }

  if ($route === '/checkout') {
    $cart = cart();

    $ids = array_values(array_unique(array_map(fn($r) => (int)$r['product_id'], $cart)));
    $map = $ids ? Product::findManyByIds($ids) : [];

    $lines = [];
    $subtotal = 0;

    foreach ($cart as $key => $row) {
      $p = $map[(int)$row['product_id']] ?? null;
      if (!$p) continue;

      $qty = max(1, (int)$row['qty']);
      $sub = (int)$p['price'] * $qty;
      $subtotal += $sub;

      $lines[] = [
        'key' => $key,
        'product_id' => (int)$p['id'],
        'name' => $p['name'],
        'price' => (int)$p['price'],
        'qty' => $qty,
        'sub' => $sub,
        'variant_id' => $row['variant_id'] ?? null,
        'size' => $row['size'] ?? null,
        'color' => $row['color'] ?? null,
      ];
    }

    $coupon = null;
    $discount = 0;
    $couponCode = $_SESSION['coupon_code'] ?? null;

    if ($couponCode) {
      $c = Coupon::findActiveByCode($couponCode);
      if ($c) {
        $coupon = $c;
        $discount = Coupon::calcDiscount($c, $subtotal);
      } else {
        unset($_SESSION['coupon_code']);
      }
    }

    $payable = max(0, $subtotal - $discount);

    $u = auth_user();
    $order = null;
    if (!empty($_SESSION['_checkout_success']) && !empty($_SESSION['_checkout_order_id'])) {
      $order = Order::findWithItems((int)$_SESSION['_checkout_order_id']);
    }

    $flash_msg = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);

    render_front('checkout', [
      'cart' => $cart,
      'map' => $map,
      'lines' => $lines,
      'subtotal' => $subtotal,
      'discount' => $discount,
      'payable' => $payable,
      'total' => $payable,
      'coupon' => $coupon,
      'u' => $u,
      'flash_msg' => $flash_msg,
      'form' => $_SESSION['_checkout_form'] ?? [],
      'error' => $_SESSION['_checkout_error'] ?? null,
      'success' => $_SESSION['_checkout_success'] ?? null,
      'order_id' => $_SESSION['_checkout_order_id'] ?? null,
      'order' => $order,
    ]);

    unset(
      $_SESSION['_checkout_form'],
      $_SESSION['_checkout_error'],
      $_SESSION['_checkout_success'],
      $_SESSION['_checkout_order_id']
    );
    exit;
  }

  if ($route === '/checkout/submit') {
    csrf_check();

    $cart = cart();
    if (empty($cart)) {
      $_SESSION['_checkout_error'] = 'Giỏ hàng trống.';
      redirect('/checkout');
    }

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note    = trim($_POST['note'] ?? '');
    $pm      = trim($_POST['payment_method'] ?? 'cod');

    if ($name === '' || $email === '' || $phone === '' || $address === '') {
      $_SESSION['_checkout_error'] = 'Vui lòng nhập đầy đủ Họ tên / Email / SĐT / Địa chỉ.';
      $_SESSION['_checkout_form'] = $_POST;
      redirect('/checkout');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['_checkout_error'] = 'Email không hợp lệ.';
      $_SESSION['_checkout_form'] = $_POST;
      redirect('/checkout');
    }

    $ids = [];
    foreach ($cart as $row) {
      $ids[] = (int)$row['product_id'];
    }
    $ids = array_values(array_unique($ids));

    $map = $ids ? Product::findManyByIds($ids) : [];
    $items = [];
    $subtotal = 0;

    foreach ($cart as $row) {
      $pid = (int)$row['product_id'];
      if (!isset($map[$pid])) continue;

      $p = $map[$pid];
      $qty = max(1, (int)($row['qty'] ?? 1));
      $price = (int)$p['price'];

      $subtotal += $price * $qty;

      $items[] = [
        'product_id' => $pid,
        'variant_id' => $row['variant_id'] ?? null,
        'price'      => $price,
        'qty'        => $qty,
        'size'       => $row['size'] ?? null,
        'color'      => $row['color'] ?? null,
      ];
    }

    if (empty($items)) {
      $_SESSION['_checkout_error'] = 'Giỏ hàng không hợp lệ.';
      redirect('/checkout');
    }

    $u = auth_user();
    $userId = isset($u['id']) ? (int)$u['id'] : null;

    $shippingFee = 0;
    $orderTotal = $subtotal + $shippingFee;

    $orderId = Order::create(
      $userId,
      $name,
      $email,
      $phone,
      $address,
      ($note !== '' ? $note : null),
      (int)$subtotal,
      0,
      null,
      (int)$orderTotal,
      $items,
      $pm,
      'unpaid',
      null,
      $shippingFee,
      null,
      'manual'
    );

    unset($_SESSION['cart']);

    $_SESSION['_checkout_success'] = 'Đặt hàng thành công!';
    $_SESSION['_checkout_order_id'] = $orderId;

    redirect('/checkout');
  }

  if ($route === '/orders') {
    require_login();
    $u = auth_user();

    $orders = Order::listByUser((int)$u['id'], 50);
    render_front('orders', compact('orders', 'u'));
    exit;
  }

  if ($route === '/orders/view') {
    require_login();
    $u = auth_user();

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
      http_response_code(400);
      echo "Bad request";
      exit;
    }

    $order = Order::findWithItemsByUser($id, (int)$u['id']);
    if (!$order) {
      http_response_code(404);
      echo "Order not found";
      exit;
    }

    render_front('order_detail', compact('order'));
    exit;
  }

  if ($route === '/orders/cancel') {
    csrf_check();
    require_login();
    $u = auth_user();

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
      Order::cancelByUser($id, (int)$u['id']);
    }

    redirect('/account');
  }

  if ($route === '/login') {
    render_front('login', [
      'error' => $_SESSION['_auth_error'] ?? null,
      'success' => $_SESSION['_auth_success'] ?? null,
    ]);
    unset($_SESSION['_auth_error'], $_SESSION['_auth_success']);
    exit;
  }

  if ($route === '/login/submit') {
    csrf_check();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $u = User::verify($email, $password);

    if (!$u) {
      $_SESSION['_auth_error'] = "Sai email hoặc mật khẩu.";
      redirect('/login');
    }

    $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email']];
    redirect('/account');
  }

  if ($route === '/register') {
    render_front('register', [
      'error' => $_SESSION['_auth_error'] ?? null,
      'success' => $_SESSION['_auth_success'] ?? null,
    ]);
    unset($_SESSION['_auth_error'], $_SESSION['_auth_success']);
    exit;
  }

  if ($route === '/register/submit') {
    csrf_check();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($name === '' || $email === '' || strlen($password) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['_auth_error'] = "Thông tin chưa hợp lệ (email đúng định dạng, mật khẩu >= 6 ký tự).";
      redirect('/register');
    }

    if (User::findByEmail($email)) {
      $_SESSION['_auth_error'] = "Email đã tồn tại.";
      redirect('/register');
    }

    $otpResult = OtpService::issue('register', $email, [
      'name' => $name,
      'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    if (empty($otpResult['sent'])) {
      $_SESSION['_auth_error'] = "Không gửi được OTP đăng ký. Hãy kiểm tra cấu hình SMTP trong backend/.env và extension openssl của XAMPP.";
      redirect('/register');
    }

    $_SESSION['_auth_success'] = "Đã gửi mã OTP đăng ký về email của bạn.";
    redirect('/register/verify-otp?email=' . urlencode($email));
  }

  if ($route === '/register/verify-otp') {
    $email = trim($_GET['email'] ?? $_POST['email'] ?? '');
    render_front('verify_otp', [
      'title' => 'Xác thực OTP đăng ký',
      'email' => $email,
      'submitPath' => '/register/verify-otp/submit',
      'resendPath' => '/register/verify-otp/resend',
      'error' => $_SESSION['_auth_error'] ?? null,
      'success' => $_SESSION['_auth_success'] ?? null,
    ]);
    unset($_SESSION['_auth_error'], $_SESSION['_auth_success']);
    exit;
  }

  if ($route === '/register/verify-otp/submit') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    $verified = OtpService::verify('register', $email, $otp);
    if (!$verified) {
      $_SESSION['_auth_error'] = 'Mã OTP không đúng, đã hết hạn hoặc đã vượt quá số lần thử.';
      redirect('/register/verify-otp?email=' . urlencode($email));
    }

    $payload = $verified['payload'] ?? [];
    $name = trim((string)($payload['name'] ?? ''));
    $passwordHash = (string)($payload['password_hash'] ?? '');

    if ($name === '' || $passwordHash === '' || User::findByEmail($email)) {
      $_SESSION['_auth_error'] = 'Không thể hoàn tất đăng ký. Vui lòng thử lại.';
      redirect('/register');
    }

    $st = db()->prepare('INSERT INTO users (name, email, password_hash, email_verified_at) VALUES (?, ?, ?, NOW())');
    $st->execute([$name, $email, $passwordHash]);
    $u = User::findByEmail($email);
    OtpService::consume('register', $email);

    $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email']];
    $_SESSION['_auth_success'] = 'Đăng ký tài khoản thành công.';
    redirect('/account');
  }

  if ($route === '/register/verify-otp/resend') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
      $_SESSION['_auth_error'] = 'Thiếu email để gửi lại OTP.';
      redirect('/register');
    }

    $dbUser = User::findByEmail($email);
    if ($dbUser) {
      $_SESSION['_auth_error'] = 'Email đã tồn tại.';
      redirect('/register');
    }

    $db = db();
    $st = $db->prepare('SELECT payload_json FROM email_otps WHERE purpose = ? AND email = ? ORDER BY id DESC LIMIT 1');
    $st->execute(['register', $email]);
    $otpRow = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    $payload = json_decode((string)($otpRow['payload_json'] ?? '{}'), true) ?: [];
    if (empty($payload['name']) || empty($payload['password_hash'])) {
      $_SESSION['_auth_error'] = 'Không tìm thấy yêu cầu đăng ký trước đó. Vui lòng nhập lại thông tin đăng ký.';
      redirect('/register');
    }

    $otpResult = OtpService::resend('register', $email, $payload);
    if (empty($otpResult['sent'])) {
      $_SESSION['_auth_error'] = 'Không gửi lại được OTP đăng ký. Hãy kiểm tra cấu hình SMTP.';
      redirect('/register/verify-otp?email=' . urlencode($email));
    }

    $_SESSION['_auth_success'] = 'Đã gửi lại OTP đăng ký.';
    redirect('/register/verify-otp?email=' . urlencode($email));
  }

  if ($route === '/forgot-password') {
    render_front('forgot_password', [
      'error' => $_SESSION['_auth_error'] ?? null,
      'success' => $_SESSION['_auth_success'] ?? null,
    ]);
    unset($_SESSION['_auth_error'], $_SESSION['_auth_success']);
    exit;
  }

  if ($route === '/forgot-password/submit') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $u = User::findByEmail($email);

    if (!$u) {
      $_SESSION['_auth_error'] = 'Email chưa tồn tại trong hệ thống.';
      redirect('/forgot-password');
    }

    $otpResult = OtpService::issue('reset_password', $email, ['name' => $u['name'] ?? ''], (int)$u['id']);
    if (empty($otpResult['sent'])) {
      $_SESSION['_auth_error'] = 'Không gửi được OTP đặt lại mật khẩu. Hãy kiểm tra cấu hình SMTP trong backend/.env.';
      redirect('/forgot-password');
    }

    $_SESSION['_auth_success'] = 'Đã gửi OTP đặt lại mật khẩu về email.';
    redirect('/forgot-password/verify-otp?email=' . urlencode($email));
  }

  if ($route === '/forgot-password/verify-otp') {
    $email = trim($_GET['email'] ?? $_POST['email'] ?? '');
    render_front('verify_otp', [
      'title' => 'Xác thực OTP quên mật khẩu',
      'email' => $email,
      'submitPath' => '/forgot-password/verify-otp/submit',
      'resendPath' => '/forgot-password/verify-otp/resend',
      'error' => $_SESSION['_auth_error'] ?? null,
      'success' => $_SESSION['_auth_success'] ?? null,
    ]);
    unset($_SESSION['_auth_error'], $_SESSION['_auth_success']);
    exit;
  }

  if ($route === '/forgot-password/verify-otp/submit') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    $verified = OtpService::verify('reset_password', $email, $otp);
    if (!$verified) {
      $_SESSION['_auth_error'] = 'Mã OTP không đúng, đã hết hạn hoặc đã vượt quá số lần thử.';
      redirect('/forgot-password/verify-otp?email=' . urlencode($email));
    }

    $userId = (int)($verified['user_id'] ?? 0);
    if ($userId <= 0) {
      $_SESSION['_auth_error'] = 'Không tìm thấy tài khoản cần đặt lại mật khẩu.';
      redirect('/forgot-password');
    }

    $token = User::createPasswordReset($userId);
    OtpService::consume('reset_password', $email);
    redirect('/reset-password?token=' . urlencode($token));
  }

  if ($route === '/forgot-password/verify-otp/resend') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $u = User::findByEmail($email);
    if (!$u) {
      $_SESSION['_auth_error'] = 'Email chưa tồn tại trong hệ thống.';
      redirect('/forgot-password');
    }

    $otpResult = OtpService::resend('reset_password', $email, ['name' => $u['name'] ?? ''], (int)$u['id']);
    if (empty($otpResult['sent'])) {
      $_SESSION['_auth_error'] = 'Không gửi lại được OTP đặt lại mật khẩu. Hãy kiểm tra cấu hình SMTP.';
      redirect('/forgot-password/verify-otp?email=' . urlencode($email));
    }

    $_SESSION['_auth_success'] = 'Đã gửi lại OTP đặt lại mật khẩu.';
    redirect('/forgot-password/verify-otp?email=' . urlencode($email));
  }

  if ($route === '/reset-password') {
    $token = trim($_GET['token'] ?? '');
    $row = User::findResetByToken($token);
    if (!$row) {
      $_SESSION['_auth_error'] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
      redirect('/forgot-password');
    }

    render_front('reset_password', [
      'token' => $token,
      'error' => $_SESSION['_auth_error'] ?? null,
    ]);
    unset($_SESSION['_auth_error']);
    exit;
  }

  if ($route === '/reset-password/submit') {
    csrf_check();
    $token = trim($_POST['token'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
    $row = User::findResetByToken($token);

    if (!$row) {
      $_SESSION['_auth_error'] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
      redirect('/forgot-password');
    }

    if (strlen($password) < 6) {
      $_SESSION['_auth_error'] = 'Mật khẩu mới phải từ 6 ký tự.';
      redirect('/reset-password?token=' . urlencode($token));
    }

    if ($password !== $passwordConfirm) {
      $_SESSION['_auth_error'] = 'Mật khẩu nhập lại chưa khớp.';
      redirect('/reset-password?token=' . urlencode($token));
    }

    User::updatePassword((int)$row['user_id'], $password);
    User::deleteResetToken($token);
    $_SESSION['_auth_success'] = 'Đổi mật khẩu thành công. Bạn hãy đăng nhập lại.';
    redirect('/login');
  }

  if ($route === '/account') {
    require_login();
    $u = auth_user();
    $orders = Order::recentByUser((int)$u['id'], 10);
    render_front('account', compact('u', 'orders'));
    exit;
  }

  if ($route === '/logout') {
    unset($_SESSION['user']);
    redirect('/');
  }

  // ======================
  // ADMIN AUTH (DB) + LOG
  // ======================

  if ($route === '/admin/login') {
    $error = $_SESSION['_admin_error'] ?? null;
    unset($_SESSION['_admin_error']);
    require __DIR__ . '/../app/views/admin/login.php';
    exit;
  }

  if ($route === '/admin/login/submit') {
    csrf_check();

    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $au = AdminUser::verify($username, $password);
    if (!$au) {
      $_SESSION['_admin_error'] = "Sai tài khoản/mật khẩu hoặc tài khoản bị khóa.";
      redirect('/admin/login');
    }

    $_SESSION['admin_user'] = [
      'id' => (int)$au['id'],
      'username' => $au['username'],
      'role' => $au['role'],
    ];

    AdminLog::add((int)$au['id'], 'admin.login', 'admin_users', (int)$au['id'], ['role' => $au['role']]);
    redirect('/admin');
  }

  if ($route === '/admin/logout') {
    $a = admin_user();
    if ($a) AdminLog::add((int)$a['id'], 'admin.logout', 'admin_users', (int)$a['id'], null);
    unset($_SESSION['admin_user']);
    redirect('/admin/login');
  }

  if ($route === '/admin' || $route === '/admin/') {
    require_perm('dashboard.view');

    $db = db();

    $stats = [
      'products'         => Product::adminCount(null, null),
      'categories'       => (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
      'orders'           => Order::adminCount(null),
      'users'            => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),

      'pending_orders'   => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
      'confirmed_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'")->fetchColumn(),
      'shipping_orders'  => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'shipping'")->fetchColumn(),
      'completed_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn(),
      'cancelled_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn(),

      'paid_orders'      => (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
      'unpaid_orders'    => (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'unpaid'")->fetchColumn(),
      'failed_orders'    => (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'failed'")->fetchColumn(),

      'revenue'          => (int)$db->query("
        SELECT COALESCE(SUM(total), 0)
        FROM orders
        WHERE status = 'completed'
      ")->fetchColumn(),
    ];

    $lowStockProducts = [];
    $st = $db->query("
      SELECT
        p.id,
        p.name,
        p.sku,
        COALESCE(SUM(v.stock), 0) AS total_stock
      FROM products p
      LEFT JOIN product_variants v ON v.product_id = p.id
      GROUP BY p.id, p.name, p.sku
      HAVING total_stock <= 10
      ORDER BY total_stock ASC, p.id DESC
      LIMIT 5
    ");
    if ($st) {
      $lowStockProducts = $st->fetchAll(PDO::FETCH_ASSOC);
    }

    $recentOrders = [];
    $st = $db->query("
      SELECT
        id,
        customer_name,
        customer_phone,
        total,
        subtotal,
        shipping_fee,
        status,
        shipping_status,
        payment_status,
        payment_method,
        created_at
      FROM orders
      ORDER BY id DESC
      LIMIT 5
    ");
    if ($st) {
      $recentOrders = $st->fetchAll(PDO::FETCH_ASSOC);
    }

    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);

    render_admin(
      __DIR__ . '/../app/views/admin/dashboard.php',
      compact('stats', 'lowStockProducts', 'recentOrders', 'flash')
    );
    exit;
  }

  // ======================
// ADMIN PAGES
// ======================

if ($route === '/admin' || $route === '/admin/') {
  require_perm('dashboard.view');

  $db = db();

  $stats = [
    'products'         => Product::adminCount(null, null),
    'categories'       => (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'orders'           => Order::adminCount(null),
    'users'            => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),

    'pending_orders'   => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'confirmed_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'confirmed'")->fetchColumn(),
    'shipping_orders'  => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'shipping'")->fetchColumn(),
    'completed_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn(),
    'cancelled_orders' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn(),

    'paid_orders'      => (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
    'unpaid_orders'    => (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'unpaid'")->fetchColumn(),
    'failed_orders'    => (int)$db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'failed'")->fetchColumn(),

    'revenue'          => (int)$db->query("
      SELECT COALESCE(SUM(total), 0)
      FROM orders
      WHERE status = 'completed'
    ")->fetchColumn(),
  ];

  $lowStockProducts = [];
  $st = $db->query("
    SELECT
      p.id,
      p.name,
      p.sku,
      COALESCE(SUM(v.stock), 0) AS total_stock
    FROM products p
    LEFT JOIN product_variants v ON v.product_id = p.id
    GROUP BY p.id, p.name, p.sku
    HAVING total_stock <= 10
    ORDER BY total_stock ASC, p.id DESC
    LIMIT 5
  ");
  if ($st) {
    $lowStockProducts = $st->fetchAll(PDO::FETCH_ASSOC);
  }

  $recentOrders = [];
  $st = $db->query("
    SELECT
      id,
      customer_name,
      customer_phone,
      total,
      subtotal,
      shipping_fee,
      status,
      shipping_status,
      payment_status,
      payment_method,
      created_at
    FROM orders
    ORDER BY id DESC
    LIMIT 5
  ");
  if ($st) {
    $recentOrders = $st->fetchAll(PDO::FETCH_ASSOC);
  }

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/dashboard.php',
    compact('stats', 'lowStockProducts', 'recentOrders', 'flash')
  );
  exit;
}

if ($route === '/admin/products') {
  require_perm('products.view');

  $q = trim($_GET['q'] ?? '');
  $status = trim($_GET['status'] ?? '');

  if (!in_array($status, ['active', 'draft'], true)) {
    $status = '';
  }

  $allowedPerPage = [20, 30, 50, 100];
  $perPage = (int)($_GET['per_page'] ?? 30);
  if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 30;
  }

  $parentCategories = Product::adminParentCategories();
  $productGroups = [];

  foreach ($parentCategories as $parent) {
    $pageParam = 'page_group_' . (int)$parent['id'];
    $page = max(1, (int)($_GET[$pageParam] ?? 1));

    $total = Product::adminCountByParentCategory(
      (int)$parent['id'],
      $q !== '' ? $q : null,
      $status !== '' ? $status : null
    );

    $pager = paginate_meta($total, $page, $perPage);

    $rows = Product::adminListByParentCategory(
      (int)$parent['id'],
      $pager['perPage'],
      $pager['offset'],
      $q !== '' ? $q : null,
      $status !== '' ? $status : null
    );

    $productGroups[] = [
      'parent' => $parent,
      'rows'   => $rows,
      'pager'  => $pager,
    ];
  }

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/products_list.php',
    compact('productGroups', 'q', 'status', 'flash', 'perPage')
  );
  exit;
}

if ($route === '/admin/categories') {
  require_perm('categories.view');

  $categories = Category::all();

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/categories.php',
    compact('categories', 'flash')
  );
  exit;
}

if ($route === '/admin/categories/create') {
  require_perm('categories.view');

  $allCategories = Category::all();
  $error = null;
  $form = [
    'name' => '',
    'slug' => '',
    'parent_id' => '',
    'sort_order' => 0,
  ];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['name'] = trim($_POST['name'] ?? '');
    $form['slug'] = trim($_POST['slug'] ?? '');
    $form['parent_id'] = $_POST['parent_id'] ?? '';
    $form['sort_order'] = (int)($_POST['sort_order'] ?? 0);

    $parentId = $form['parent_id'] !== '' ? (int)$form['parent_id'] : null;

    if ($form['name'] === '') {
      $error = 'Tên danh mục không được để trống.';
    } else {
      if ($form['slug'] === '') {
        $form['slug'] = slugify($form['name']);
      }

      Category::create($parentId, $form['name'], $form['slug'], $form['sort_order']);

      $a = admin_user();
      if ($a) {
        AdminLog::add((int)$a['id'], 'category.create', 'categories', null, [
          'name' => $form['name'],
          'slug' => $form['slug'],
          'parent_id' => $parentId,
          'sort_order' => $form['sort_order'],
        ]);
      }

      $_SESSION['_flash'] = 'Đã thêm danh mục mới';
      redirect('/admin/categories');
    }
  }

  $mode = 'create';
  $action = url('/admin/categories/create');

  render_admin(
    __DIR__ . '/../app/views/admin/categories_form.php',
    compact('mode', 'action', 'form', 'error', 'allCategories')
  );
  exit;
}

if ($route === '/admin/categories/edit') {
  require_perm('categories.view');

  $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
  $category = $id > 0 ? Category::byId($id) : null;

  if (!$category) {
    http_response_code(404);
    echo 'Category not found';
    exit;
  }

  $allCategories = Category::all();
  $error = null;
  $form = $category;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['name'] = trim($_POST['name'] ?? '');
    $form['slug'] = trim($_POST['slug'] ?? '');
    $form['parent_id'] = $_POST['parent_id'] ?? '';
    $form['sort_order'] = (int)($_POST['sort_order'] ?? 0);

    $parentId = $form['parent_id'] !== '' ? (int)$form['parent_id'] : null;

    if ($parentId === $id) {
      $error = 'Danh mục cha không hợp lệ.';
    } elseif ($form['name'] === '') {
      $error = 'Tên danh mục không được để trống.';
    } else {
      if ($form['slug'] === '') {
        $form['slug'] = slugify($form['name']);
      }

      Category::update($id, $parentId, $form['name'], $form['slug'], $form['sort_order']);

      $a = admin_user();
      if ($a) {
        AdminLog::add((int)$a['id'], 'category.update', 'categories', $id, [
          'name' => $form['name'],
          'slug' => $form['slug'],
          'parent_id' => $parentId,
          'sort_order' => $form['sort_order'],
        ]);
      }

      $_SESSION['_flash'] = 'Đã cập nhật danh mục';
      redirect('/admin/categories');
    }
  }

  $mode = 'edit';
  $action = url('/admin/categories/edit?id=' . $id);

  render_admin(
    __DIR__ . '/../app/views/admin/categories_form.php',
    compact('mode', 'action', 'form', 'error', 'allCategories')
  );
  exit;
}

if ($route === '/admin/categories/delete') {
  require_perm('categories.view');

  $id = (int)($_GET['id'] ?? 0);

  if ($id > 0) {
    try {
      Category::delete($id);

      $a = admin_user();
      if ($a) {
        AdminLog::add((int)$a['id'], 'category.delete', 'categories', $id, null);
      }

      $_SESSION['_flash'] = 'Đã xóa danh mục';
    } catch (Throwable $e) {
      $_SESSION['_flash'] = 'Không thể xóa danh mục vì đang có sản phẩm liên kết.';
    }
  }

  redirect('/admin/categories');
}

if ($route === '/admin/orders') {
  require_perm('orders.view');

  $db = db();
  $q = trim($_GET['q'] ?? '');
  $status = trim($_GET['status'] ?? '');

  $where = ["1=1"];
  $params = [];

  if ($q !== '') {
    $where[] = "(CAST(id AS CHAR) LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
  }

  if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
  }

  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 20;

  $sqlCount = "SELECT COUNT(*) FROM orders WHERE " . implode(' AND ', $where);
  $stCount = $db->prepare($sqlCount);
  $stCount->execute($params);
  $total = (int)$stCount->fetchColumn();

  $pager = paginate_meta($total, $page, $perPage);

  $sql = "SELECT * FROM orders WHERE " . implode(' AND ', $where) . " ORDER BY id DESC LIMIT ? OFFSET ?";
  $st = $db->prepare($sql);

  $i = 1;
  foreach ($params as $val) {
    $st->bindValue($i++, $val);
  }
  $st->bindValue($i++, $pager['perPage'], PDO::PARAM_INT);
  $st->bindValue($i++, $pager['offset'], PDO::PARAM_INT);
  $st->execute();

  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/orders_list.php',
    compact('rows', 'q', 'status', 'flash', 'pager')
  );
  exit;
}

if ($route === '/admin/orders/view') {
  require_perm('orders.view');

  $id = (int)($_GET['id'] ?? 0);
  if ($id <= 0) {
    http_response_code(404);
    echo 'Order not found';
    exit;
  }

  $db = db();

  $st = $db->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
    LIMIT 1
  ");
  $st->execute([$id]);
  $order = $st->fetch(PDO::FETCH_ASSOC);

  if (!$order) {
    http_response_code(404);
    echo 'Order not found';
    exit;
  }

  $stItems = $db->prepare("
    SELECT oi.*, p.name AS product_name, p.thumbnail
    FROM order_items oi
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
  ");
  $stItems->execute([$id]);
  $items = $stItems->fetchAll(PDO::FETCH_ASSOC);

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/order_detail.php',
    compact('order', 'items', 'flash')
  );
  exit;
}

if ($route === '/admin/customers/view') {
  require_admin_auth();

  $id = (int)($_GET['id'] ?? 0);
  if ($id <= 0) {
    http_response_code(404);
    echo 'Customer not found';
    exit;
  }

  $db = db();

  $st = $db->prepare("
    SELECT
      u.*,
      0 AS is_locked
    FROM users u
    WHERE u.id = ? AND u.role = 'customer'
    LIMIT 1
  ");
  $st->execute([$id]);
  $customer = $st->fetch(PDO::FETCH_ASSOC);

  if (!$customer) {
    http_response_code(404);
    echo 'Customer not found';
    exit;
  }

  $stOrders = $db->prepare("
    SELECT id, total, status, payment_method, payment_status, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 20
  ");
  $stOrders->execute([$id]);
  $orders = $stOrders->fetchAll(PDO::FETCH_ASSOC);

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/customer_detail.php',
    compact('customer', 'orders', 'flash')
  );
  exit;
}

if ($route === '/admin/orders/update-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_perm('orders.edit');

  csrf_check();

  $id = (int)($_POST['id'] ?? 0);
  $newStatus = trim($_POST['status'] ?? '');

  $allowed = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];

  if ($id <= 0 || !in_array($newStatus, $allowed, true)) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Dữ liệu cập nhật không hợp lệ'];
    redirect('/admin/orders');
  }

  $db = db();

  $stOld = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
  $stOld->execute([$id]);
  $order = $stOld->fetch(PDO::FETCH_ASSOC);

  if (!$order) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy đơn hàng'];
    redirect('/admin/orders');
  }

  $shippingStatus = $order['shipping_status'] ?? null;

  if ($newStatus === 'confirmed') $shippingStatus = 'picking';
  if ($newStatus === 'shipping')  $shippingStatus = 'shipping';
  if ($newStatus === 'completed') $shippingStatus = 'delivered';
  if ($newStatus === 'cancelled') $shippingStatus = 'cancelled';

  $st = $db->prepare("
    UPDATE orders
    SET status = ?, shipping_status = ?, updated_at = NOW()
    WHERE id = ?
  ");
  $st->execute([$newStatus, $shippingStatus, $id]);

  $admin = admin_user();

  $log = $db->prepare("
    INSERT INTO order_status_logs
    (order_id, old_status, new_status, old_shipping_status, new_shipping_status, note, changed_by_admin_id, changed_by_type, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
  ");
  $log->execute([
    $id,
    $order['status'] ?? null,
    $newStatus,
    $order['shipping_status'] ?? null,
    $shippingStatus,
    'Cập nhật trạng thái từ trang đơn hàng',
    $admin['id'] ?? null,
    'admin'
  ]);

  if (!empty($admin['id'])) {
    AdminLog::add((int)$admin['id'], 'order.update_status', 'orders', $id, [
      'old_status' => $order['status'] ?? null,
      'new_status' => $newStatus,
      'old_shipping_status' => $order['shipping_status'] ?? null,
      'new_shipping_status' => $shippingStatus,
    ]);
  }

  $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã cập nhật trạng thái đơn hàng'];
  redirect('/admin/orders');
}

if ($route === '/admin/customers') {
  require_admin_auth();

  $db = db();
  $q = trim($_GET['q'] ?? '');

  $where = ["role = 'customer'"];
  $params = [];

  if ($q !== '') {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
  }

  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 20;

  $sqlCount = "SELECT COUNT(*) FROM users WHERE " . implode(' AND ', $where);
  $stCount = $db->prepare($sqlCount);
  $stCount->execute($params);
  $total = (int)$stCount->fetchColumn();

  $pager = [
    'page' => $page,
    'perPage' => $perPage,
    'total' => $total,
    'pages' => max(1, (int)ceil($total / $perPage)),
    'offset' => ($page - 1) * $perPage,
  ];

  $sql = "
    SELECT
      u.id,
      u.name,
      u.email,
      u.phone,
      u.created_at,
      CASE
        WHEN EXISTS(SELECT 1 FROM user_locks ul WHERE ul.user_id = u.id AND ul.is_locked = 1)
        THEN 1 ELSE 0
      END AS is_locked,
      COUNT(DISTINCT o.id) AS orders_count,
      COALESCE(SUM(CASE WHEN o.status = 'completed' THEN o.total ELSE 0 END), 0) AS total_spent,
      MAX(o.created_at) AS last_order_at
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY u.id, u.name, u.email, u.phone, u.created_at
    ORDER BY u.id DESC
    LIMIT ? OFFSET ?
  ";

  $st = $db->prepare($sql);
  $i = 1;
  foreach ($params as $val) {
    $st->bindValue($i++, $val);
  }
  $st->bindValue($i++, $pager['perPage'], PDO::PARAM_INT);
  $st->bindValue($i++, $pager['offset'], PDO::PARAM_INT);
  $st->execute();
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/customers_list.php',
    compact('rows', 'q', 'flash', 'pager')
  );
  exit;
}


if ($route === '/admin/customers/toggle-lock' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_admin_auth();
  csrf_check();

  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Khách hàng không hợp lệ'];
    redirect('/admin/customers');
  }

  $db = db();

  $st = $db->prepare("SELECT * FROM user_locks WHERE user_id = ? LIMIT 1");
  $st->execute([$id]);
  $lock = $st->fetch(PDO::FETCH_ASSOC);

  if ($lock) {
    $newLocked = ((int)$lock['is_locked'] === 1) ? 0 : 1;
    $up = $db->prepare("UPDATE user_locks SET is_locked = ?, updated_at = NOW() WHERE user_id = ?");
    $up->execute([$newLocked, $id]);
  } else {
    $ins = $db->prepare("
      INSERT INTO user_locks (user_id, is_locked, reason, updated_at)
      VALUES (?, 1, ?, NOW())
    ");
    $ins->execute([$id, 'Khóa từ trang admin khách hàng']);
    $newLocked = 1;
  }

  $_SESSION['_flash'] = [
    'type' => 'success',
    'msg' => $newLocked ? 'Đã khóa khách hàng' : 'Đã mở khóa khách hàng'
  ];
  redirect('/admin/customers');
}

if ($route === '/admin/inventory') {
  require_perm('inventory.view');

  $q = trim($_GET['q'] ?? '') ?: null;
  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 30;

  $total = Inventory::adminCountVariants($q);
  $pager = paginate_meta($total, $page, $perPage);
  $rows = Inventory::adminListVariants($pager['perPage'], $pager['offset'], $q);

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/inventory_list.php',
    compact('rows', 'pager', 'q', 'flash')
  );
  exit;
}

if ($route === '/admin/inventory/history') {
  require_perm('inventory.view');

  $q = trim($_GET['q'] ?? '') ?: null;
  $type = trim($_GET['type'] ?? '') ?: null;
  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 30;

  $total = Inventory::historyCount($q, $type);
  $pager = paginate_meta($total, $page, $perPage);
  $rows = Inventory::history($pager['perPage'], $pager['offset'], $q, $type);

  render_admin(
    __DIR__ . '/../app/views/admin/inventory_history.php',
    compact('rows', 'pager', 'q', 'type')
  );
  exit;
}

if ($route === '/admin/payments') {
  require_admin_auth();

  $db = db();
  $q = trim($_GET['q'] ?? '');
  $status = trim($_GET['status'] ?? '');

  $where = ["1=1"];
  $params = [];

  if ($q !== '') {
    $where[] = "(CAST(id AS CHAR) LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR payment_method LIKE ? OR payment_status LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
  }

  if ($status !== '') {
    $where[] = "payment_status = ?";
    $params[] = $status;
  }

  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 20;

  $sqlCount = "SELECT COUNT(*) FROM orders WHERE " . implode(' AND ', $where);
  $stCount = $db->prepare($sqlCount);
  $stCount->execute($params);
  $total = (int)$stCount->fetchColumn();

  $pager = [
    'page' => $page,
    'pages' => max(1, (int)ceil($total / $perPage)),
    'perPage' => $perPage,
    'offset' => ($page - 1) * $perPage,
    'total' => $total,
  ];

  $sql = "SELECT * FROM orders WHERE " . implode(' AND ', $where) . " ORDER BY id DESC LIMIT ? OFFSET ?";
  $st = $db->prepare($sql);

  $i = 1;
  foreach ($params as $val) {
    $st->bindValue($i++, $val);
  }
  $st->bindValue($i++, $pager['perPage'], PDO::PARAM_INT);
  $st->bindValue($i++, $pager['offset'], PDO::PARAM_INT);
  $st->execute();

  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/payments_list.php',
    compact('rows', 'q', 'status', 'flash', 'pager')
  );
  exit;
}

if ($route === '/admin/payments/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_admin_auth();
  csrf_check();

  $id = (int)($_POST['id'] ?? 0);
  $paymentStatus = trim($_POST['payment_status'] ?? '');
  $paymentRef = trim($_POST['payment_ref'] ?? '');

  $allowed = ['unpaid', 'paid', 'failed', 'refunded'];

  if ($id <= 0 || !in_array($paymentStatus, $allowed, true)) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Dữ liệu thanh toán không hợp lệ'];
    redirect('/admin/payments');
  }

  $db = db();

  $stOld = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
  $stOld->execute([$id]);
  $order = $stOld->fetch(PDO::FETCH_ASSOC);

  if (!$order) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy đơn hàng'];
    redirect('/admin/payments');
  }

  $st = $db->prepare("
    UPDATE orders
    SET payment_status = ?, payment_ref = ?, updated_at = NOW()
    WHERE id = ?
  ");
  $st->execute([
    $paymentStatus,
    ($paymentRef !== '' ? $paymentRef : null),
    $id
  ]);

  $admin = admin_user();
  if (!empty($admin['id'])) {
    AdminLog::add((int)$admin['id'], 'payment.update', 'orders', $id, [
      'old_payment_status' => $order['payment_status'] ?? null,
      'new_payment_status' => $paymentStatus,
      'payment_ref' => $paymentRef,
    ]);
  }

  $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã cập nhật thanh toán'];
  redirect('/admin/payments');
}

if ($route === '/admin/settings/payment') {
  require_admin_auth();

  $setting = PaymentSetting::get();
  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/payment_settings.php',
    compact('setting', 'flash')
  );
  exit;
}

if ($route === '/admin/settings/payment/save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_admin_auth();
  csrf_check();

  PaymentSetting::save([
    'bank_account' => $_POST['bank_account'] ?? '',
    'bank_name'    => $_POST['bank_name'] ?? '',
    'bank_owner'   => $_POST['bank_owner'] ?? '',
    'qr_image'     => $_POST['qr_image'] ?? '',
  ]);

  $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã lưu cấu hình nhận tiền'];
  redirect('/admin/settings/payment');
}

if ($route === '/admin/shipping/settings') {
  require_admin_auth();

  $setting = Shipping::settings();
  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/shipping_settings.php',
    compact('setting', 'flash')
  );
  exit;
}

if ($route === '/admin/shipping/zones') {
  require_admin_auth();

  $q = trim($_GET['q'] ?? '') ?: null;
  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 30;

  $total = Shipping::zoneCount($q);
  $pager = paginate_meta($total, $page, $perPage);
  $rows = Shipping::zoneList($pager['perPage'], $pager['offset'], $q);

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/shipping_zones_list.php',
    compact('rows', 'pager', 'q', 'flash')
  );
  exit;
}

if ($route === '/admin/shipments') {
  require_admin_auth();

  $q = trim($_GET['q'] ?? '') ?: null;
  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 30;

  $total = Shipment::adminCount($q);
  $pager = paginate_meta($total, $page, $perPage);
  $rows = Shipment::adminList($pager['perPage'], $pager['offset'], $q);

  render_admin(
    __DIR__ . '/../app/views/admin/shipments_list.php',
    compact('rows', 'pager', 'q')
  );
  exit;
}

if ($route === '/admin/shipments/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_admin_auth();
  csrf_check();

  $id = (int)($_POST['id'] ?? 0);
  $carrier = trim($_POST['carrier'] ?? 'manual');
  $trackingCode = trim($_POST['tracking_code'] ?? '');
  $status = trim($_POST['status'] ?? '');

  $allowedCarrier = ['manual', 'ghn', 'ghtk', 'vnpay'];
  $allowedStatus = ['created', 'confirmed', 'picking', 'shipping', 'delivered', 'cancelled', 'returned'];

  if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Dữ liệu vận đơn không hợp lệ'];
    redirect('/admin/shipments');
  }

  if (!in_array($carrier, $allowedCarrier, true)) {
    $carrier = 'manual';
  }

  $db = db();

  $stOld = $db->prepare("SELECT * FROM shipments WHERE id = ? LIMIT 1");
  $stOld->execute([$id]);
  $shipment = $stOld->fetch(PDO::FETCH_ASSOC);

  if (!$shipment) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy vận đơn'];
    redirect('/admin/shipments');
  }

  $st = $db->prepare("
    UPDATE shipments
    SET carrier = ?, tracking_code = ?, status = ?, updated_at = NOW()
    WHERE id = ?
  ");
  $st->execute([
    $carrier,
    ($trackingCode !== '' ? $trackingCode : null),
    $status,
    $id
  ]);

  $admin = admin_user();
  if (!empty($admin['id'])) {
    AdminLog::add((int)$admin['id'], 'shipment.update', 'shipments', $id, [
      'old_status' => $shipment['status'] ?? null,
      'new_status' => $status,
      'old_tracking_code' => $shipment['tracking_code'] ?? null,
      'new_tracking_code' => $trackingCode,
      'carrier' => $carrier,
    ]);
  }

  $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã cập nhật vận đơn'];
  redirect('/admin/shipments');
}

if ($route === '/admin/reports') {
  require_admin_auth();

  $from = $_GET['from'] ?? date('Y-m-01');
  $to   = $_GET['to'] ?? date('Y-m-d');
  $groupBy = $_GET['group_by'] ?? 'day';

  if (!in_array($groupBy, ['day', 'month'], true)) {
    $groupBy = 'day';
  }

  $revenueTotal = Report::revenueTotal($from, $to);
  $revenueSeries = Report::revenueSeries($from, $to, $groupBy);
  $topProducts = Report::topProducts($from, $to, 10);
  $inventorySummary = Report::inventorySummary(5);
  $profit = Report::profit($from, $to);
  $newCustomers = Report::newCustomers($from, $to);
  $cancelRate = Report::cancelRate($from, $to);
  $orderStatusPie = Report::orderStatusPie($from, $to);

  render_admin(
    __DIR__ . '/../app/views/admin/reports.php',
    compact(
      'from',
      'to',
      'groupBy',
      'revenueTotal',
      'revenueSeries',
      'topProducts',
      'inventorySummary',
      'profit',
      'newCustomers',
      'cancelRate',
      'orderStatusPie'
    )
  );
  exit;
}

if ($route === '/admin/reports/export') {
  require_admin_auth();

  $from = $_GET['from'] ?? date('Y-m-01');
  $to   = $_GET['to'] ?? date('Y-m-d');

  $revenueTotal = Report::revenueTotal($from, $to);
  $revenueSeries = Report::revenueSeries($from, $to, 'day');
  $topProducts = Report::topProducts($from, $to, 10);
  $inventorySummary = Report::inventorySummary(10);
  $profit = Report::profit($from, $to);
  $newCustomers = Report::newCustomers($from, $to);
  $cancelRate = Report::cancelRate($from, $to);
  $orderStatusPie = Report::orderStatusPie($from, $to);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=bao_cao_' . $from . '_den_' . $to . '.csv');

  $out = fopen('php://output', 'w');

  // UTF-8 BOM cho Excel
  fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

  fputcsv($out, ['BÁO CÁO TỔNG QUAN']);
  fputcsv($out, ['Từ ngày', $from]);
  fputcsv($out, ['Đến ngày', $to]);
  fputcsv($out, []);

  fputcsv($out, ['CHỈ SỐ', 'GIÁ TRỊ']);
  fputcsv($out, ['Doanh thu hoàn tất', (int)$revenueTotal]);
  fputcsv($out, ['Lợi nhuận tạm tính', (int)($profit['profit'] ?? 0)]);
  fputcsv($out, ['Giá vốn tạm tính', (int)($profit['cost'] ?? 0)]);
  fputcsv($out, ['Khách hàng mới', (int)$newCustomers]);
  fputcsv($out, ['Tổng đơn', (int)($cancelRate['total_orders'] ?? 0)]);
  fputcsv($out, ['Đơn hủy', (int)($cancelRate['cancelled_orders'] ?? 0)]);
  fputcsv($out, ['Tỷ lệ hủy (%)', (float)($cancelRate['rate'] ?? 0)]);
  fputcsv($out, []);

  fputcsv($out, ['DOANH THU THEO THỜI GIAN']);
  fputcsv($out, ['Mốc thời gian', 'Doanh thu']);
  foreach ($revenueSeries as $row) {
    fputcsv($out, [$row['label'] ?? '', (int)($row['revenue'] ?? 0)]);
  }
  fputcsv($out, []);

  fputcsv($out, ['TRẠNG THÁI ĐƠN HÀNG']);
  fputcsv($out, ['Trạng thái', 'Số lượng']);
  foreach ($orderStatusPie as $row) {
    fputcsv($out, [$row['status'] ?? '', (int)($row['total'] ?? 0)]);
  }
  fputcsv($out, []);

  fputcsv($out, ['TOP SẢN PHẨM']);
  fputcsv($out, ['Sản phẩm', 'SKU', 'Số lượng bán', 'Doanh thu']);
  foreach ($topProducts as $row) {
    fputcsv($out, [
      $row['product_name'] ?? '',
      $row['sku'] ?? '',
      (int)($row['total_qty'] ?? 0),
      (int)($row['total_revenue'] ?? 0)
    ]);
  }
  fputcsv($out, []);

  fputcsv($out, ['TỒN KHO THẤP']);
  fputcsv($out, ['Sản phẩm', 'SKU', 'Tồn kho']);
  foreach ($inventorySummary as $row) {
    fputcsv($out, [
      $row['name'] ?? '',
      $row['sku'] ?? '',
      (int)($row['total_stock'] ?? 0)
    ]);
  }

  fclose($out);
  exit;
}

if ($route === '/admin/backup') {
  require_admin_auth();

  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/backup.php',
    compact('flash')
  );
  exit;
}

if ($route === '/admin/products/create') {
  require_perm('products.view');

  $categories = db()->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

  $error = null;
  $form = [
    'name' => '',
    'sku' => '',
    'slug' => '',
    'category_id' => $categories[0]['id'] ?? 0,
    'price' => 0,
    'compare_at_price' => '',
    'thumbnail' => '',
    'description' => '',
    'status' => 'active',
    'is_new' => 1,
  ];
  $variants = [];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['name'] = trim($_POST['name'] ?? '');
    $form['sku'] = trim($_POST['sku'] ?? '');
    $form['slug'] = trim($_POST['slug'] ?? '');
    $form['category_id'] = (int)($_POST['category_id'] ?? 0);
    $form['price'] = max(0, (int)($_POST['price'] ?? 0));
    $form['compare_at_price'] = ($_POST['compare_at_price'] ?? '') !== '' ? max(0, (int)$_POST['compare_at_price']) : null;
    $form['description'] = trim($_POST['description'] ?? '');
    $form['status'] = in_array($_POST['status'] ?? '', ['active', 'draft'], true) ? $_POST['status'] : 'active';
    $form['is_new'] = (int)(($_POST['is_new'] ?? '1') === '1');

    if ($form['slug'] === '') {
      $form['slug'] = slugify($form['name']);
    }

    $upload = upload_image('thumbnail_file', 'product');
    if ($upload) {
      $form['thumbnail'] = $upload;
    }

    $sizes = $_POST['v_size'] ?? [];
    $colors = $_POST['v_color'] ?? [];
    $stocks = $_POST['v_stock'] ?? [];

    for ($i = 0; $i < max(count($sizes), count($colors), count($stocks)); $i++) {
      $size = trim($sizes[$i] ?? '');
      $color = trim($colors[$i] ?? '');
      $stock = max(0, (int)($stocks[$i] ?? 0));

      if ($size === '' || $color === '') {
        continue;
      }

      $variants[] = [
        'size' => $size,
        'color' => $color,
        'stock' => $stock,
      ];
    }

    if ($form['name'] === '') {
      $error = 'Tên sản phẩm không được để trống.';
    } elseif ($form['category_id'] <= 0) {
      $error = 'Bạn chưa chọn danh mục.';
    } else {
      $newId = Product::create($form);
      Product::setVariants($newId, $variants);

      $a = admin_user();
      if ($a) {
        AdminLog::add((int)$a['id'], 'product.create', 'products', $newId, [
          'name' => $form['name'],
          'sku' => $form['sku'],
        ]);
      }

      $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã thêm sản phẩm mới'];
      redirect('/admin/products');
    }
  }

  $mode = 'create';
  $action = url('/admin/products/create');
  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/products_form.php',
    compact('mode', 'action', 'form', 'variants', 'categories', 'error', 'flash')
  );
  exit;
}

if ($route === '/admin/products/edit') {
  require_perm('products.view');

  $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
  $product = $id > 0 ? Product::byId($id) : null;

  if (!$product) {
    http_response_code(404);
    echo 'Product not found';
    exit;
  }

  $categories = db()->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

  $error = null;
  $form = $product;
  $variants = Product::variants($id);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['name'] = trim($_POST['name'] ?? '');
    $form['sku'] = trim($_POST['sku'] ?? '');
    $form['slug'] = trim($_POST['slug'] ?? '');
    $form['category_id'] = (int)($_POST['category_id'] ?? 0);
    $form['price'] = max(0, (int)($_POST['price'] ?? 0));
    $form['compare_at_price'] = ($_POST['compare_at_price'] ?? '') !== '' ? max(0, (int)$_POST['compare_at_price']) : null;
    $form['description'] = trim($_POST['description'] ?? '');
    $form['status'] = in_array($_POST['status'] ?? '', ['active', 'draft'], true) ? $_POST['status'] : 'active';
    $form['is_new'] = (int)(($_POST['is_new'] ?? '1') === '1');

    if ($form['slug'] === '') {
      $form['slug'] = slugify($form['name']);
    }

    $upload = upload_image('thumbnail_file', 'product');
    if ($upload) {
      $form['thumbnail'] = $upload;
    } else {
      $form['thumbnail'] = $product['thumbnail'] ?? '';
    }

    $variants = [];
    $sizes = $_POST['v_size'] ?? [];
    $colors = $_POST['v_color'] ?? [];
    $stocks = $_POST['v_stock'] ?? [];

    for ($i = 0; $i < max(count($sizes), count($colors), count($stocks)); $i++) {
      $size = trim($sizes[$i] ?? '');
      $color = trim($colors[$i] ?? '');
      $stock = max(0, (int)($stocks[$i] ?? 0));

      if ($size === '' || $color === '') {
        continue;
      }

      $variants[] = [
        'size' => $size,
        'color' => $color,
        'stock' => $stock,
      ];
    }

    if ($form['name'] === '') {
      $error = 'Tên sản phẩm không được để trống.';
    } elseif ($form['category_id'] <= 0) {
      $error = 'Bạn chưa chọn danh mục.';
    } else {
      Product::update($id, $form);
      Product::setVariants($id, $variants);

      $a = admin_user();
      if ($a) {
        AdminLog::add((int)$a['id'], 'product.update', 'products', $id, [
          'name' => $form['name'],
          'sku' => $form['sku'],
        ]);
      }

      $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã cập nhật sản phẩm'];
      redirect('/admin/products');
    }
  }

  $mode = 'edit';
  $action = url('/admin/products/edit?id=' . $id);
  $flash = $_SESSION['_flash'] ?? null;
  unset($_SESSION['_flash']);

  render_admin(
    __DIR__ . '/../app/views/admin/products_form.php',
    compact('mode', 'action', 'form', 'variants', 'categories', 'error', 'flash')
  );
  exit;
}

if ($route === '/admin/products/delete') {
  require_perm('products.view');

  $id = (int)($_GET['id'] ?? 0);

  if ($id > 0) {
    $a = admin_user();

    try {
      Product::delete($id);

      if ($a) {
        AdminLog::add((int)$a['id'], 'product.delete', 'products', $id, null);
      }

      $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã xóa sản phẩm'];
    } catch (Throwable $e) {
      Product::setStatus($id, 'draft');

      if ($a) {
        AdminLog::add((int)$a['id'], 'product.soft_delete', 'products', $id, [
          'reason' => 'fallback_to_draft'
        ]);
      }

      $_SESSION['_flash'] = [
        'type' => 'warning',
        'msg' => 'Sản phẩm đã phát sinh dữ liệu liên quan nên không xóa cứng được. Hệ thống đã chuyển sang trạng thái draft.'
      ];
    }
  }

  redirect('/admin/products');
}

  if ($route === '/api/chat') {
    header('Content-Type: application/json; charset=utf-8');

    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: [];
    $text = trim((string)($body['message'] ?? ''));

    if ($text === '') {
      echo json_encode(['ok' => false, 'reply' => 'Bạn hãy nhập nội dung nhé.']);
      exit;
    }

    $_SESSION['_chat_last'] = $_SESSION['_chat_last'] ?? 0;
    if (time() - (int)$_SESSION['_chat_last'] < 1) {
      echo json_encode(['ok' => true, 'reply' => 'Bạn gửi chậm 1 chút nhé 🙂']);
      exit;
    }
    $_SESSION['_chat_last'] = time();

    require __DIR__ . '/../backend/api/chat.php';
    exit;
  }

  if ($route === '/admin/shipments/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_admin_auth();
  csrf_check();

  $id = (int)($_POST['id'] ?? 0);
  $carrier = trim($_POST['carrier'] ?? 'manual');
  $trackingCode = trim($_POST['tracking_code'] ?? '');
  $status = trim($_POST['status'] ?? '');

  $allowedCarrier = ['manual', 'ghn', 'ghtk', 'vnpay'];
  $allowedStatus = ['created', 'confirmed', 'picking', 'shipping', 'delivered', 'cancelled', 'returned'];

  if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Dữ liệu vận đơn không hợp lệ'];
    redirect('/admin/shipments');
  }

  if (!in_array($carrier, $allowedCarrier, true)) {
    $carrier = 'manual';
  }

  $db = db();

  $stOld = $db->prepare("SELECT * FROM shipments WHERE id = ? LIMIT 1");
  $stOld->execute([$id]);
  $shipment = $stOld->fetch(PDO::FETCH_ASSOC);

  if (!$shipment) {
    $_SESSION['_flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy vận đơn'];
    redirect('/admin/shipments');
  }

  $st = $db->prepare("
    UPDATE shipments
    SET carrier = ?, tracking_code = ?, status = ?, updated_at = NOW()
    WHERE id = ?
  ");
  $st->execute([
    $carrier,
    ($trackingCode !== '' ? $trackingCode : null),
    $status,
    $id
  ]);

  $admin = admin_user();
  if (!empty($admin['id'])) {
    AdminLog::add((int)$admin['id'], 'shipment.update', 'shipments', $id, [
      'old_status' => $shipment['status'] ?? null,
      'new_status' => $status,
      'old_tracking_code' => $shipment['tracking_code'] ?? null,
      'new_tracking_code' => $trackingCode,
      'carrier' => $carrier,
    ]);
  }

  $_SESSION['_flash'] = ['type' => 'success', 'msg' => 'Đã cập nhật vận đơn'];
  redirect('/admin/shipments');
}

if ($route === '/admin/reports/export') {
  require_admin_auth();

  $from = $_GET['from'] ?? date('Y-m-01');
  $to   = $_GET['to'] ?? date('Y-m-d');
  $groupBy = $_GET['group_by'] ?? 'day';

  if (!in_array($groupBy, ['day', 'month'], true)) {
    $groupBy = 'day';
  }

  $revenueTotal = Report::revenueTotal($from, $to);
  $revenueSeries = Report::revenueSeries($from, $to, $groupBy);
  $topProducts = Report::topProducts($from, $to, 10);
  $inventorySummary = Report::inventorySummary(10);
  $profit = Report::profit($from, $to);
  $newCustomers = Report::newCustomers($from, $to);
  $cancelRate = Report::cancelRate($from, $to);
  $orderStatusPie = Report::orderStatusPie($from, $to);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=bao_cao_' . $from . '_den_' . $to . '.csv');

  $out = fopen('php://output', 'w');
  fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

  fputcsv($out, ['BÁO CÁO TỔNG QUAN']);
  fputcsv($out, ['Từ ngày', $from]);
  fputcsv($out, ['Đến ngày', $to]);
  fputcsv($out, ['Gộp dữ liệu', $groupBy]);
  fputcsv($out, []);

  fputcsv($out, ['CHỈ SỐ', 'GIÁ TRỊ']);
  fputcsv($out, ['Doanh thu hoàn tất', (int)$revenueTotal]);
  fputcsv($out, ['Lợi nhuận tạm tính', (int)($profit['profit'] ?? 0)]);
  fputcsv($out, ['Giá vốn tạm tính', (int)($profit['cost'] ?? 0)]);
  fputcsv($out, ['Khách hàng mới', (int)$newCustomers]);
  fputcsv($out, ['Tổng đơn', (int)($cancelRate['total_orders'] ?? 0)]);
  fputcsv($out, ['Đơn hủy', (int)($cancelRate['cancelled_orders'] ?? 0)]);
  fputcsv($out, ['Tỷ lệ hủy (%)', (float)($cancelRate['rate'] ?? 0)]);
  fputcsv($out, []);

  fputcsv($out, ['DOANH THU THEO THỜI GIAN']);
  fputcsv($out, ['Mốc thời gian', 'Doanh thu']);
  foreach ($revenueSeries as $row) {
    fputcsv($out, [$row['label'] ?? '', (int)($row['revenue'] ?? 0)]);
  }
  fputcsv($out, []);

  fputcsv($out, ['TRẠNG THÁI ĐƠN HÀNG']);
  fputcsv($out, ['Trạng thái', 'Số lượng']);
  foreach ($orderStatusPie as $row) {
    fputcsv($out, [$row['status'] ?? '', (int)($row['total'] ?? 0)]);
  }
  fputcsv($out, []);

  fputcsv($out, ['TOP SẢN PHẨM']);
  fputcsv($out, ['Sản phẩm', 'SKU', 'Số lượng bán', 'Doanh thu']);
  foreach ($topProducts as $row) {
    fputcsv($out, [
      $row['product_name'] ?? '',
      $row['sku'] ?? '',
      (int)($row['total_qty'] ?? 0),
      (int)($row['total_revenue'] ?? 0)
    ]);
  }
  fputcsv($out, []);

  fputcsv($out, ['TỒN KHO THẤP']);
  fputcsv($out, ['Sản phẩm', 'SKU', 'Tồn kho']);
  foreach ($inventorySummary as $row) {
    fputcsv($out, [
      $row['name'] ?? '',
      $row['sku'] ?? '',
      (int)($row['total_stock'] ?? 0)
    ]);
  }

  fclose($out);
  exit;
}

  http_response_code(404);
  echo "Route not found";
} catch (Throwable $e) {
  http_response_code(500);
  echo "Error: " . e($e->getMessage());
}