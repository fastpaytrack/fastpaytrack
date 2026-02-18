<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminGuard;
use function App\Lib\redirect;
use function App\Lib\flash;

final class StoreController
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

    $st = $pdo->query("SELECT * FROM admin_store_ads ORDER BY slot ASC");
    $ads = $st->fetchAll();

    $this->view('store.php', [
      'csrf' => CSRF::token(),
      'ads' => $ads,
      'metaTitle' => 'Admin • Manage Store',
      'page' => 'store',
    ]);
  }

  public function saveDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $pdo = DB::pdo();

    $slot = trim((string)($_POST['slot'] ?? 'dashboard_banner_1'));
    $image = trim((string)($_POST['image_url'] ?? ''));
    $target = trim((string)($_POST['target_url'] ?? ''));
    $active = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;

    if ($slot === '') $slot = 'dashboard_banner_1';

    $st = $pdo->prepare("
      INSERT INTO admin_store_ads (slot,image_url,target_url,is_active,updated_at)
      VALUES (?,?,?,?,NOW())
      ON DUPLICATE KEY UPDATE image_url=VALUES(image_url), target_url=VALUES(target_url), is_active=VALUES(is_active), updated_at=NOW()
    ");
    $st->execute([$slot, $image, $target, $active]);

    flash('success', 'Store Ads berhasil disimpan.');
    redirect('/admin/store');
  }
}
