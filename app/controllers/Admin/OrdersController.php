<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminGuard;
use App\Lib\AdminAuth;
use App\Lib\AdminUtil;
use App\Lib\MailerSMTP;
use function App\Lib\redirect;
use function App\Lib\flash;

final class OrdersController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../../views/admin/partials/head.php';
    require __DIR__ . '/../../views/partials/flash.php';
    require __DIR__ . '/../../views/admin/' . $file;
    require __DIR__ . '/../../views/admin/partials/foot.php';
  }

  // GET /admin/orders
  public function index(): void
  {
    AdminGuard::requireAdmin();
    $pdo = DB::pdo();

    $rows = [];
    try {
      $st = $pdo->query("
        SELECT o.*,
               u.email AS user_email,
               u.name AS user_name,
               (SELECT COUNT(*) FROM order_deliveries d WHERE d.order_id=o.id) AS delivered_count
        FROM orders o
        JOIN users u ON u.id=o.user_id
        ORDER BY o.id DESC
        LIMIT 50
      ");
      $rows = $st->fetchAll() ?: [];
    } catch (\Throwable $e) {
      $rows = [];
    }

    $this->view('orders.php', [
      'rows' => $rows,
      'csrf' => CSRF::token(),
      'money' => fn(int $n) => AdminUtil::moneyIdr($n),
    ]);
  }

  // GET /admin/orders/send?id=123
  public function sendForm(): void
  {
    AdminGuard::requireAdmin();
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) redirect('/admin/orders');

    $pdo = DB::pdo();

    $st = $pdo->prepare("
      SELECT o.*, u.email AS user_email, u.name AS user_name
      FROM orders o
      JOIN users u ON u.id=o.user_id
      WHERE o.id=? LIMIT 1
    ");
    $st->execute([$id]);
    $o = $st->fetch();
    if (!$o) {
      flash('error','Order tidak ditemukan.');
      redirect('/admin/orders');
    }

    // items
    $items = [];
    try {
      $it = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
      $it->execute([$id]);
      $items = $it->fetchAll() ?: [];
    } catch (\Throwable $e) {}

    // cek sudah delivered?
    $del = null;
    try {
      $ds = $pdo->prepare("SELECT * FROM order_deliveries WHERE order_id=? LIMIT 1");
      $ds->execute([$id]);
      $del = $ds->fetch() ?: null;
    } catch (\Throwable $e) {}

    $this->view('order_send.php', [
      'csrf' => CSRF::token(),
      'o' => $o,
      'items' => $items,
      'del' => $del,
      'money' => fn(int $n) => AdminUtil::moneyIdr($n),
    ]);
  }

  // POST /admin/orders/send
  public function sendDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $orderId = (int)($_POST['order_id'] ?? 0);
    $voucher = trim((string)($_POST['voucher_text'] ?? ''));

    if ($orderId <= 0) redirect('/admin/orders');
    if ($voucher === '') {
      flash('error', 'Voucher/Gift Card wajib diisi.');
      redirect('/admin/orders/send?id=' . $orderId);
    }

    $pdo = DB::pdo();

    // load order + user
    $st = $pdo->prepare("
      SELECT o.*, u.email AS user_email, u.name AS user_name
      FROM orders o JOIN users u ON u.id=o.user_id
      WHERE o.id=? LIMIT 1
    ");
    $st->execute([$orderId]);
    $o = $st->fetch();
    if (!$o) {
      flash('error','Order tidak ditemukan.');
      redirect('/admin/orders');
    }

    // insert delivery (unique by order_id)
    try {
      $ins = $pdo->prepare("
        INSERT INTO order_deliveries (order_id, delivered_by_admin_id, voucher_text, delivered_at)
        VALUES (?,?,?,NOW())
        ON DUPLICATE KEY UPDATE
          voucher_text=VALUES(voucher_text),
          delivered_by_admin_id=VALUES(delivered_by_admin_id),
          delivered_at=NOW()
      ");
      $ins->execute([$orderId, AdminAuth::id(), $voucher]);
    } catch (\Throwable $e) {
      flash('error', 'Gagal menyimpan delivery.');
      redirect('/admin/orders/send?id=' . $orderId);
    }

    // EMAIL: kirim voucher
    try {
      $to = (string)$o['user_email'];
      $subj = "FastPayTrack - Produk kamu sudah dikirim ✅";
      $html = "
        <div style='font-family:Arial,sans-serif;color:#111827;'>
          <h2>Produk/Voucher kamu sudah dikirim ✅</h2>
          <p>Order ID: <b>".AdminUtil::e((string)($o['order_code'] ?? $orderId))."</b></p>
          <div style='margin:12px 0;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;'>
            <div style='color:#6b7280;font-size:12px;font-weight:700;'>Voucher / Gift Card</div>
            <div style='font-weight:900;white-space:pre-wrap;'>".AdminUtil::e($voucher)."</div>
          </div>
          <p style='color:#6b7280;font-size:12px;'>Terima kasih telah menggunakan FastPayTrack.</p>
        </div>
      ";
      if ($to) MailerSMTP::send($to, $subj, $html);
    } catch (\Throwable $e) {
      // tetap sukses walau email error
    }

    flash('success', 'Voucher berhasil dikirim & disimpan.');
    redirect('/admin/orders/send?id=' . $orderId);
  }
}
