<?php
declare(strict_types=1);

// =========================
// BOOTSTRAP
// =========================
$bootstrap = __DIR__ . '/app/config/bootstrap.php';
if (!is_file($bootstrap)) {
  http_response_code(500);
  echo "Bootstrap file not found: " . htmlspecialchars($bootstrap);
  exit;
}
require $bootstrap;

// =========================
// SIMPLE AUTOLOADER
// Struktur: /app/controllers, /app/lib, /app/config
// Namespace: App\Controllers\X => app/controllers/X.php
// =========================
spl_autoload_register(function (string $class) {
  $prefix = 'App\\';
  if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;

  $relative = substr($class, strlen($prefix)); // ex: Controllers\Admin\AuthController
  $relativePath = str_replace('\\', '/', $relative) . '.php';

  // Map folder namespace -> folder nyata (lowercase)
  $relativePath = preg_replace('#^Controllers/#', 'controllers/', $relativePath);
  $relativePath = preg_replace('#^Lib/#', 'lib/', $relativePath);
  $relativePath = preg_replace('#^Config/#', 'config/', $relativePath);

  $file = __DIR__ . '/app/' . $relativePath;
  if (is_file($file)) require_once $file;
});

use App\Lib\Auth;
use App\Lib\MobileGuard;

// =========================
// ROUTER
// =========================
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = rtrim($path, '/') ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// =========================
// MOBILE ONLY GUARD (kecuali homepage "/" dan semua "/admin/*")
// Home "fastpaytrack.com/" tidak diblokir.
// Admin panel desktop-friendly, jadi harus di-allow.
// Webhook juga tidak diblokir.
// =========================
MobileGuard::enforce($path, $method, [
  '/',                 // homepage publik
  '/admin',            // allow admin area
  '/admin/login',
  '/admin/login/password',
  '/admin/logout',
  '/webhook/stripe',
  '/webhook/midtrans',
]);

$routes = [
  // =========================
  // HOME
  // =========================
  'GET /' => ['App\Controllers\HomeController', 'index'],

  // =========================
  // AUTH USER
  // =========================
  'GET /login' => ['App\Controllers\AuthController', 'showLogin'],
  'POST /login' => ['App\Controllers\AuthController', 'login'],
  'GET /register' => ['App\Controllers\AuthController', 'showRegister'],
  'POST /register' => ['App\Controllers\AuthController', 'register'],
  'GET /logout' => ['App\Controllers\AuthController', 'logout'],

  // OTP
  'GET /otp' => ['App\Controllers\AuthController', 'showOtp'],
  'POST /otp' => ['App\Controllers\AuthController', 'verifyOtp'],
  'POST /otp/resend' => ['App\Controllers\AuthController', 'resendOtp'],

  // Forgot/Reset
  'GET /forgot' => ['App\Controllers\AuthController', 'showForgot'],
  'POST /forgot' => ['App\Controllers\AuthController', 'sendReset'],
  'GET /reset' => ['App\Controllers\AuthController', 'showReset'],
  'POST /reset' => ['App\Controllers\AuthController', 'resetPassword'],

  // Dashboard user
  'GET /dashboard' => ['App\Controllers\DashboardController', 'index'],

  // Products (user)
  'GET /products' => ['App\Controllers\ProductsController', 'index'],
  'GET /product'  => ['App\Controllers\ProductsController', 'detail'], // pakai salah satu saja

  // Cart & Checkout
  'POST /cart/add' => ['App\Controllers\CartController', 'add'],
  'POST /cart/update' => ['App\Controllers\CartController', 'update'],
  'POST /cart/remove' => ['App\Controllers\CartController', 'remove'],
  'GET /checkout' => ['App\Controllers\CheckoutController', 'show'],
  'POST /checkout' => ['App\Controllers\CheckoutController', 'createOrder'],

  // Orders
  'GET /orders' => ['App\Controllers\CheckoutController', 'orders'],
  'GET /success' => ['App\Controllers\CheckoutController', 'success'],
  'GET /order' => ['App\Controllers\OrderController', 'detail'],

  // Wallet
  'GET /wallet/transfer' => ['App\Controllers\WalletController', 'transferForm'],
  'POST /wallet/transfer' => ['App\Controllers\WalletController', 'transferDo'],
  'GET /wallet/transfer/history' => ['App\Controllers\WalletController', 'transferHistory'],
  'GET /wallet/transfer/success' => ['App\Controllers\WalletController', 'transferSuccess'],
  'GET /wallet/transfer/failed' => ['App\Controllers\WalletController', 'transferFailed'],

  // PIN
  'GET /pin' => ['App\Controllers\PinController', 'show'],
  'POST /pin' => ['App\Controllers\PinController', 'save'],

  // Profile
  'GET /profile' => ['App\Controllers\ProfileController', 'index'],
  'POST /profile/update' => ['App\Controllers\ProfileController', 'update'],

  // TopUp
  'GET /topup' => ['App\Controllers\TopupController', 'show'],
  'POST /topup' => ['App\Controllers\TopupController', 'create'],
  'GET /topup/history' => ['App\Controllers\TopupController', 'history'],

  // Invoice
  'GET /invoice' => ['App\Controllers\InvoiceController', 'show'],
  'GET /invoice/html' => ['App\Controllers\InvoiceController', 'downloadHtml'],
  'GET /invoice/pdf' => ['App\Controllers\InvoiceController', 'pdf'],
  'POST /invoice/resend' => ['App\Controllers\InvoiceController', 'resend'],

  // Payment
  'GET /pay' => ['App\Controllers\PaymentController', 'payPage'],
  'GET /pay/stripe' => ['App\Controllers\PaymentController', 'stripeStart'],
  'GET /pay/paypal' => ['App\Controllers\PaymentController', 'paypalStart'],
  'GET /pay/paypal/return' => ['App\Controllers\PaymentController', 'paypalReturn'],
  'GET /pay/paypal/cancel' => ['App\Controllers\PaymentController', 'paypalCancel'],
  'GET /pay/qris' => ['App\Controllers\PaymentController', 'qrisStart'],
  'POST /pay/balance' => ['App\Controllers\PaymentController', 'balancePay'],

  // Settings main
  'GET /settings' => ['App\Controllers\SettingsController', 'index'],
  'GET /settings/support' => ['App\Controllers\SettingsController', 'index'],

  // Notification Settings
  'GET /settings/notifications' => ['App\Controllers\NotificationController', 'index'],
  'POST /settings/notifications/toggle-login-email' => ['App\Controllers\NotificationController', 'toggleLoginEmail'],

  // Security
  'GET /settings/security' => ['App\Controllers\SettingsController', 'security'],
  'GET /settings/security/revoke' => ['App\Controllers\SettingsController', 'security_revoke'],
  'GET /settings/security/revoke-others' => ['App\Controllers\SettingsController', 'security_revoke_others'],

  // =========================
  // ADMIN PANEL
  // (sesuai App\Controllers\Admin\AuthController yang kamu upload)
  // =========================
  'GET /admin/login' => ['App\Controllers\Admin\AuthController', 'login'],
  'POST /admin/login/username' => ['App\Controllers\Admin\AuthController', 'loginUsername'],
  'POST /admin/login/password' => ['App\Controllers\Admin\AuthController', 'loginPassword'],
  'POST /admin/login/key' => ['App\Controllers\Admin\AuthController', 'loginKey'],
  'GET /admin/logout' => ['App\Controllers\Admin\AuthController', 'logout'],

  'GET /admin/dashboard' => ['App\Controllers\Admin\DashboardController', 'index'],

  // Orders
  'GET /admin/orders' => ['App\Controllers\Admin\OrdersController', 'index'],
  'GET /admin/orders/send' => ['App\Controllers\Admin\OrdersController', 'sendForm'],
  'POST /admin/orders/send' => ['App\Controllers\Admin\OrdersController', 'sendDo'],

  // Products
  'GET /admin/products' => ['App\Controllers\Admin\ProductsController', 'index'],
  'GET /admin/products/create' => ['App\Controllers\Admin\ProductsController', 'createForm'],
  'POST /admin/products/create' => ['App\Controllers\Admin\ProductsController', 'createDo'],
  'GET /admin/products/edit' => ['App\Controllers\Admin\ProductsController', 'editForm'],
  'POST /admin/products/edit' => ['App\Controllers\Admin\ProductsController', 'editDo'],
  'POST /admin/products/delete' => ['App\Controllers\Admin\ProductsController', 'deleteDo'],

  // Users
  'GET /admin/users' => ['App\Controllers\Admin\UsersController', 'index'],
  'GET /admin/users/create' => ['App\Controllers\Admin\UsersController', 'createForm'],
  'POST /admin/users/create' => ['App\Controllers\Admin\UsersController', 'createDo'],
  'GET /admin/users/edit' => ['App\Controllers\Admin\UsersController', 'editForm'],
  'POST /admin/users/edit' => ['App\Controllers\Admin\UsersController', 'editDo'],
  'POST /admin/users/delete' => ['App\Controllers\Admin\UsersController', 'deleteDo'],
  'POST /admin/users/balance' => ['App\Controllers\Admin\UsersController', 'adjustBalanceDo'],

  // Transactions
  'GET /admin/transactions' => ['App\Controllers\Admin\TransactionsController', 'index'],

  // Manage Store (Ads)
  'GET /admin/store' => ['App\Controllers\Admin\StoreController', 'index'],
  'POST /admin/store' => ['App\Controllers\Admin\StoreController', 'saveDo'],

  // Admin Settings
  'GET /admin/settings' => ['App\Controllers\Admin\SettingsController', 'index'],
  'POST /admin/settings/profile' => ['App\Controllers\Admin\SettingsController', 'saveProfileDo'],
  'POST /admin/settings/password' => ['App\Controllers\Admin\SettingsController', 'savePasswordDo'],
  'POST /admin/settings/key' => ['App\Controllers\Admin\SettingsController', 'regenKeyDo'],

  // =========================
  // WEBHOOKS (PUBLIC)
  // =========================
  'POST /webhook/stripe' => ['App\Controllers\WebhookController', 'stripe'],
  'POST /webhook/midtrans' => ['App\Controllers\WebhookController', 'midtrans'],
];

$key = $method . ' ' . $path;

if (!isset($routes[$key])) {
  http_response_code(404);
  echo "404 Not Found";
  exit;
}

[$class, $action] = $routes[$key];

// =========================
// PUBLIC PATHS (tanpa login user dan tanpa OTP gate)
// =========================
$publicPaths = [
  '/', // homepage publik
  '/login','/register','/forgot','/reset','/otp',
  '/webhook/stripe','/webhook/midtrans',

  // admin login steps (public)
  '/admin/login',
  '/admin/login/username',
  '/admin/login/password',
  '/admin/login/key',
];

// =========================
// GATE LOGIN USER
// - Jangan pernah apply gate user untuk "/admin/*"
// =========================
$isAdminPath = (strpos($path, '/admin') === 0);

if (!$isAdminPath && !in_array($path, $publicPaths, true)) {
  if (!Auth::check()) {
    header('Location: /login');
    exit;
  }
  // Gate OTP
  if (Auth::check() && Auth::needsOtp() && $path !== '/otp') {
    header('Location: /otp');
    exit;
  }
}

// =========================
// Execute controller
// =========================
try {
  if (!class_exists($class)) {
    throw new RuntimeException("ROUTER ERROR: Class not found: {$class}");
  }

  $controller = new $class();

  if (!method_exists($controller, $action)) {
    throw new RuntimeException("ROUTER ERROR {$key}: Action not found: {$class}::{$action}()");
  }

  $controller->$action();

} catch (Throwable $e) {
  error_log($e->getMessage());
  http_response_code(500);
  echo "500 Internal Server Error";
}
