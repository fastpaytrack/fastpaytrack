<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\PaymentStripe;
use function App\Lib\redirect;
use function App\Lib\flash;
use function App\Lib\app_url;

final class TopupController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  public function show(): void
  {
    Auth::requireAuth();
    $this->view('profile/topup.php', ['csrf' => CSRF::token()]);
  }

  public function create(): void
  {
    CSRF::check();
    Auth::requireAuth();

    // user input bisa format "10.000" / "10000"
    $raw = (string)($_POST['amount_idr'] ?? '');
    $clean = preg_replace('/[^0-9]/', '', $raw);
    $amount = (int)$clean;

    $min = 10000;        // minimal topup
    $max = 10000000;     // maksimal topup (10 juta)

    if ($amount < $min) {
      flash('error', 'Minimal topup Rp 10.000');
      redirect('/topup');
    }
    if ($amount > $max) {
      flash('error', 'Maksimal topup Rp 10.000.000');
      redirect('/topup');
    }

    $pdo = DB::pdo();
    $uid = Auth::id();

    $code = 'TOPUP' . date('YmdHis') . random_int(1000,9999);

    $ins = $pdo->prepare("INSERT INTO wallet_topups (user_id, topup_code, amount_idr, status) VALUES (?,?,?, 'PENDING')");
    $ins->execute([$uid, $code, $amount]);

    $success = app_url('/dashboard');
    $cancel  = app_url('/topup');

    // Stripe session: client_reference_id = TOPUP code
    $session = PaymentStripe::createCheckoutSession($code, $amount, $success, $cancel);

    $upd = $pdo->prepare("UPDATE wallet_topups SET payment_provider='stripe', payment_reference=? WHERE topup_code=?");
    $upd->execute([$session['id'], $code]);

    redirect($session['url']);
  }

  // ✅ RIWAYAT TOPUP
  // GET /topup/history
  public function history(): void
  {
    Auth::requireAuth();
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("
      SELECT topup_code, amount_idr, status, payment_provider, created_at, updated_at
      FROM wallet_topups
      WHERE user_id=?
      ORDER BY id DESC
      LIMIT 100
    ");
    $st->execute([$uid]);
    $topups = $st->fetchAll();

    $this->view('profile/topup_history.php', [
      'topups' => $topups
    ]);
  }
}
