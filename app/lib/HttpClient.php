<?php
declare(strict_types=1);

namespace App\Lib;

final class HttpClient
{
  public static function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeout = 25): array
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    if ($body !== null) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $h = [];
    foreach ($headers as $k => $v) {
      $h[] = $k . ': ' . $v;
    }
    if ($h) curl_setopt($ch, CURLOPT_HTTPHEADER, $h);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
      throw new \RuntimeException("HTTP request failed: " . $err);
    }

    return ['status' => $code, 'body' => $resp];
  }
}
