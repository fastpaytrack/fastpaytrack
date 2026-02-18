<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\PaymentStripe;
use App\Lib\PaymentPayPal;
use App\Lib\PaymentMidtrans;
use App\Lib\MailerSMTP;
use function App\Lib\redirect;
use function App\Lib\flash;
use function App\Lib\app_url;

final class PaymentController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  private function loadOrder(string $code): array
  {
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("SELECT * FROM orders WHERE order_code=? AND user_id=? LIMIT 1");
    $st->execute([$code, $uid]);
    $o = $st->fetch();
    if (!$o) {
      flash('error', 'Order tidak ditemukan.');
      redirect('/orders');
    }
    return $o;
  }

  private function getUserBalance(int $userId): int
  {
    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT balance_idr FROM users WHERE id=? LIMIT 1");
    $st->execute([$userId]);
    $u = $st->fetch();
    return (int)($u['balance_idr'] ?? 0);
  }

  // /pay?order=FPxxxx  (halaman pilih metode)
  public function payPage(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['order'] ?? ''));
    if (!$code) redirect('/orders');

    $o = $this->loadOrder($code);

    $uid = (int)Auth::id();
    $balance = $this->getUserBalance($uid);

    $this->view('cart/pay.php', [
      'order' => $o,
      'balance' => $balance,
      'csrf' => CSRF::token()
    ]);
  }

  // ====== BAYAR DENGAN SALDO (WALLET) ======
  // POST /pay/balance
  public function balancePay(): void
  {
    CSRF::check();
    Auth::requireAuth();

    $code = trim((string)($_POST['order'] ?? ''));
    if (!$code) redirect('/orders');

    $pdo = DB::pdo();
    $uid = (int)Auth::id();

    // ambil order milik user
    $st = $pdo->prepare("SELECT * FROM orders WHERE order_code=? AND user_id=? LIMIT 1");
    $st->execute([$code, $uid]);
    $order = $st->fetch();

    if (!$order) {
      flash('error', 'Order tidak ditemukan.');
      redirect('/orders');
    }

    if (($order['status'] ?? '') !== 'PENDING') {
      flash('error', 'Order ini sudah tidak PENDING.');
      redirect('/order?code=' . urlencode($code));
    }

    $total = (int)$order['total'];

    // transaksi: cek saldo -> potong -> set order PAID
    $pdo->beginTransaction();
    try {
      // lock row user agar tidak race condition
      $u = $pdo->prepare("SELECT balance_idr FROM users WHERE id=? FOR UPDATE");
      $u->execute([$uid]);
      $bal = (int)($u->fetch()['balance_idr'] ?? 0);

      if ($bal < $total) {
        $pdo->rollBack();
        flash('error', 'Saldo tidak cukup untuk membayar order ini.');
        redirect('/pay?order=' . urlencode($code));
      }

      // potong saldo
      $updBal = $pdo->prepare("UPDATE users SET balance_idr = balance_idr - ? WHERE id=?");
      $updBal->execute([$total, $uid]);

      // set order PAID
      $updOrder = $pdo->prepare("
        UPDATE orders
        SET status='PAID',
            payment_provider='balance',
            payment_reference='wallet'
        WHERE id=?
      ");
      $updOrder->execute([(int)$order['id']]);

      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      error_log("BALANCE PAY ERROR: " . $e->getMessage());
      flash('error', 'Gagal bayar pakai saldo. Cek error_log.');
      redirect('/pay?order=' . urlencode($code));
    }

    // kirim email konfirmasi (invoice ringkas)
    $this->sendPaidEmailByOrderId((int)$order['id']);

    flash('success', 'Pembayaran menggunakan saldo berhasil. Order menjadi PAID.');
    redirect('/order?code=' . urlencode($code));
  }

  private function sendPaidEmailByOrderId(int $orderId): void
  {
    try {
      $pdo = DB::pdo();

      $st = $pdo->prepare("SELECT * FROM orders WHERE id=? LIMIT 1");
      $st->execute([$orderId]);
      $order = $st->fetch();
      if (!$order) return;

      $it = $pdo->prepare("SELECT product_name, amount_idr, qty, line_total FROM order_items WHERE order_id=?");
      $it->execute([$orderId]);
      $items = $it->fetchAll();

      $orderCode = (string)$order['order_code'];
      $buyer = (string)$order['buyer_email'];
      $receiver = (string)$order['receiver_email'];
      $total = (int)$order['total'];

      $rows = '';
      foreach ($items as $itx) {
        $name = (string)$itx['product_name'];
        $qty  = (int)$itx['qty'];
        $amt  = (int)$itx['amount_idr'];
        $line = (int)$itx['line_total'];
        $rows .= "<tr>
          <td style='padding:8px;border-bottom:1px solid #e5e7eb;'><b>" . htmlspecialchars($name) . "</b><br>
          <span style='color:#6b7280;font-size:12px;'>Nominal: Rp " . number_format($amt,0,',','.') . " • Qty: {$qty}</span></td>
          <td style='padding:8px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:800;'>Rp " . number_format($line,0,',','.') . "</td>
        </tr>";
      }

      $html = "
        <div style='font-family:Arial,sans-serif;color:#111827;'>
          <h2>Pembayaran Berhasil ✅</h2>
          <p>Order <b>{$orderCode}</b> sudah <b>PAID</b> (Wallet).</p>
          <div style='margin:12px 0;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;'>
            <table style='width:100%;border-collapse:collapse;'>
              <thead>
                <tr>
                  <th style='text-align:left;padding:8px;border-bottom:1px solid #e5e7eb;color:#6b7280;'>Item</th>
                  <th style='text-align:right;padding:8px;border-bottom:1px solid #e5e7eb;color:#6b7280;'>Total</th>
                </tr>
              </thead>
              <tbody>{$rows}</tbody>
            </table>
            <div style='margin-top:12px;text-align:right;font-size:16px;font-weight:900;'>
              Total Bayar: Rp " . number_format($total,0,',','.') . "
            </div>
          </div>
          <p style='color:#6b7280;font-size:12px;'>Terima kasih sudah menggunakan FastPayTrack.</p>
        </div>
      ";

      $subject = "FastPayTrack - Pembayaran Berhasil ({$orderCode})";

      if ($buyer) {
        try { MailerSMTP::send($buyer, $subject, $html); } catch (\Throwable $e) { error_log("MAIL BUYER ERROR: ".$e->getMessage()); }
      }
      if ($receiver && strtolower($receiver) !== strtolower($buyer)) {
        try { MailerSMTP::send($receiver, $subject, $html); } catch (\Throwable $e) { error_log("MAIL RECEIVER ERROR: ".$e->getMessage()); }
      }
    } catch (\Throwable $e) {
      error_log("SEND PAID EMAIL ERROR: " . $e->getMessage());
    }
  }

  // ===== Stripe / PayPal / QRIS existing =====

  public function stripeStart(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['order'] ?? ''));
    if (!$code) redirect('/orders');

    $o = $this->loadOrder($code);
    if ($o['status'] !== 'PENDING') redirect('/orders');

    $success = app_url('/orders');
    $cancel  = app_url('/pay?order=' . urlencode($code));

    try {
      $session = PaymentStripe::createCheckoutSession($code, (int)$o['total'], $success, $cancel);

      $pdo = DB::pdo();
      $upd = $pdo->prepare("UPDATE orders SET payment_provider='stripe', payment_reference=? WHERE id=?");
      $upd->execute([$session['id'], (int)$o['id']]);

      redirect($session['url']);
    } catch (\Throwable $e) {
      error_log("STRIPE ERROR: " . $e->getMessage());
      flash('error', 'Stripe error: ' . $e->getMessage());
      redirect('/pay?order=' . urlencode($code));
    }
  }

  public function paypalStart(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['order'] ?? ''));
    if (!$code) redirect('/orders');

    $o = $this->loadOrder($code);
    if ($o['status'] !== 'PENDING') redirect('/orders');

    $usd = max(1.00, round(((int)$o['total']) / 16000, 2));
    $returnUrl = app_url('/pay/paypal/return?order=' . urlencode($code));
    $cancelUrl = app_url('/pay/paypal/cancel?order=' . urlencode($code));

    try {
      $pp = PaymentPayPal::createOrder($code, $usd, 'USD', $returnUrl, $cancelUrl);

      $pdo = DB::pdo();
      $upd = $pdo->prepare("UPDATE orders SET payment_provider='paypal', payment_reference=? WHERE id=?");
      $upd->execute([$pp['id'], (int)$o['id']]);

      redirect($pp['approve_url']);
    } catch (\Throwable $e) {
      error_log("PAYPAL ERROR: " . $e->getMessage());
      flash('error', 'PayPal error: ' . $e->getMessage());
      redirect('/pay?order=' . urlencode($code));
    }
  }

  public function paypalReturn(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['order'] ?? ''));
    if (!$code) redirect('/orders');

    $o = $this->loadOrder($code);
    if (($o['payment_provider'] ?? '') !== 'paypal') redirect('/orders');

    $paypalOrderId = (string)($o['payment_reference'] ?? '');
    if (!$paypalOrderId) {
      flash('error', 'PayPal reference kosong.');
      redirect('/orders');
    }

    try {
      PaymentPayPal::capture($paypalOrderId);
      $pdo = DB::pdo();
      $upd = $pdo->prepare("UPDATE orders SET status='PAID' WHERE id=?");
      $upd->execute([(int)$o['id']]);
      flash('success', 'Payment PayPal berhasil (sandbox).');
    } catch (\Throwable $e) {
      error_log("PAYPAL CAPTURE ERROR: " . $e->getMessage());
      flash('error', 'Payment PayPal gagal: ' . $e->getMessage());
    }

    redirect('/orders');
  }

  public function paypalCancel(): void
  {
    flash('error', 'Payment PayPal dibatalkan.');
    redirect('/orders');
  }

  public function qrisStart(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['order'] ?? ''));
    if (!$code) redirect('/orders');

    $o = $this->loadOrder($code);
    if ($o['status'] !== 'PENDING') redirect('/orders');

    try {
      $q = PaymentMidtrans::createQris($code, (int)$o['total'], (string)$o['buyer_email']);

      $txId = (string)($q['transaction_id'] ?? '');
      $qrUrl = (string)($q['qr_url'] ?? '');

      // simpan tx + qr url
      $ref = $txId;
      if ($qrUrl) $ref .= '|' . $qrUrl;

      $pdo = DB::pdo();
      $upd = $pdo->prepare("UPDATE orders SET payment_provider='midtrans', payment_reference=? WHERE id=?");
      $upd->execute([$ref, (int)$o['id']]);

      $this->view('cart/qris.php', [
        'order' => $o,
        'qr_url' => $qrUrl
      ]);
    } catch (\Throwable $e) {
      error_log("MIDTRANS QRIS ERROR: " . $e->getMessage());
      flash('error', 'QRIS error: ' . $e->getMessage());
      redirect('/pay?order=' . urlencode($code));
    }
  }
}
