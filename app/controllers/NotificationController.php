<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use function App\Lib\redirect;
use function App\Lib\flash;

final class NotificationController
{
  private function view(string $file, array $data = []): void
  {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  public function index(): void
  {
    if (!Auth::check()) redirect('/login');

    $pdo = DB::pdo();
    $uid = (int)Auth::id();

    $st = $pdo->prepare("SELECT notify_login_email FROM users WHERE id=? LIMIT 1");
    $st->execute([$uid]);
    $row = $st->fetch() ?: ['notify_login_email' => 1];

    $enabled = (int)($row['notify_login_email'] ?? 1) === 1;

    $this->view('settings/notifications.php', [
      'csrf' => CSRF::token(),
      'enabled' => $enabled,
    ]);
  }

  public function toggleLoginEmail(): void
  {
    if (!Auth::check()) {
      http_response_code(401);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
      exit;
    }

    // Wajib: CSRF
    CSRF::check();

    $uid = (int)Auth::id();
    $enabled = (int)($_POST['enabled'] ?? 0) === 1 ? 1 : 0;

    $pdo = DB::pdo();
    $upd = $pdo->prepare("UPDATE users SET notify_login_email=? WHERE id=?");
    $upd->execute([$enabled, $uid]);

    $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
           || (strpos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false);

    if ($isAjax) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['ok' => true, 'enabled' => (bool)$enabled]);
      exit;
    }

    flash('success', 'Pengaturan notifikasi berhasil diperbarui.');
    redirect('/settings/notifications');
  }
}
