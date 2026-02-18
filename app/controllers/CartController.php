<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\CSRF;
use App\Lib\Auth;
use function App\Lib\redirect;
use function App\Lib\flash;

final class CartController
{
  public function add(): void
  {
    CSRF::check();
    Auth::requireAuth();

    $pid = (int)($_POST['product_id'] ?? 0);
    $amount = (int)($_POST['amount_idr'] ?? 0);

    if ($pid <= 0 || $amount <= 0) {
      flash('error', 'Produk/nominal tidak valid.');
      redirect('/dashboard');
    }

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $key = $pid . ':' . $amount;

    if (!isset($_SESSION['cart'][$key])) {
      $_SESSION['cart'][$key] = ['product_id' => $pid, 'amount_idr' => $amount, 'qty' => 1];
    } else {
      $_SESSION['cart'][$key]['qty'] = (int)$_SESSION['cart'][$key]['qty'] + 1;
    }

    flash('success', 'Item ditambahkan ke keranjang.');
    redirect('/dashboard');
  }

  public function update(): void
  {
    CSRF::check();
    Auth::requireAuth();
    $key = (string)($_POST['key'] ?? '');
    $qty = (int)($_POST['qty'] ?? 1);

    if (isset($_SESSION['cart'][$key])) {
      $_SESSION['cart'][$key]['qty'] = max(1, $qty);
    }
    redirect('/checkout');
  }

  public function remove(): void
  {
    CSRF::check();
    Auth::requireAuth();
    $key = (string)($_POST['key'] ?? '');
    unset($_SESSION['cart'][$key]);
    redirect('/checkout');
  }
}
