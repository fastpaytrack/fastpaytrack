<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminGuard;
use App\Lib\AdminUtil;
use function App\Lib\redirect;
use function App\Lib\flash;

final class ProductsController
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

    $q = trim((string)($_GET['q'] ?? ''));

    if ($q !== '') {
      $st = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
      $st->execute(['%' . $q . '%']);
    } else {
      $st = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    }
    $products = $st->fetchAll();

    // Denominations count per product
    $denomMap = [];
    $st2 = $pdo->query("SELECT product_id, COUNT(*) AS c FROM product_denominations GROUP BY product_id");
    foreach ($st2->fetchAll() as $r) {
      $denomMap[(int)$r['product_id']] = (int)$r['c'];
    }

    $this->view('products_index.php', [
      'csrf' => CSRF::token(),
      'products' => $products,
      'q' => $q,
      'denomMap' => $denomMap,
      'metaTitle' => 'Admin • Products',
      'page' => 'products',
    ]);
  }

  public function createForm(): void
  {
    AdminGuard::requireAdmin();
    $this->view('products_create.php', [
      'csrf' => CSRF::token(),
      'metaTitle' => 'Admin • Add Product',
      'page' => 'products',
    ]);
  }

  public function createDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $name = trim((string)($_POST['name'] ?? ''));
    $desc = trim((string)($_POST['description'] ?? ''));
    $img = trim((string)($_POST['image_url'] ?? ''));
    $active = (int)($_POST['is_active'] ?? 1) === 1 ? 1 : 0;

    if ($name === '') {
      flash('error', 'Nama produk wajib diisi.');
      redirect('/admin/products/create');
    }

    $pdo = DB::pdo();
    $st = $pdo->prepare("INSERT INTO products (name, description, image_url, is_active) VALUES (?,?,?,?)");
    $st->execute([$name, $desc ?: null, $img ?: null, $active]);

    flash('success', 'Product berhasil ditambahkan.');
    redirect('/admin/products');
  }

  public function editForm(): void
  {
    AdminGuard::requireAdmin();
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) redirect('/admin/products');

    $pdo = DB::pdo();

    $st = $pdo->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p) redirect('/admin/products');

    $st2 = $pdo->prepare("SELECT * FROM product_denominations WHERE product_id=? ORDER BY amount_idr ASC");
    $st2->execute([$id]);
    $denoms = $st2->fetchAll();

    $this->view('products_edit.php', [
      'csrf' => CSRF::token(),
      'p' => $p,
      'denoms' => $denoms,
      'metaTitle' => 'Admin • Edit Product',
      'page' => 'products',
    ]);
  }

  public function editDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) redirect('/admin/products');

    $name = trim((string)($_POST['name'] ?? ''));
    $desc = trim((string)($_POST['description'] ?? ''));
    $img = trim((string)($_POST['image_url'] ?? ''));
    $active = (int)($_POST['is_active'] ?? 1) === 1 ? 1 : 0;

    if ($name === '') {
      flash('error', 'Nama produk wajib diisi.');
      redirect('/admin/products/edit?id=' . $id);
    }

    $pdo = DB::pdo();
    $pdo->beginTransaction();
    try {
      $st = $pdo->prepare("UPDATE products SET name=?, description=?, image_url=?, is_active=? WHERE id=?");
      $st->execute([$name, $desc ?: null, $img ?: null, $active, $id]);

      // Replace denominations (simple + safe)
      $pdo->prepare("DELETE FROM product_denominations WHERE product_id=?")->execute([$id]);

      $labels = (array)($_POST['denom_label'] ?? []);
      $amounts = (array)($_POST['denom_amount'] ?? []);

      $ins = $pdo->prepare("INSERT INTO product_denominations (product_id, label, amount_idr) VALUES (?,?,?)");
      for ($i=0; $i<count($labels); $i++){
        $lab = trim((string)$labels[$i]);
        $amtRaw = preg_replace('/[^0-9]/', '', (string)($amounts[$i] ?? ''));
        $amt = (int)$amtRaw;
        if ($lab === '' || $amt <= 0) continue;
        $ins->execute([$id, $lab, $amt]);
      }

      $pdo->commit();
    } catch (\Throwable $e){
      $pdo->rollBack();
      error_log("ADMIN PRODUCTS EDIT ERROR: " . $e->getMessage());
      flash('error', 'Gagal menyimpan product.');
      redirect('/admin/products/edit?id=' . $id);
    }

    flash('success', 'Product berhasil diperbarui.');
    redirect('/admin/products/edit?id=' . $id);
  }

  public function deleteDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) redirect('/admin/products');

    $pdo = DB::pdo();
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    flash('success', 'Product berhasil dihapus.');
    redirect('/admin/products');
  }
}
