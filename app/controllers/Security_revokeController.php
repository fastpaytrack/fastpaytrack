<?php

class Security_revokeController
{
  // URL: /security_revoke/{id}
  public function index($id = null)
  {
    Auth::requireLogin();

    $user = Auth::user();
    $sessionHash = hash('sha256', session_id());
    $id = (int)$id;

    if ($id <= 0) {
      flash('error', 'ID device tidak valid.');
      redirect('/security');
    }

    $row = DB::fetch(
      "SELECT id, session_id_hash, revoked_at
       FROM user_login_devices
       WHERE id=? AND user_id=? LIMIT 1",
      [$id, $user['id']]
    );

    if (!$row) {
      flash('error', 'Device tidak ditemukan.');
      redirect('/security');
    }

    // jangan revoke device aktif
    if ($row['session_id_hash'] === $sessionHash) {
      flash('error', 'Tidak bisa logout device yang sedang aktif. Gunakan tombol Logout biasa.');
      redirect('/security');
    }

    // kalau sudah revoked
    if (!empty($row['revoked_at'])) {
      flash('success', 'Device sudah dalam keadaan logout.');
      redirect('/security');
    }

    DB::query(
      "UPDATE user_login_devices
       SET revoked_at=UTC_TIMESTAMP()
       WHERE id=? AND user_id=?",
      [$id, $user['id']]
    );

    flash('success', 'Device berhasil dikeluarkan.');
    redirect('/security');
  }
}
