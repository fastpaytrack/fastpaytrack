<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use function App\Lib\redirect;
use function App\Lib\flash;

final class CheckoutController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  public function show(): void
  {
    Auth::requireAuth();

    $cart = $_SESSION['cart'] ?? [];
    $pdo = DB::pdo();

    // Ambil nama produk untuk tampilan keranjang
    $items = [];
    $subtotal = 0;

    foreach ($cart as $key => $it) {
      $pid = (int)$it['product_id'];
      $amt = (int)$it['amount_idr'];
      $qty = max(1, (int)$it['qty']);

      $st = $pdo->prepare("SELECT id,name,category,image_url FROM products WHERE id=? LIMIT 1");
      $st->execute([$pid]);
      $p = $st->fetch();
      if (!$p) continue;

      $line = $amt * $qty;
      $subtotal += $line;

      $items[] = [
        'key' => $key,
        'product_id' => $pid,
        'name' => (string)$p['name'],
        'category' => (string)$p['category'],
        'image_url' => (string)($p['image_url'] ?? ''),
        'amount_idr' => $amt,
        'qty' => $qty,
        'line_total' => $line
      ];
    }

    // fee/discount demo (nanti bisa diubah)
    $fee = $subtotal > 0 ? max(2000, min(15000, (int)round($subtotal * 0.01))) : 0;
    $discount = $subtotal >= 300000 ? 10000 : 0;
    $total = max(0, $subtotal + $fee - $discount);

    $this->view('cart/checkout.php', [
      'csrf' => CSRF::token(),
      'items' => $items,
      'subtotal' => $subtotal,
      'fee' => $fee,
      'discount' => $discount,
      'total' => $total
    ]);
  }

  public function createOrder(): void
  {
    CSRF::check();
    Auth::requireAuth();

    $buyerEmail = trim((string)($_POST['buyer_email'] ?? ''));
    $receiverEmail = trim((string)($_POST['receiver_email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));

    if (!filter_var($buyerEmail, FILTER_VALIDATE_EMAIL) || !filter_var($receiverEmail, FILTER_VALIDATE_EMAIL)) {
      flash('error', 'Email pembeli dan penerima wajib valid.');
      redirect('/checkout');
    }

    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
      flash('error', 'Keranjang kosong.');
      redirect('/dashboard');
    }

    $pdo = DB::pdo();
    $uid = Auth::id();

    // hitung ulang subtotal dari cart (server-side)
    $items = [];
    $subtotal = 0;

    foreach ($cart as $key => $it) {
      $pid = (int)$it['product_id'];
      $amt = (int)$it['amount_idr'];
      $qty = max(1, (int)$it['qty']);

      $st = $pdo->prepare("SELECT id,name FROM products WHERE id=? AND is_active=1 LIMIT 1");
      $st->execute([$pid]);
      $p = $st->fetch();
      if (!$p) continue;

      $line = $amt * $qty;
      $subtotal += $line;

      $items[] = [
        'product_id' => $pid,
        'product_name' => (string)$p['name'],
        'amount_idr' => $amt,
        'qty' => $qty,
        'line_total' => $line,
      ];
    }

    if (!$items) {
      flash('error', 'Keranjang tidak valid.');
      redirect('/dashboard');
    }

    $fee = max(2000, min(15000, (int)round($subtotal * 0.01)));
    $discount = $subtotal >= 300000 ? 10000 : 0;
    $total = max(0, $subtotal + $fee - $discount);

    $orderCode = 'FP' . date('YmdHis') . random_int(1000, 9999);

    $pdo->beginTransaction();
    try {
      $ins = $pdo->prepare("
        INSERT INTO orders (order_code,user_id,buyer_email,receiver_email,phone,note,currency,subtotal,fee,discount,total,status)
        VALUES (?,?,?,?,?,'', 'IDR',?,?,?,?, 'PENDING')
      ");
      $ins->execute([
        $orderCode,
        $uid,
        $buyerEmail,
        $receiverEmail,
        $phone,
        $subtotal,
        $fee,
        $discount,
        $total
      ]);

      $orderId = (int)$pdo->lastInsertId();

      $insItem = $pdo->prepare("
        INSERT INTO order_items (order_id,product_id,product_name,amount_idr,qty,line_total)
        VALUES (?,?,?,?,?,?)
      ");
      foreach ($items as $it) {
        $insItem->execute([
          $orderId,
          $it['product_id'],
          $it['product_name'],
          $it['amount_idr'],
          $it['qty'],
          $it['line_total']
        ]);
      }

      $pdo->commit();

      // kosongkan cart setelah order dibuat
      unset($_SESSION['cart']);

      flash('success', 'Order berhasil dibuat. (Payment akan kita aktifkan setelah ini)');
      redirect('/orders');

    } catch (\Throwable $e) {
      $pdo->rollBack();
      error_log("CREATE ORDER ERROR: " . $e->getMessage());
      flash('error', 'Gagal membuat order. Cek error_log.');
      redirect('/checkout');
    }
  }

  public function orders(): void
  {
    Auth::requireAuth();
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 50");
    $st->execute([$uid]);
    $orders = $st->fetchAll();

    $this->view('cart/orders.php', [
      'orders' => $orders
    ]);
  }

  public function success(): void
  {
    Auth::requireAuth();
    $this->view('cart/success.php');
  }
}
