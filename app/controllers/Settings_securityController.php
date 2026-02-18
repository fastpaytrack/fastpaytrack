<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\DB;

final class Settings_securityController
{
  public function index(): void
  {
    Auth::requireAuth();

    $userId = Auth::id();
    $sessionHash = hash('sha256', session_id());

    $devices = DB::fetchAll(
      "SELECT id, ip_address, user_agent, device_label, session_id_hash,
              created_at, last_seen_at, revoked_at
       FROM user_login_devices
       WHERE user_id=?
       ORDER BY (revoked_at IS NOT NULL) ASC, last_seen_at DESC, created_at DESC",
      [$userId]
    );

    foreach ($devices as &$d) {
      $d['is_current'] = ($d['session_id_hash'] === $sessionHash);
    }
    unset($d);

    // pakai helper view global project kamu
    view('security/devices', ['devices' => $devices]);
  }
}
