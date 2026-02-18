<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\DB;
use function App\Lib\flash;
use function App\Lib\redirect;

final class Settings_security_revoke_othersController
{
  // URL: /settings_security_revoke_others
  public function index(): void
  {
    Auth::requireAuth();

    $userId = Auth::id();
    $sessionHash = hash('sha256', session_id());

    DB::query(
      "UPDATE user_login_devices
       SET revoked_at=UTC_TIMESTAMP()
       WHERE user_id=? AND session_id_hash<>? AND revoked_at IS NULL",
      [$userId, $sessionHash]
    );

    flash('success', 'Semua device lain berhasil dikeluarkan.');
    redirect('/settings_security');
  }
}
