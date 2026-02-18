<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use function App\Lib\redirect;
use function App\Lib\flash;

final class ProfileController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  public function index(): void
  {
    Auth::requireAuth();
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("SELECT name, email, created_at FROM users WHERE id=? LIMIT 1");
    $st->execute([$uid]);
    $user = $st->fetch();

    $this->view('profile/index.php', [
      'user' => $user,
      'csrf' => CSRF::token()
    ]);
  }

  public function update(): void
  {
    CSRF::check();
    Auth::requireAuth();

    $name = trim((string)($_POST['name'] ?? ''));
    if (strlen($name) < 2) {
      flash('error', 'Nama minimal 2 karakter.');
      redirect('/profile');
    }

    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("UPDATE users SET name=? WHERE id=?");
    $st->execute([$name, $uid]);

    flash('success', 'Profil berhasil diperbarui.');
    redirect('/profile');
  }
}
