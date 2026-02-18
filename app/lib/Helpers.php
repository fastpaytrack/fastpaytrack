<?php
declare(strict_types=1);

namespace App\Lib;

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function redirect(string $to): void {
  header('Location: ' . $to);
  exit;
}

function flash(string $key, ?string $val = null): ?string {
  if ($val !== null) {
    $_SESSION['_flash'][$key] = $val;
    return null;
  }
  $v = $_SESSION['_flash'][$key] ?? null;
  unset($_SESSION['_flash'][$key]);
  return $v;
}

function app_url(string $path = ''): string {
  $base = Env::get('APP_URL', '');
  return rtrim($base, '/') . $path;
}

function now_utc(): \DateTimeImmutable {
  return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
}

function random_digits(int $len = 6): string {
  $s = '';
  for ($i=0;$i<$len;$i++) $s .= (string)random_int(0,9);
  return $s;
}

function money_idr(int $n): string {
  return 'Rp ' . number_format($n, 0, ',', '.');
}
