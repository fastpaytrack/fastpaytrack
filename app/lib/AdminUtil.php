<?php
declare(strict_types=1);

namespace App\Lib;

final class AdminUtil
{
  public static function now(): string {
    return (new \DateTime('now'))->format('Y-m-d H:i:s');
  }

  public static function money_idr(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
  }

  public static function randKey16(): string {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $out = '';
    for ($i=0; $i<16; $i++){
      $out .= $chars[random_int(0, strlen($chars)-1)];
    }
    return $out;
  }

  public static function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }
}
