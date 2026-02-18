<?php
declare(strict_types=1);

namespace App\Lib;

final class PaymentStripe
{
  /**
   * IMPORTANT:
   * Dari error_log kamu: Stripe menganggap 52000 -> Rp520.00
   * Artinya Stripe menginterpretasikan IDR dengan 2 desimal pada Checkout Session API.
   * Maka untuk men-charge Rp52.000, unit_amount harus 5.200.000 (x100).
   */
  public static function createCheckoutSession(string $orderCode, int $amountIdr, string $successUrl, string $cancelUrl): array
  {
    $secret = Env::get('STRIPE_SECRET_KEY', '');
    if (!$secret) {
      throw new \RuntimeException("STRIPE_SECRET_KEY kosong di .env");
    }

    // ✅ FIX: kalikan 100 agar nilai IDR tidak jadi Rp520.00
    $stripeUnitAmount = $amountIdr * 100;

    // Safety: jangan sampai nol
    if ($stripeUnitAmount < 1) {
      throw new \RuntimeException("Total order invalid untuk Stripe.");
    }

    $payload = http_build_query([
      'mode' => 'payment',
      'success_url' => $successUrl,
      'cancel_url' => $cancelUrl,
      'line_items[0][price_data][currency]' => 'idr',
      'line_items[0][price_data][product_data][name]' => 'FastPayTrack Order ' . $orderCode,
      'line_items[0][price_data][unit_amount]' => (string)$stripeUnitAmount,
      'line_items[0][quantity]' => '1',
      'client_reference_id' => $orderCode,
    ]);

    $res = HttpClient::request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
      'Authorization' => 'Bearer ' . $secret,
      'Content-Type'  => 'application/x-www-form-urlencoded'
    ], $payload);

    $data = json_decode($res['body'], true);

    if ($res['status'] < 200 || $res['status'] >= 300) {
      $msg = '';
      if (is_array($data) && isset($data['error']['message'])) $msg = (string)$data['error']['message'];
      throw new \RuntimeException("Stripe HTTP {$res['status']}: " . ($msg ?: $res['body']));
    }

    if (!is_array($data) || empty($data['id']) || empty($data['url'])) {
      throw new \RuntimeException("Stripe response invalid: {$res['body']}");
    }

    return [
      'id'  => (string)$data['id'],
      'url' => (string)$data['url'],
      'raw' => $data
    ];
  }
}
