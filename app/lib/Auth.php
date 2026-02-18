<?php
declare(strict_types=1);

namespace App\Lib;

final class Auth {
  public static function login(int $userId, bool $otpRequired = true): void {
    $_SESSION['auth_user_id'] = $userId;
    $_SESSION['auth_otp_ok'] = $otpRequired ? 0 : 1;
  }

  public static function logout(): void {
    unset($_SESSION['auth_user_id'], $_SESSION['auth_otp_ok']);
  }

  public static function id(): ?int {
    $id = $_SESSION['auth_user_id'] ?? null;
    return $id ? (int)$id : null;
  }

  public static function check(): bool {
    return self::id() !== null;
  }

  public static function markOtpOk(): void {
    $_SESSION['auth_otp_ok'] = 1;
  }

  public static function needsOtp(): bool {
    if (!self::check()) return false;
    return (int)($_SESSION['auth_otp_ok'] ?? 0) !== 1;
  }

  public static function requireAuth(): void {
    if (!self::check()) redirect('/login');
    if (self::needsOtp()) redirect('/otp');

    // If this session has been revoked from "Manage Devices", force logout.
    $sessionHash = hash('sha256', session_id());
    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT id FROM user_login_devices WHERE session_id_hash=? AND revoked_at IS NOT NULL LIMIT 1");
    $st->execute([$sessionHash]);
    if ($st->fetch()) {
      self::logout();
      flash('error', 'Sesi login Anda telah berakhir.');
      redirect('/login');
    }

    // Update last seen at most once per 5 minutes (lightweight).
    $last = (int)($_SESSION['last_seen_ping'] ?? 0);
    if (time() - $last > 300) {
      $_SESSION['last_seen_ping'] = time();
      $u = $pdo->prepare("UPDATE user_login_devices SET last_seen_at=UTC_TIMESTAMP() WHERE session_id_hash=? AND revoked_at IS NULL");
      $u->execute([$sessionHash]);
    }
  }
}
