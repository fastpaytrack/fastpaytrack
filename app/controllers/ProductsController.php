<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;

final class ProductsController
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

    // saldo
    $u = $pdo->prepare("SELECT balance_idr, email, name FROM users WHERE id=? LIMIT 1");
    $u->execute([$uid]);
    $user = $u->fetch() ?: [];

    // filters
    $cat = trim((string)($_GET['cat'] ?? ''));
    $q   = trim((string)($_GET['q'] ?? ''));

    // categories list (buat chips)
    $cats = $pdo->query("
      SELECT DISTINCT category
      FROM products
      WHERE is_active=1 AND category IS NOT NULL AND category <> ''
      ORDER BY category ASC
    ")->fetchAll();

    // products query
    $where = "WHERE is_active=1";
    $params = [];

    if ($cat !== '') {
      $where .= " AND category = ?";
      $params[] = $cat;
    }

    if ($q !== '') {
      $where .= " AND (name LIKE ? OR category LIKE ? OR description LIKE ?)";
      $like = '%' . $q . '%';
      $params[] = $like;
      $params[] = $like;
      $params[] = $like;
    }

    $st = $pdo->prepare("
      SELECT id, sku, name, category, tag, image_url, description
      FROM products
      $where
      ORDER BY sort_order ASC, id DESC
    ");
    $st->execute($params);
    $products = $st->fetchAll();

    // denoms
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

    $this->view('products/index.php', [
      'products' => $products,
      'denByProduct' => $denByProduct,
      'csrf' => CSRF::token(),
      'balance' => (int)($user['balance_idr'] ?? 0),
      'user' => $user,
      'cats' => $cats,
      'activeCat' => $cat,
      'q' => $q,
    ]);
  }

  /**
   * Kompatibilitas dengan router kamu yang memanggil /product -> detail()
   * (log kamu: Action not found ...::detail()).
   */
  public function detail(): void
  {
    $this->show();
  }

  /**
   * Halaman detail produk:
   * URL yang disarankan:
   * - /product?id=123
   * - /product?sku=STEAM-001 (opsional kalau kamu pakai sku)
   */
  public function show(): void
  {
    Auth::requireAuth();
    $pdo = DB::pdo();
    $uid = (int)Auth::id();

    // user + saldo (biar header konsisten)
    $u = $pdo->prepare("SELECT balance_idr, email, name FROM users WHERE id=? LIMIT 1");
    $u->execute([$uid]);
    $user = $u->fetch() ?: [];
    $balance = (int)($user['balance_idr'] ?? 0);

    // ambil param
    $id  = (int)($_GET['id'] ?? 0);
    $sku = trim((string)($_GET['sku'] ?? ''));

    if ($id <= 0 && $sku === '') {
      header('Location: /products');
      exit;
    }

    // fetch product
    if ($id > 0) {
      $st = $pdo->prepare("
        SELECT id, sku, name, category, tag, image_url, description
        FROM products
        WHERE is_active=1 AND id=? LIMIT 1
      ");
      $st->execute([$id]);
    } else {
      $st = $pdo->prepare("
        SELECT id, sku, name, category, tag, image_url, description
        FROM products
        WHERE is_active=1 AND sku=? LIMIT 1
      ");
      $st->execute([$sku]);
    }

    $product = $st->fetch() ?: null;
    if (!$product) {
      http_response_code(404);
      $this->view('products/show.php', [
        'product' => null,
        'denoms' => [],
        'csrf' => CSRF::token(),
        'balance' => $balance,
        'user' => $user,
        'related' => [],
      ]);
      return;
    }

    $pid = (int)$product['id'];

    // denoms untuk product ini
    $d = $pdo->prepare("
      SELECT amount_idr
      FROM product_denominations
      WHERE is_active=1 AND product_id=?
      ORDER BY amount_idr ASC
    ");
    $d->execute([$pid]);
    $rows = $d->fetchAll();
    $denoms = [];
    foreach ($rows as $r) $denoms[] = (int)$r['amount_idr'];

    // related (opsional) berdasarkan category
    $related = [];
    $cat = trim((string)($product['category'] ?? ''));
    if ($cat !== '') {
      $rel = $pdo->prepare("
        SELECT id, sku, name, category, tag, image_url, description
        FROM products
        WHERE is_active=1 AND category=? AND id<>?
        ORDER BY sort_order ASC, id DESC
        LIMIT 6
      ");
      $rel->execute([$cat, $pid]);
      $related = $rel->fetchAll() ?: [];
    }

    $this->view('products/show.php', [
      'product' => $product,
      'denoms' => $denoms,
      'csrf' => CSRF::token(),
      'balance' => $balance,
      'user' => $user,
      'related' => $related,
    ]);
  }
}
