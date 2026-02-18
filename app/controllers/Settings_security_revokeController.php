<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\DB;
use function App\Lib\flash;
use function App\Lib\redirect;

final class Settings_security_revokeController
{
  // URL: /settings_security_revoke/{id}
  public function index($id = null): void
  {
    Auth::requireAuth();

    $userId = Auth::id();
    $sessionHash = hash('sha256', session_id());
    $id = (int)$id;

    if ($id <= 0) {
      flash('error', 'ID device tidak valid.');
      redirect('/settings_security');
    }

    $row = DB::fetch(
      "SELECT id, session_id_hash, revoked_at
       FROM user_login_devices
       WHERE id=? AND user_id=? LIMIT 1",
      [$id, $userId]
    );

    if (!$row) {
      flash('error', 'Device tidak ditemukan.');
      redirect('/settings_security');
    }

    if ($row['session_id_hash'] === $sessionHash) {
      flash('error', 'Tidak bisa logout device yang sedang aktif. Gunakan tombol Logout biasa.');
      redirect('/settings_security');
    }

    if (!empty($row['revoked_at'])) {
      flash('success', 'Device sudah dalam keadaan logout.');
      redirect('/settings_security');
    }

    DB::query(
      "UPDATE user_login_devices
       SET revoked_at=UTC_TIMESTAMP()
       WHERE id=? AND user_id=?",
      [$id, $userId]
    );

    flash('success', 'Device berhasil dikeluarkan.');
    redirect('/settings_security');
  }
}
