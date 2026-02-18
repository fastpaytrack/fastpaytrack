<?php
declare(strict_types=1);

namespace App\Lib;

final class AdminUtil
{
  public static function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }

  // Denominasi FULL (tanpa rb/jt/M)
  // output contoh: Rp 10.000
  public static function moneyIdr(int $amount): string {
    $sign = $amount < 0 ? '-' : '';
    $n = abs($amount);
    return $sign . 'Rp ' . number_format($n, 0, ',', '.');
  }

  public static function nowJakarta(): \DateTime {
    return new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
  }

  public static function ua(): string {
    return (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
  }

  public static function ip(): string {
    // basic; cukup aman untuk panel
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return (string)$_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $parts = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
      return trim((string)($parts[0] ?? ''));
    }
    return (string)($_SERVER['REMOTE_ADDR'] ?? '');
  }

  public static function randAccessKey16(): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i=0; $i<16; $i++){
      $out .= $alphabet[random_int(0, strlen($alphabet)-1)];
    }
    return $out;
  }
}
