<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use function App\Lib\redirect;
use function App\Lib\flash;

final class OrderController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  public function detail(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['code'] ?? ''));
    if (!$code) redirect('/orders');

    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("SELECT * FROM orders WHERE order_code=? AND user_id=? LIMIT 1");
    $st->execute([$code, $uid]);
    $order = $st->fetch();

    if (!$order) {
      flash('error', 'Order tidak ditemukan.');
      redirect('/orders');
    }

    $it = $pdo->prepare("
      SELECT product_name, amount_idr, qty, line_total
      FROM order_items
      WHERE order_id=?
      ORDER BY id ASC
    ");
    $it->execute([(int)$order['id']]);
    $items = $it->fetchAll();

    $this->view('orders/detail.php', [
      'order' => $order,
      'items' => $items
    ]);
  }
}
