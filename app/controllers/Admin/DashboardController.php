<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\AdminGuard;
use App\Lib\AdminUtil;

final class DashboardController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../../views/admin/partials/head.php';
    require __DIR__ . '/../../views/admin/' . $file;
    require __DIR__ . '/../../views/admin/partials/foot.php';
  }

  public function index(): void
  {
    AdminGuard::requireAdmin();
    $pdo = DB::pdo();

    // ===== METRICS =====
    // Wallet Balance
    $walletTotal = (int)($pdo->query("SELECT COALESCE(SUM(balance_idr),0) AS s FROM users")->fetch()['s'] ?? 0);

    // Total User
    $totalUser = (int)($pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'] ?? 0);

    // User Active (2 hari terakhir) -> pakai user_login_devices.last_seen_at kalau ada
    $userActive = 0;
    try {
      $st = $pdo->query("SELECT COUNT(DISTINCT user_id) AS c
        FROM user_login_devices
        WHERE last_seen_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
      ");
      $userActive = (int)($st->fetch()['c'] ?? 0);
    } catch (\Throwable $e) {
      $userActive = 0;
    }

    // Online user -> last_seen_at 10 menit terakhir (best effort)
    $onlineUser = 0;
    try {
      $st = $pdo->query("SELECT COUNT(DISTINCT user_id) AS c
        FROM user_login_devices
        WHERE last_seen_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
      ");
      $onlineUser = (int)($st->fetch()['c'] ?? 0);
    } catch (\Throwable $e) {
      $onlineUser = 0;
    }

    // Total Products
    $totalProducts = (int)($pdo->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'] ?? 0);

    // Total Products Sold (orders paid)
    $totalProductsSold = 0;
    try {
      $st = $pdo->query("SELECT COUNT(*) AS c FROM orders WHERE status='paid'");
      $totalProductsSold = (int)($st->fetch()['c'] ?? 0);
    } catch (\Throwable $e) { $totalProductsSold = 0; }

    // Earnings = fee jasa layanan dari order paid
    // (pakai kolom fee/service_fee kalau ada, kalau tidak ada → 0)
    $earnings = 0;
    try {
      $st = $pdo->query("SELECT COALESCE(SUM(service_fee_idr),0) AS s FROM orders WHERE status='paid'");
      $earnings = (int)($st->fetch()['s'] ?? 0);
    } catch (\Throwable $e) { $earnings = 0; }

    // Gross Income = total paid amount + wallet total + earnings (sesuai request kamu)
    $paidTotal = 0;
    try {
      $st = $pdo->query("SELECT COALESCE(SUM(total_amount_idr),0) AS s FROM orders WHERE status='paid'");
      $paidTotal = (int)($st->fetch()['s'] ?? 0);
    } catch (\Throwable $e) { $paidTotal = 0; }

    $grossIncome = $paidTotal + $walletTotal + $earnings;

    // ===== NOTIFICATIONS CENTER (topup/transfer/order) =====
    $notif = [];
    try {
      $st = $pdo->query("
        (SELECT 'topup' AS type, id AS ref_id, user_id, amount_idr, created_at
         FROM wallet_topups
         ORDER BY id DESC
         LIMIT 10)
        UNION ALL
        (SELECT 'transfer' AS type, id AS ref_id, from_user_id AS user_id, amount_idr, created_at
         FROM wallet_transfers
         ORDER BY id DESC
         LIMIT 10)
        UNION ALL
        (SELECT 'order' AS type, id AS ref_id, user_id, total_amount_idr AS amount_idr, created_at
         FROM orders
         ORDER BY id DESC
         LIMIT 10)
        ORDER BY created_at DESC
        LIMIT 12
      ");
      $rows = $st->fetchAll();
      if ($rows) {
        // attach user email
        $uSt = $pdo->query("SELECT id,email,name FROM users");
        $users = [];
        foreach ($uSt->fetchAll() as $u) $users[(int)$u['id']] = $u;
        foreach ($rows as $r) {
          $uid = (int)$r['user_id'];
          $u = $users[$uid] ?? null;
          $notif[] = [
            'type' => (string)$r['type'],
            'ref_id' => (int)$r['ref_id'],
            'email' => (string)($u['email'] ?? ''),
            'name' => (string)($u['name'] ?? ''),
            'amount' => (int)$r['amount_idr'],
            'created_at' => (string)$r['created_at'],
          ];
        }
      }
    } catch (\Throwable $e) {
      $notif = [];
    }

    // ===== RECENT ORDERS =====
    $recentOrders = [];
    try {
      $st = $pdo->query("
        SELECT o.*,
               u.email AS user_email,
               u.name AS user_name,
               (SELECT COUNT(*) FROM order_deliveries d WHERE d.order_id=o.id) AS delivered_count
        FROM orders o
        JOIN users u ON u.id=o.user_id
        ORDER BY o.id DESC
        LIMIT 8
      ");
      $recentOrders = $st->fetchAll() ?: [];
    } catch (\Throwable $e) {
      $recentOrders = [];
    }

    // ===== ORDER STATUS TABLE (history) =====
    $orderStatus = [];
    try {
      $st = $pdo->query("
        SELECT o.created_at, o.order_code, o.payment_method, o.status,
               o.total_amount_idr, u.email AS user_email,
               (SELECT GROUP_CONCAT(oi.product_name SEPARATOR ', ')
                FROM order_items oi WHERE oi.order_id=o.id) AS items
        FROM orders o
        JOIN users u ON u.id=o.user_id
        ORDER BY o.id DESC
        LIMIT 10
      ");
      $orderStatus = $st->fetchAll() ?: [];
    } catch (\Throwable $e) {
      $orderStatus = [];
    }

    $this->view('dashboard.php', [
      'grossIncome' => $grossIncome,
      'walletTotal' => $walletTotal,
      'earnings' => $earnings,
      'totalUser' => $totalUser,
      'userActive' => $userActive,
      'onlineUser' => $onlineUser,
      'totalProducts' => $totalProducts,
      'totalProductsSold' => $totalProductsSold,
      'notif' => $notif,
      'recentOrders' => $recentOrders,
      'orderStatus' => $orderStatus,
      'money' => fn(int $n) => AdminUtil::moneyIdr($n),
    ]);
  }
}
