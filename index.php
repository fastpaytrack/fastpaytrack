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
// SIMPLE AUTOLOADER (FIX Class not found)
// Struktur kamu: /app/controllers, /app/lib, /app/config
// Namespace: App\Controllers\X => app/controllers/X.php
// =========================
spl_autoload_register(function(string $class){
  $prefix = 'App\\';
  if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;

  $relative = substr($class, strlen($prefix)); // ex: Controllers\ProductsController
  $relativePath = str_replace('\\', '/', $relative) . '.php';

  // Map folder namespace -> folder nyata (lowercase)
  // Controllers => controllers, Lib => lib, Config => config
  $relativePath = preg_replace('#^Controllers/#', 'controllers/', $relativePath);
  $relativePath = preg_replace('#^Lib/#', 'lib/', $relativePath);
  $relativePath = preg_replace('#^Config/#', 'config/', $relativePath);

  $file = __DIR__ . '/app/' . $relativePath;

  if (is_file($file)) {
    require_once $file;
  }
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
// MOBILE ONLY GUARD (kecuali homepage "/")
// NOTE: Home "fastpaytrack.com/" tidak diblokir.
// Webhook juga tidak diblokir.
// =========================
MobileGuard::enforce($path, $method, [
  '/',                 // ✅ homepage publik
  // kalau kamu punya endpoint publik lain, bisa ditambah di sini
]);

$routes = [
  // Home
  'GET /' => ['App\Controllers\HomeController', 'index'],

  // Auth
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

  // Dashboard
  'GET /dashboard' => ['App\Controllers\DashboardController', 'index'],

  // ✅ FULL LIST PRODUCTS (FIX)
  'GET /products' => ['App\Controllers\ProductsController', 'index'],
  'GET /product'  => ['App\Controllers\ProductsController', 'detail'],
  'GET /product' => ['App\Controllers\ProductsController', 'show'],

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

  // Webhooks (PUBLIC, no auth)
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
// PUBLIC PATHS (tanpa login dan tanpa OTP gate)
// =========================
$publicPaths = [
  '/login','/register','/forgot','/reset','/otp',
  '/webhook/stripe','/webhook/midtrans',
];

// Gate login
if (!in_array($path, $publicPaths, true) && $path !== '/') {
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
// Execute controller (dengan guard biar jelas errornya)
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
  // log ke error_log biar kebaca di cPanel
  error_log($e->getMessage());
  http_response_code(500);
  echo "500 Internal Server Error";
}
