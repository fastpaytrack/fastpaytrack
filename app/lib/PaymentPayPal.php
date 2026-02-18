<?php
declare(strict_types=1);

namespace App\Lib;

final class PaymentPayPal
{
  private static function base(): string {
    return Env::get('PAYPAL_BASE', 'https://api-m.sandbox.paypal.com');
  }

  private static function clientId(): string {
    return Env::get('PAYPAL_CLIENT_ID', '');
  }

  private static function secret(): string {
    return Env::get('PAYPAL_CLIENT_SECRET', '');
  }

  public static function getAccessToken(): string
  {
    $cid = self::clientId();
    $sec = self::secret();
    if (!$cid || !$sec) throw new \RuntimeException("PAYPAL_CLIENT_ID/SECRET kosong di .env");

    $auth = base64_encode($cid . ':' . $sec);
    $res = HttpClient::request('POST', self::base() . '/v1/oauth2/token', [
      'Authorization' => 'Basic ' . $auth,
      'Content-Type' => 'application/x-www-form-urlencoded'
    ], 'grant_type=client_credentials');

    $data = json_decode($res['body'], true);
    if (!is_array($data) || empty($data['access_token'])) {
      throw new \RuntimeException("PayPal token error: {$res['body']}");
    }
    return (string)$data['access_token'];
  }

  public static function createOrder(string $orderCode, float $amount, string $currency, string $returnUrl, string $cancelUrl): array
  {
    $token = self::getAccessToken();

    $payload = [
      'intent' => 'CAPTURE',
      'purchase_units' => [[
        'reference_id' => $orderCode,
        'amount' => [
          'currency_code' => $currency,
          'value' => number_format($amount, 2, '.', '')
        ]
      ]],
      'application_context' => [
        'return_url' => $returnUrl,
        'cancel_url' => $cancelUrl
      ]
    ];

    $res = HttpClient::request('POST', self::base() . '/v2/checkout/orders', [
      'Authorization' => 'Bearer ' . $token,
      'Content-Type' => 'application/json'
    ], json_encode($payload));

    $data = json_decode($res['body'], true);
    if (!is_array($data) || empty($data['id'])) {
      throw new \RuntimeException("PayPal create order error: {$res['body']}");
    }

    $approve = '';
    foreach (($data['links'] ?? []) as $l) {
      if (($l['rel'] ?? '') === 'approve') $approve = (string)($l['href'] ?? '');
    }

    return ['id' => (string)$data['id'], 'approve_url' => $approve, 'raw' => $data];
  }

  public static function capture(string $paypalOrderId): array
  {
    $token = self::getAccessToken();
    $res = HttpClient::request('POST', self::base() . "/v2/checkout/orders/{$paypalOrderId}/capture", [
      'Authorization' => 'Bearer ' . $token,
      'Content-Type' => 'application/json'
    ], json_encode(new \stdClass()));

    $data = json_decode($res['body'], true);
    if (!is_array($data)) throw new \RuntimeException("PayPal capture error: {$res['body']}");
    return $data;
  }
}
