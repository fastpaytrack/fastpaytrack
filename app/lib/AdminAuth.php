<?php
declare(strict_types=1);

namespace App\Lib;

final class AdminAuth
{
  private const SESS_KEY = 'admin_auth';
  private const SESS_STEP = 'admin_login_step';
  private const SESS_TMP = 'admin_login_tmp';

  public static function start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  }

  public static function isLoggedIn(): bool {
    self::start();
    return !empty($_SESSION[self::SESS_KEY]['id']);
  }

  public static function id(): int {
    self::start();
    return (int)($_SESSION[self::SESS_KEY]['id'] ?? 0);
  }

  public static function name(): string {
    self::start();
    return (string)($_SESSION[self::SESS_KEY]['name'] ?? '');
  }

  public static function login(int $adminId, string $name, string $username): void {
    self::start();
    $_SESSION[self::SESS_KEY] = [
      'id' => $adminId,
      'name' => $name,
      'username' => $username,
      'at' => time(),
    ];
    unset($_SESSION[self::SESS_STEP], $_SESSION[self::SESS_TMP]);
  }

  public static function logout(): void {
    self::start();
    unset($_SESSION[self::SESS_KEY], $_SESSION[self::SESS_STEP], $_SESSION[self::SESS_TMP]);
  }

  // ===== Login Flow Steps: username -> password -> key =====

  public static function setStep(string $step): void {
    self::start();
    $_SESSION[self::SESS_STEP] = $step;
  }

  public static function step(): string {
    self::start();
    return (string)($_SESSION[self::SESS_STEP] ?? 'username');
  }

  public static function setTmp(array $data): void {
    self::start();
    $_SESSION[self::SESS_TMP] = $data;
  }

  public static function tmp(): array {
    self::start();
    return (array)($_SESSION[self::SESS_TMP] ?? []);
  }
}
