<?php
declare(strict_types=1);

namespace App;

use App\Lib\Env;

// Load env
require_once __DIR__ . '/../lib/Env.php';
Env::load(__DIR__ . '/../../.env');

// Timezone
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Jakarta'));

// Session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Core libs
require_once __DIR__ . '/../lib/Helpers.php';
require_once __DIR__ . '/../lib/DB.php';
require_once __DIR__ . '/../lib/CSRF.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/MailerSMTP.php';

// HTTP + Payment libs
require_once __DIR__ . '/../lib/HttpClient.php';
require_once __DIR__ . '/../lib/PaymentStripe.php';
require_once __DIR__ . '/../lib/PaymentPayPal.php';
require_once __DIR__ . '/../lib/PaymentMidtrans.php';

// Controllers
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/HomeController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CheckoutController.php';
require_once __DIR__ . '/../controllers/PaymentController.php';
require_once __DIR__ . '/../controllers/WebhookController.php';
require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/InvoiceController.php';
require_once __DIR__ . '/../controllers/TopupController.php';
require_once __DIR__ . '/../controllers/WalletController.php';
require_once __DIR__ . '/../controllers/PinController.php';
require_once __DIR__ . '/../controllers/SettingsController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../lib/TrustedDevice.php';
