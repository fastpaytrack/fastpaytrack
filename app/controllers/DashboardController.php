<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;

final class DashboardController
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
    $uid = (int)Auth::id();

    $u = $pdo->prepare("SELECT balance_idr, email, name FROM users WHERE id=? LIMIT 1");
    $u->execute([$uid]);
    $user = $u->fetch() ?: [];

    // Catalogs
    $catalogs = [];
    try {
      $catalogs = $pdo->query("
        SELECT id, title, icon_url, link_url
        FROM dashboard_catalogs
        WHERE is_active=1
        ORDER BY sort_order ASC, id ASC
      ")->fetchAll();
    } catch (\Throwable $e) {
      $catalogs = [];
    }

    // ✅ Ads / Banner Dashboard (MySQL)
    $ads = [];
    try {
      // NOTE: sesuaikan nama kolom kalau berbeda dengan DB kamu
      // Asumsi minimal: media_type, media_url, click_url, is_active, sort_order
      $ads = $pdo->query("
        SELECT id, title, media_type, media_url, click_url
        FROM dashboard_ads
        WHERE is_active=1
        ORDER BY sort_order ASC, id ASC
        LIMIT 3
      ")->fetchAll();
    } catch (\Throwable $e) {
      $ads = [];
    }

    // Products
    $products = $pdo->query("
      SELECT id, sku, name, category, tag, image_url, description
      FROM products
      WHERE is_active=1
      ORDER BY sort_order ASC, id DESC
    ")->fetchAll();

    // Denoms
    $denoms = $pdo->query("
      SELECT product_id, amount_idr
      FROM product_denominations
      WHERE is_active=1
      ORDER BY amount_idr ASC
    ")->fetchAll();

    $denByProduct = [];
    foreach ($denoms as $d) {
      $pid = (int)$d['product_id'];
      $denByProduct[$pid][] = (int)$d['amount_idr'];
    }

    $this->view('dashboard/index.php', [
      'metaTitle' => 'Dashboard • FastPayTrack',
      'metaDesc'  => 'FastPayTrack Dashboard',
      'bodyClass' => 'page-dashboard',

      'products' => $products,
      'denByProduct' => $denByProduct,
      'catalogs' => $catalogs,
      'ads' => $ads, // ✅ penting agar banner muncul
      'csrf' => CSRF::token(),
      'balance' => (int)($user['balance_idr'] ?? 0),
      'user' => $user
    ]);
  }
}
