<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\AdminAuth;
use App\Lib\AdminUtil;

final class DashboardController
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
    AdminAuth::require();
    $pdo = DB::pdo();

    // Wallet total
    $walletTotal = (int)$pdo->query("SELECT COALESCE(SUM(balance_idr),0) FROM users")->fetchColumn();

    // Total users
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Active users (last seen within 2 days) from user_login_devices
    $activeUsers = (int)$pdo->query("
      SELECT COUNT(*) FROM (
        SELECT user_id, MAX(last_seen_at) AS last_seen
        FROM user_login_devices
        WHERE revoked_at IS NULL AND last_seen_at IS NOT NULL
        GROUP BY user_id
      ) x
      WHERE x.last_seen >= (NOW() - INTERVAL 2 DAY)
    ")->fetchColumn();

    // Online users (last seen within 5 minutes)
    $onlineUsers = (int)$pdo->query("
      SELECT COUNT(*) FROM (
        SELECT user_id, MAX(last_seen_at) AS last_seen
        FROM user_login_devices
        WHERE revoked_at IS NULL AND last_seen_at IS NOT NULL
        GROUP BY user_id
      ) x
      WHERE x.last_seen >= (NOW() - INTERVAL 5 MINUTE)
    ")->fetchColumn();

    // Total products
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // Total products sold (completed orders)
    $totalProductsSold = (int)$pdo->query("
      SELECT COALESCE(SUM(oi.qty),0)
      FROM order_items oi
      JOIN orders o ON o.id = oi.order_id
      WHERE o.status IN ('COMPLETED','DELIVERED')
    ")->fetchColumn();

    // Earnings = fee_total dari order completed/delivered
    $earnings = (int)$pdo->query("
      SELECT COALESCE(SUM(fee_total_idr),0)
      FROM orders
      WHERE status IN ('COMPLETED','DELIVERED')
    ")->fetchColumn();

    // Gross income = total paid + wallet total + earnings (sesuai request kamu)
    $paidTotal = (int)$pdo->query("
      SELECT COALESCE(SUM(grand_total_idr),0)
      FROM orders
      WHERE status IN ('PAID','COMPLETED','DELIVERED')
    ")->fetchColumn();
    $grossIncome = $paidTotal + $walletTotal + $earnings;

    // Notifications Center (latest: topup, transfer, order)
    $notif = [
      'topups' => $pdo->query("
        SELECT t.id, t.amount_idr, t.status, t.created_at, u.email
        FROM wallet_topups t
        JOIN users u ON u.id=t.user_id
        ORDER BY t.id DESC
        LIMIT 5
      ")->fetchAll(),
      'transfers' => $pdo->query("
        SELECT w.id, w.amount_idr, w.created_at, u1.email AS from_email, u2.email AS to_email
        FROM wallet_transfers w
        JOIN users u1 ON u1.id=w.from_user_id
        JOIN users u2 ON u2.id=w.to_user_id
        ORDER BY w.id DESC
        LIMIT 5
      ")->fetchAll(),
      'orders' => $pdo->query("
        SELECT o.id, o.order_code, o.status, o.grand_total_idr, o.created_at, u.email
        FROM orders o
        JOIN users u ON u.id=o.user_id
        ORDER BY o.id DESC
        LIMIT 5
      ")->fetchAll(),
    ];

    // Recent Orders (table)
    $recentOrders = $pdo->query("
      SELECT o.id, o.order_code, o.status, o.payment_method, o.channel,
             o.grand_total_idr, o.created_at,
             u.email AS customer_email,
             p.title AS item_title,
             (SELECT COUNT(*) FROM order_deliveries d WHERE d.order_id=o.id) AS delivered_count
      FROM orders o
      JOIN users u ON u.id=o.user_id
      JOIN order_items oi ON oi.order_id=o.id
      JOIN products p ON p.id=oi.product_id
      ORDER BY o.id DESC
      LIMIT 8
    ")->fetchAll();

    // Order Status bottom table (latest 10)
    $orderStatus = $pdo->query("
      SELECT o.created_at, p.title AS item_title, o.order_code, o.payment_method AS transaction_type,
             o.channel, o.status, o.grand_total_idr, u.email
      FROM orders o
      JOIN users u ON u.id=o.user_id
      JOIN order_items oi ON oi.order_id=o.id
      JOIN products p ON p.id=oi.product_id
      ORDER BY o.id DESC
      LIMIT 10
    ")->fetchAll();

    $this->view('dashboard.php', [
      'me' => AdminAuth::user(),
      'cards' => [
        'gross_income' => $grossIncome,
        'wallet_total' => $walletTotal,
        'earnings' => $earnings,
        'total_users' => $totalUsers,
        'active_users' => $activeUsers,
        'online_users' => $onlineUsers,
        'total_products' => $totalProducts,
        'total_products_sold' => $totalProductsSold,
      ],
      'notif' => $notif,
      'recentOrders' => $recentOrders,
      'orderStatus' => $orderStatus,
      'fmt' => fn(int $n) => AdminUtil::money_idr($n),
    ]);
  }
}
