<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminAuth;
use App\Lib\AdminGuard;
use App\Lib\AdminUtil;
use function App\Lib\redirect;
use function App\Lib\flash;

final class SettingsController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../../views/admin/partials/head.php';
    require __DIR__ . '/../../views/partials/flash.php';
    require __DIR__ . '/../../views/admin/' . $file;
    require __DIR__ . '/../../views/admin/partials/foot.php';
  }

  public function index(): void
  {
    AdminGuard::requireAdmin();
    $pdo = DB::pdo();
    $aid = AdminAuth::id();

    $st = $pdo->prepare("SELECT * FROM admin_users WHERE id=? LIMIT 1");
    $st->execute([$aid]);
    $me = $st->fetch();

    $this->view('settings.php', [
      'csrf' => CSRF::token(),
      'me' => $me,
      'metaTitle' => 'Admin • Settings',
      'page' => 'settings',
    ]);
  }

  public function saveProfileDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();
    $pdo = DB::pdo();
    $aid = AdminAuth::id();

    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $username === '') {
      flash('error', 'Nama/email/username tidak valid.');
      redirect('/admin/settings');
    }

    try {
      $pdo->prepare("UPDATE admin_users SET name=?, email=?, username=?, updated_at=NOW() WHERE id=?")
          ->execute([$name,$email,$username,$aid]);
    } catch (\Throwable $e) {
      flash('error', 'Gagal menyimpan (email/username mungkin sudah dipakai).');
      redirect('/admin/settings');
    }

    flash('success', 'Profile admin berhasil diperbarui.');
    redirect('/admin/settings');
  }

  public function savePasswordDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();
    $pdo = DB::pdo();
    $aid = AdminAuth::id();

    $old = (string)($_POST['old_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');

    if (strlen($new) < 6) {
      flash('error', 'Password baru minimal 6 karakter.');
      redirect('/admin/settings');
    }

    $st = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id=? LIMIT 1");
    $st->execute([$aid]);
    $row = $st->fetch();
    if (!$row || !password_verify($old, (string)$row['password_hash'])) {
      flash('error', 'Password lama salah.');
      redirect('/admin/settings');
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE admin_users SET password_hash=?, updated_at=NOW() WHERE id=?")->execute([$hash,$aid]);

    flash('success', 'Password admin berhasil diubah.');
    redirect('/admin/settings');
  }

  public function regenKeyDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();
    $pdo = DB::pdo();
    $aid = AdminAuth::id();

    $plain = AdminUtil::generateAccessKey16(); // 16 uppercase
    $hash = password_hash($plain, PASSWORD_BCRYPT);

    $pdo->prepare("UPDATE admin_users SET access_key_hash=?, updated_at=NOW() WHERE id=?")
        ->execute([$hash,$aid]);

    flash('success', 'Access key berhasil digenerate. Simpan key baru ini: ' . $plain);
    redirect('/admin/settings');
  }
}
