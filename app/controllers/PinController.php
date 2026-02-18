<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use function App\Lib\redirect;
use function App\Lib\flash;

final class PinController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  // GET /pin
  public function show(): void
  {
    Auth::requireAuth();
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("SELECT pin_hash IS NOT NULL AS has_pin FROM users WHERE id=? LIMIT 1");
    $st->execute([$uid]);
    $row = $st->fetch();
    $hasPin = (int)($row['has_pin'] ?? 0) === 1;

    $this->view('profile/pin.php', [
      'csrf' => CSRF::token(),
      'hasPin' => $hasPin
    ]);
  }

  // POST /pin
  public function save(): void
  {
    CSRF::check();
    Auth::requireAuth();

    $pin = trim((string)($_POST['pin'] ?? ''));
    $pin2 = trim((string)($_POST['pin2'] ?? ''));

    if (!preg_match('/^\d{6}$/', $pin)) {
      flash('error', 'PIN harus 6 digit angka.');
      redirect('/pin');
    }
    if ($pin !== $pin2) {
      flash('error', 'Konfirmasi PIN tidak sama.');
      redirect('/pin');
    }

    $hash = password_hash($pin, PASSWORD_BCRYPT);

    $pdo = DB::pdo();
    $uid = Auth::id();
    $upd = $pdo->prepare("UPDATE users SET pin_hash=?, pin_updated_at=UTC_TIMESTAMP() WHERE id=?");
    $upd->execute([$hash, $uid]);

    flash('success', 'PIN berhasil disimpan.');
    redirect('/profile');
  }
}
