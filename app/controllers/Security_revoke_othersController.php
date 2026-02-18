<?php

class Security_revoke_othersController
{
  // URL: /security_revoke_others
  public function index()
  {
    Auth::requireLogin();

    $user = Auth::user();
    $sessionHash = hash('sha256', session_id());

    DB::query(
      "UPDATE user_login_devices
       SET revoked_at=UTC_TIMESTAMP()
       WHERE user_id=? AND session_id_hash<>? AND revoked_at IS NULL",
      [$user['id'], $sessionHash]
    );

    flash('success', 'Semua device lain berhasil dikeluarkan.');
    redirect('/security');
  }
}
