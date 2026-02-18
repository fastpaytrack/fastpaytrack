<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\DB;
use App\Lib\CSRF;
use function App\Lib\flash;
use function App\Lib\redirect;

final class SettingsController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  // GET /settings
  public function index(): void
  {
    Auth::requireAuth();
    $this->view('settings/index.php');
  }

  // GET /settings/security
  public function security(): void
  {
    Auth::requireAuth();

    $userId = Auth::id();
    $sessionHash = hash('sha256', session_id());

    // ambil setting toggle user
    $user = DB::fetch(
      "SELECT notify_login_email FROM users WHERE id=? LIMIT 1",
      [$userId]
    );
    $notifyLoginEmail = (int)($user['notify_login_email'] ?? 1);

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

    $this->view('security/devices.php', [
      'devices' => $devices,
      'csrf' => CSRF::token(),
      'notify_login_email' => $notifyLoginEmail,
    ]);
  }

  // POST /settings/security/notify-email
  public function security_toggle_notify_email(): void
  {
    Auth::requireAuth();
    CSRF::check();

    $userId = Auth::id();
    $value = (int)($_POST['notify_login_email'] ?? 1);
    $value = ($value === 1) ? 1 : 0;

    DB::query(
      "UPDATE users SET notify_login_email=? WHERE id=? LIMIT 1",
      [$value, $userId]
    );

    flash('success', $value ? 'Notifikasi login email diaktifkan.' : 'Notifikasi login email dimatikan.');
    redirect('/settings/security');
  }

  // GET /settings/security/revoke/{id}
  public function security_revoke($id = null): void
  {
    Auth::requireAuth();

    $userId = Auth::id();
    $sessionHash = hash('sha256', session_id());
    $id = (int)$id;

    if ($id <= 0) {
      flash('error', 'ID device tidak valid.');
      redirect('/settings/security');
    }

    $row = DB::fetch(
      "SELECT id, session_id_hash, revoked_at
       FROM user_login_devices
       WHERE id=? AND user_id=? LIMIT 1",
      [$id, $userId]
    );

    if (!$row) {
      flash('error', 'Device tidak ditemukan.');
      redirect('/settings/security');
    }

    // Jangan logout device yang sedang aktif
    if ($row['session_id_hash'] === $sessionHash) {
      flash('error', 'Tidak bisa logout device yang sedang aktif. Gunakan tombol Logout biasa.');
      redirect('/settings/security');
    }

    if (!empty($row['revoked_at'])) {
      flash('success', 'Device sudah dalam keadaan logout.');
      redirect('/settings/security');
    }

    DB::query(
      "UPDATE user_login_devices
       SET revoked_at=UTC_TIMESTAMP()
       WHERE id=? AND user_id=?",
      [$id, $userId]
    );

    flash('success', 'Device berhasil dikeluarkan.');
    redirect('/settings/security');
  }

  // GET /settings/security/revoke-others
  public function security_revoke_others(): void
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
    redirect('/settings/security');
  }
}
