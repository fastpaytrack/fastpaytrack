<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Env;
use App\Lib\MailerSMTP;

final class WebhookController
{
  // ========= STRIPE WEBHOOK =========
  // Endpoint: POST /webhook/stripe
  public function stripe(): void
  {
    $payload = file_get_contents('php://input') ?: '';
    $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    $secret = Env::get('STRIPE_WEBHOOK_SECRET', '');
    if (!$secret) {
      error_log("STRIPE WEBHOOK: STRIPE_WEBHOOK_SECRET kosong di .env");
      http_response_code(400);
      echo "missing webhook secret";
      return;
    }

    if (!$this->verifyStripeSignature($payload, $sigHeader, $secret)) {
      error_log("STRIPE WEBHOOK: signature invalid");
      http_response_code(400);
      echo "invalid signature";
      return;
    }

    $event = json_decode($payload, true);
    if (!is_array($event) || empty($event['type'])) {
      http_response_code(400);
      echo "invalid payload";
      return;
    }

    // Event utama
    if (($event['type'] ?? '') === 'checkout.session.completed') {
      $session = $event['data']['object'] ?? null;
      if (!is_array($session)) {
        http_response_code(200);
        echo "ok";
        return;
      }

      // Kita set client_reference_id = orderCode (FPxxxx) atau TOPUPxxxx
      $refCode = (string)($session['client_reference_id'] ?? '');
      if (!$refCode && isset($session['metadata']['order_code'])) {
        $refCode = (string)$session['metadata']['order_code'];
      }

      $paymentStatus = strtolower((string)($session['payment_status'] ?? ''));
      $isPaid = ($paymentStatus === 'paid');

      if ($refCode && $isPaid) {

        // ✅ TOPUP SALDO: code dimulai TOPUP
        if (strpos($refCode, 'TOPUP') === 0) {
          $this->markTopupPaidAndAddBalance($refCode, 'stripe', (string)($session['id'] ?? ''));
        } else {
          // ✅ ORDER PRODUK
          $this->markOrderPaidAndEmail($refCode, 'stripe', (string)($session['id'] ?? ''));
        }
      }
    }

    http_response_code(200);
    echo "ok";
  }

  private function verifyStripeSignature(string $payload, string $sigHeader, string $secret): bool
  {
    // Stripe-Signature: t=timestamp,v1=signature,...
    if (!$sigHeader) return false;

    $parts = explode(',', $sigHeader);
    $timestamp = null;
    $signatures = [];

    foreach ($parts as $p) {
      $p = trim($p);
      if (strpos($p, 't=') === 0) $timestamp = substr($p, 2);
      if (strpos($p, 'v1=') === 0) $signatures[] = substr($p, 3);
    }

    if (!$timestamp || !$signatures) return false;

    // toleransi 5 menit
    $t = (int)$timestamp;
    if (abs(time() - $t) > 300) {
      error_log("STRIPE WEBHOOK: timestamp too old/new");
      return false;
    }

    $signedPayload = $timestamp . '.' . $payload;
    $expected = hash_hmac('sha256', $signedPayload, $secret);

    foreach ($signatures as $sig) {
      if (hash_equals($expected, $sig)) return true;
    }
    return false;
  }

  // ========= MIDTRANS WEBHOOK =========
  // Endpoint: POST /webhook/midtrans
  public function midtrans(): void
  {
    $payload = file_get_contents('php://input') ?: '';
    $data = json_decode($payload, true);

    if (!is_array($data)) {
      http_response_code(400);
      echo "invalid payload";
      return;
    }

    $serverKey = Env::get('MIDTRANS_SERVER_KEY', '');
    if (!$serverKey) {
      error_log("MIDTRANS WEBHOOK: MIDTRANS_SERVER_KEY kosong di .env");
      http_response_code(400);
      echo "missing server key";
      return;
    }

    // signature_key = sha512(order_id + status_code + gross_amount + server_key)
    $orderId = (string)($data['order_id'] ?? '');
    $statusCode = (string)($data['status_code'] ?? '');
    $grossAmount = (string)($data['gross_amount'] ?? '');
    $sig = (string)($data['signature_key'] ?? '');

    $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    if (!$orderId || !$sig || !hash_equals($expected, $sig)) {
      error_log("MIDTRANS WEBHOOK: signature invalid for order {$orderId}");
      http_response_code(401);
      echo "invalid signature";
      return;
    }

    $txStatus = strtolower((string)($data['transaction_status'] ?? ''));
    $fraud = strtolower((string)($data['fraud_status'] ?? ''));

    // sukses: settlement/capture (QRIS umumnya settlement)
    $paid = in_array($txStatus, ['settlement', 'capture'], true) && ($fraud === '' || $fraud === 'accept');

    if ($paid) {
      $this->markOrderPaidAndEmail($orderId, 'midtrans', (string)($data['transaction_id'] ?? ''));
    }

    http_response_code(200);
    echo "ok";
  }

  // ========= TOPUP: update wallet_topups + add balance =========
  private function markTopupPaidAndAddBalance(string $topupCode, string $provider, string $reference): void
  {
    $pdo = DB::pdo();

    $st = $pdo->prepare("SELECT * FROM wallet_topups WHERE topup_code=? LIMIT 1");
    $st->execute([$topupCode]);
    $topup = $st->fetch();

    if (!$topup) {
      error_log("TOPUP WEBHOOK: topup not found {$topupCode}");
      return;
    }

    if (($topup['status'] ?? '') === 'PAID') {
      return; // idempotent
    }

    $pdo->beginTransaction();
    try {
      // set topup paid
      $upd = $pdo->prepare("
        UPDATE wallet_topups
        SET status='PAID', payment_provider=?, payment_reference=?
        WHERE id=?
      ");
      $upd->execute([$provider, $reference, (int)$topup['id']]);

      // add balance
      $add = $pdo->prepare("UPDATE users SET balance_idr = balance_idr + ? WHERE id=?");
      $add->execute([(int)$topup['amount_idr'], (int)$topup['user_id']]);

      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      error_log("TOPUP WEBHOOK ERROR: " . $e->getMessage());
    }
  }

  // ========= COMMON: update order + send email =========
  private function markOrderPaidAndEmail(string $orderCode, string $provider, string $reference): void
  {
    $pdo = DB::pdo();

    $st = $pdo->prepare("SELECT * FROM orders WHERE order_code=? LIMIT 1");
    $st->execute([$orderCode]);
    $order = $st->fetch();
    if (!$order) {
      error_log("WEBHOOK: order not found {$orderCode}");
      return;
    }

    // idempotent
    if (($order['status'] ?? '') === 'PAID') {
      return;
    }

    // update status PAID
    $upd = $pdo->prepare("
      UPDATE orders
      SET status='PAID',
          payment_provider=?,
          payment_reference=?
      WHERE id=?
    ");
    $upd->execute([$provider, $reference, (int)$order['id']]);

    // item
    $it = $pdo->prepare("SELECT product_name, amount_idr, qty, line_total FROM order_items WHERE order_id=?");
    $it->execute([(int)$order['id']]);
    $items = $it->fetchAll();

    $this->sendPaidEmail($order, $items);
  }

  private function sendPaidEmail(array $order, array $items): void
  {
    $orderCode = (string)$order['order_code'];
    $buyer = (string)$order['buyer_email'];
    $receiver = (string)$order['receiver_email'];
    $total = (int)$order['total'];

    $rows = '';
    foreach ($items as $it) {
      $name = (string)$it['product_name'];
      $qty  = (int)$it['qty'];
      $amt  = (int)$it['amount_idr'];
      $line = (int)$it['line_total'];
      $rows .= "<tr>
        <td style='padding:8px;border-bottom:1px solid #e5e7eb;'><b>" . htmlspecialchars($name) . "</b><br><span style='color:#6b7280;font-size:12px;'>Nominal: Rp " . number_format($amt,0,',','.') . " • Qty: {$qty}</span></td>
        <td style='padding:8px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:800;'>Rp " . number_format($line,0,',','.') . "</td>
      </tr>";
    }

    $html = "
      <div style='font-family:Arial,sans-serif;color:#111827;'>
        <h2>Pembayaran Berhasil ✅</h2>
        <p>Order <b>{$orderCode}</b> sudah <b>PAID</b>.</p>
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

    // Buyer
    try {
      if ($buyer) MailerSMTP::send($buyer, $subject, $html);
    } catch (\Throwable $e) {
      error_log("MAIL BUYER ERROR: " . $e->getMessage());
    }

    // Receiver (kalau beda)
    try {
      if ($receiver && strtolower($receiver) !== strtolower($buyer)) {
        MailerSMTP::send($receiver, $subject, $html);
      }
    } catch (\Throwable $e) {
      error_log("MAIL RECEIVER ERROR: " . $e->getMessage());
    }
  }
}
