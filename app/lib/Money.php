<?php
declare(strict_types=1);

namespace App\Lib;

/**
 * Format Rupiah full: Rp 10.000
 */
function money_idr(int $amount): string
{
  $neg = $amount < 0;
  $n = abs($amount);
  $s = number_format($n, 0, ',', '.');
  return ($neg ? '-Rp ' : 'Rp ') . $s;
}

/**
 * Denominasi Rupiah: Rp 10 rb, Rp 1,2 jt, Rp 3,4 M, Rp 1,1 T
 * Cocok untuk card/kartu dashboard supaya pendek.
 */
function idr_denom(int $amount, int $decimals = 1): string
{
  $neg = $amount < 0;
  $n = abs($amount);

  if ($n < 1000) {
    $out = (string)$n;
  } elseif ($n < 1000000) { // < 1 jt
    $out = rtrim(rtrim(number_format($n / 1000, $decimals, ',', '.'), '0'), ',') . ' rb';
  } elseif ($n < 1000000000) { // < 1 M
    $out = rtrim(rtrim(number_format($n / 1000000, $decimals, ',', '.'), '0'), ',') . ' jt';
  } elseif ($n < 1000000000000) { // < 1 T
    $out = rtrim(rtrim(number_format($n / 1000000000, $decimals, ',', '.'), '0'), ',') . ' M';
  } else {
    $out = rtrim(rtrim(number_format($n / 1000000000000, $decimals, ',', '.'), '0'), ',') . ' T';
  }

  return ($neg ? '-Rp ' : 'Rp ') . $out;
}
