<?php
declare(strict_types=1);

namespace App\Lib;

final class PaymentMidtrans
{
  public static function createQris(string $orderCode, int $grossAmount, string $buyerEmail): array
  {
    $serverKey = Env::get('MIDTRANS_SERVER_KEY', '');
    $isProd = Env::bool('MIDTRANS_IS_PRODUCTION', false);

    if (!$serverKey) throw new \RuntimeException("MIDTRANS_SERVER_KEY kosong di .env");

    $base = $isProd ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
    $url  = $base . '/v2/charge';

    $payload = [
      'payment_type' => 'qris',
      'transaction_details' => [
        'order_id' => $orderCode,
        'gross_amount' => $grossAmount
      ],
      'customer_details' => [
        'email' => $buyerEmail
      ],
      'qris' => [
        'acquirer' => 'gopay' // acquirer umum untuk QRIS di sandbox
      ]
    ];

    $auth = base64_encode($serverKey . ':');
    $res = HttpClient::request('POST', $url, [
      'Authorization' => 'Basic ' . $auth,
      'Content-Type' => 'application/json'
    ], json_encode($payload));

    if ($res['status'] < 200 || $res['status'] >= 300) {
      throw new \RuntimeException("Midtrans error HTTP {$res['status']}: {$res['body']}");
    }

    $data = json_decode($res['body'], true);
    if (!is_array($data)) throw new \RuntimeException("Midtrans response invalid");

    // cari QR URL
    $qrUrl = '';
    if (!empty($data['actions']) && is_array($data['actions'])) {
      foreach ($data['actions'] as $a) {
        if (($a['name'] ?? '') === 'generate-qr-code') $qrUrl = (string)($a['url'] ?? '');
      }
    }

    return [
      'raw' => $data,
      'transaction_id' => (string)($data['transaction_id'] ?? ''),
      'qr_url' => $qrUrl
    ];
  }
}
