<?php
declare(strict_types=1);

namespace App\Lib;

final class TrustedDevice
{
  private const COOKIE_NAME = 'trusted_device';
  private const DAYS_VALID = 30; // 30 hari

  public static function cookieName(): string { return self::COOKIE_NAME; }

  public static function cookieValueFromRequest(): string
  {
    return (string)($_COOKIE[self::COOKIE_NAME] ?? '');
  }

  /** Cek cookie trusted_device valid untuk userId => true jika boleh skip OTP */
  public static function isTrustedForUser(int $userId): bool
  {
    $raw = self::cookieValueFromRequest();
    if (!$raw || strpos($raw, ':') === false) return false;

    [$selector, $token] = explode(':', $raw, 2);
    $selector = trim($selector);
    $token = trim($token);

    if ($selector === '' || $token === '') return false;
    if (strlen($selector) !== 12) return false;

    $pdo = DB::pdo();

    $st = $pdo->prepare("
      SELECT id, token_hash, user_agent, revoked_at, expires_at
      FROM user_trusted_devices
      WHERE user_id=? AND selector=? LIMIT 1
    ");
    $st->execute([$userId, $selector]);
    $row = $st->fetch();

    if (!$row) return false;
    if (!empty($row['revoked_at'])) return false;

    $nowUtc = gmdate('Y-m-d H:i:s');
    if (!empty($row['expires_at']) && (string)$row['expires_at'] < $nowUtc) return false;

    $tokenHash = hash('sha256', $token);
    if (!hash_equals((string)$row['token_hash'], $tokenHash)) return false;

    // Optional: cocokkan user agent agar tidak gampang disalin ke device lain
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $dbUa = (string)($row['user_agent'] ?? '');
    if ($dbUa && $ua && $dbUa !== $ua) return false;

    // update last_used_at (best effort)
    try {
      $upd = $pdo->prepare("UPDATE user_trusted_devices SET last_used_at=UTC_TIMESTAMP() WHERE id=?");
      $upd->execute([(int)$row['id']]);
    } catch (\Throwable $e) {}

    return true;
  }

  /** Dipanggil saat OTP sukses + user centang remember device */
  public static function remember(int $userId): void
  {
    $pdo = DB::pdo();

    $selector = self::randomBase62(12);
    $token = bin2hex(random_bytes(32)); // token raw (disimpan di cookie)
    $tokenHash = hash('sha256', $token);

    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ip = self::clientIp();

    $expires = gmdate('Y-m-d H:i:s', time() + (self::DAYS_VALID * 86400));

    // simpan ke DB
    $ins = $pdo->prepare("
      INSERT INTO user_trusted_devices (user_id, selector, token_hash, user_agent, ip, created_at, last_used_at, expires_at)
      VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP(), ?)
    ");
    $ins->execute([$userId, $selector, $tokenHash, $ua, $ip, $expires]);

    // set cookie
    self::setCookie($selector . ':' . $token, time() + (self::DAYS_VALID * 86400));
  }

  private static function setCookie(string $value, int $expiresTs): void
  {
    if (headers_sent()) return;

    @setcookie(self::COOKIE_NAME, $value, [
      'expires'  => $expiresTs,
      'path'     => '/',
      'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
  }

  private static function randomBase62(int $len): string
  {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $out = '';
    for ($i=0; $i<$len; $i++) {
      $out .= $chars[random_int(0, strlen($chars)-1)];
    }
    return $out;
  }

  private static function clientIp(): string
  {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
      $v = $_SERVER[$k] ?? '';
      if (!$v) continue;
      foreach (explode(',', (string)$v) as $ip) {
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
      }
    }
    return '0.0.0.0';
  }
}
