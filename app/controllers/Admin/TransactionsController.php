<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminGuard;

final class TransactionsController
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

    $tab = (string)($_GET['tab'] ?? 'orders');
    if (!in_array($tab, ['orders','topups','transfers'], true)) $tab = 'orders';

    $orders = [];
    $topups = [];
    $transfers = [];

    if ($tab === 'orders') {
      $st = $pdo->query("
        SELECT o.id, o.order_code, o.status, o.total_idr, o.payment_method, o.created_at,
               u.name AS user_name, u.email AS user_email
        FROM orders o
        JOIN users u ON u.id = o.user_id
        ORDER BY o.id DESC
        LIMIT 200
      ");
      $orders = $st->fetchAll();
    }

    if ($tab === 'topups') {
      $st = $pdo->query("
        SELECT t.id, t.status, t.amount_idr, t.provider, t.provider_ref, t.created_at,
               u.name AS user_name, u.email AS user_email
        FROM wallet_topups t
        JOIN users u ON u.id = t.user_id
        ORDER BY t.id DESC
        LIMIT 200
      ");
      $topups = $st->fetchAll();
    }

    if ($tab === 'transfers') {
      $st = $pdo->query("
        SELECT tr.id, tr.amount_idr, tr.note, tr.created_at,
               u1.name AS from_name, u1.email AS from_email,
               u2.name AS to_name,   u2.email AS to_email
        FROM wallet_transfers tr
        JOIN users u1 ON u1.id = tr.from_user_id
        JOIN users u2 ON u2.id = tr.to_user_id
        ORDER BY tr.id DESC
        LIMIT 200
      ");
      $transfers = $st->fetchAll();
    }

    $this->view('transactions.php', [
      'csrf' => CSRF::token(),
      'tab' => $tab,
      'orders' => $orders,
      'topups' => $topups,
      'transfers' => $transfers,
      'metaTitle' => 'Admin • Transactions',
      'page' => 'transactions',
    ]);
  }
}
