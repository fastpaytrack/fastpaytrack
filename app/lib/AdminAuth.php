<?php
declare(strict_types=1);

namespace App\Lib;

use App\Lib\DB;

final class AdminAuth
{
  private const SESSION_KEY = 'admin_auth';

  public static function bootSeedAdminIfEmpty(): void
  {
    $pdo = DB::pdo();
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    if ($cnt > 0) return;

    $username = 'admin';
    $name = 'Super Admin';
    $email = 'admin@fastpaytrack.com';

    $passwordPlain = 'ChangeMe123!';
    $keyPlain = 'ABCDEFGHIJKLMNOP';

    $passwordHash = password_hash($passwordPlain, PASSWORD_BCRYPT);
    $keyHash = password_hash($keyPlain, PASSWORD_BCRYPT);

    $st = $pdo->prepare("INSERT INTO admin_users (name,email,username,password_hash,access_key_hash,is_super) VALUES (?,?,?,?,?,1)");
    $st->execute([$name,$email,$username,$passwordHash,$keyHash]);
  }

  public static function loginStageSet(string $stage, array $data = []): void
  {
    $_SESSION['admin_login_stage'] = $stage;
    $_SESSION['admin_login_data'] = $data;
  }

  public static function loginStageGet(): array
  {
    return [
      'stage' => (string)($_SESSION['admin_login_stage'] ?? 'username'),
      'data' => (array)($_SESSION['admin_login_data'] ?? []),
    ];
  }

  public static function loginStageClear(): void
  {
    unset($_SESSION['admin_login_stage'], $_SESSION['admin_login_data']);
  }

  public static function check(): bool
  {
    return !empty($_SESSION[self::SESSION_KEY]['id']);
  }

  public static function id(): int
  {
    return (int)($_SESSION[self::SESSION_KEY]['id'] ?? 0);
  }

  public static function user(): array
  {
    return (array)($_SESSION[self::SESSION_KEY] ?? []);
  }

  public static function require(): void
  {
    if (!self::check()) {
      header('Location: /admin/login');
      exit;
    }
  }

  public static function setSession(int $adminId, string $name, string $email, string $username): void
  {
    $_SESSION[self::SESSION_KEY] = [
      'id' => $adminId,
      'name' => $name,
      'email' => $email,
      'username' => $username,
      'ts' => time(),
    ];
  }

  public static function logout(): void
  {
    unset($_SESSION[self::SESSION_KEY]);
  }
}
