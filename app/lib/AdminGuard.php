<?php
declare(strict_types=1);

namespace App\Lib;

use App\Lib\DB;

final class AdminGuard
{
  public static function isLocked(string $username): array
  {
    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT fail_count, locked_until FROM admin_login_locks WHERE username=? LIMIT 1");
    $st->execute([$username]);
    $row = $st->fetch();
    if (!$row) return ['locked' => false, 'until' => null, 'fails' => 0];

    $until = $row['locked_until'] ? strtotime((string)$row['locked_until']) : 0;
    if ($until && $until > time()){
      return ['locked' => true, 'until' => (string)$row['locked_until'], 'fails' => (int)$row['fail_count']];
    }
    return ['locked' => false, 'until' => (string)($row['locked_until'] ?? null), 'fails' => (int)$row['fail_count']];
  }

  public static function failKey(string $username): array
  {
    $pdo = DB::pdo();
    $pdo->beginTransaction();
    try {
      $st = $pdo->prepare("SELECT id, fail_count, locked_until FROM admin_login_locks WHERE username=? FOR UPDATE");
      $st->execute([$username]);
      $row = $st->fetch();

      if (!$row){
        $ins = $pdo->prepare("INSERT INTO admin_login_locks (username, fail_count, locked_until, updated_at) VALUES (?,1,NULL,NOW())");
        $ins->execute([$username]);
        $pdo->commit();
        return ['locked' => false, 'fails' => 1];
      }

      $fails = (int)$row['fail_count'] + 1;

      $lockedUntil = null;
      if ($fails >= 4){
        $lockedUntil = (new \DateTime('now'))->modify('+5 minutes')->format('Y-m-d H:i:s');
      }

      $up = $pdo->prepare("UPDATE admin_login_locks SET fail_count=?, locked_until=?, updated_at=NOW() WHERE username=?");
      $up->execute([$fails, $lockedUntil, $username]);

      $pdo->commit();

      return ['locked' => (bool)$lockedUntil, 'fails' => $fails, 'until' => $lockedUntil];
    } catch (\Throwable $e){
      $pdo->rollBack();
      return ['locked' => false, 'fails' => 0];
    }
  }

  public static function resetFails(string $username): void
  {
    $pdo = DB::pdo();
    $st = $pdo->prepare("UPDATE admin_login_locks SET fail_count=0, locked_until=NULL, updated_at=NOW() WHERE username=?");
    $st->execute([$username]);
  }
}
