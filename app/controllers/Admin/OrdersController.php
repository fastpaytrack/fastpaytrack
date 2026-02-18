<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminAuth;
use App\Lib\MailerSMTP;
use function App\Lib\flash;
use function App\Lib\redirect;

final class OrdersController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../../views/admin/partials/head.php';
    require __DIR__ . '/../../views/partials/flash.php';
    require __DIR__ . '/../../views/admin/' . $file;
    require __DIR__ . '/../../views/admin/partials/foot.php';
  }

  // GET /admin/orders/send?order_id=123
  public function sendForm(): void
  {
    AdminAuth::require();
    $pdo = DB::pdo();

    $orderId = (int)($_GET['order_id'] ?? 0);
    if ($orderId <= 0) redirect('/admin/dashboard');

    $st = $pdo->prepare("
      SELECT o.*, u.email AS customer_email, u.name AS customer_name
      FROM orders o
      JOIN users u ON u.id=o.user_id
      WHERE o.id=?
      LIMIT 1
    ");
    $st->execute([$orderId]);
    $o = $st->fetch();
    if (!$o){
      flash('error', 'Order tidak ditemukan.');
      redirect('/admin/dashboard');
    }

    // item summary
    $items = $pdo->prepare("
      SELECT oi.*, p.title, d.amount_idr AS denom_amount
      FROM order_items oi
      JOIN products p ON p.id=oi.product_id
      LEFT JOIN product_denominations d ON d.id=oi.denomination_id
      WHERE oi.order_id=?
    ");
    $items->execute([$orderId]);
    $rows = $items->fetchAll();

    $alreadyDelivered = (int)$pdo->prepare("SELECT COUNT(*) FROM order_deliveries WHERE order_id=?")
      ->execute([$orderId]) ?: 0;

    $this->view('order_send.php', [
      'csrf' => CSRF::token(),
      'o' => $o,
      'items' => $rows,
    ]);
  }

  // POST /admin/orders/send
  public function sendDo(): void
  {
    CSRF::check();
    AdminAuth::require();
    $pdo = DB::pdo();

    $orderId = (int)($_POST['order_id'] ?? 0);
    $voucher = trim((string)($_POST['voucher_text'] ?? ''));

    if ($orderId <= 0 || $voucher === ''){
      flash('error', 'Voucher/Gift Card wajib diisi.');
      redirect('/admin/orders/send?order_id=' . $orderId);
    }

    // fetch order
    $st = $pdo->prepare("
      SELECT o.*, u.email AS customer_email, u.name AS customer_name
      FROM orders o
      JOIN users u ON u.id=o.user_id
      WHERE o.id=?
      LIMIT 1
    ");
    $st->execute([$orderId]);
    $o = $st->fetch();
    if (!$o){
      flash('error', 'Order tidak ditemukan.');
      redirect('/admin/dashboard');
    }

    // insert delivery (unique order)
    try {
      $ins = $pdo->prepare("INSERT INTO order_deliveries (order_id, delivered_by_admin_id, voucher_text) VALUES (?,?,?)");
      $ins->execute([$orderId, (int)AdminAuth::id(), $voucher]);
    } catch (\Throwable $e){
      flash('error', 'Order ini sudah pernah dikirim / delivered.');
      redirect('/admin/dashboard');
    }

    // update order status to DELIVERED
    $up = $pdo->prepare("UPDATE orders SET status='DELIVERED' WHERE id=? AND status IN ('PAID','COMPLETED','DELIVERED')");
    $up->execute([$orderId]);

    // send email
    $to = (string)$o['customer_email'];
    $subject = "FastPayTrack - Voucher Delivery ({$o['order_code']})";
    $html = "
      <div style='font-family:Arial,sans-serif;color:#0f172a;'>
        <h2>Pesanan kamu sudah dikirim ✅</h2>
        <p>Order ID: <b>{$this->esc((string)$o['order_code'])}</b></p>
        <div style='padding:12px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;'>
          <div style='font-size:12px;color:#64748b;font-weight:700;'>Voucher / Gift Card</div>
          <pre style='white-space:pre-wrap;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:14px;font-weight:700;margin:10px 0 0;'>".$this->esc($voucher)."</pre>
        </div>
        <p style='color:#64748b;font-size:12px;'>Terima kasih sudah berbelanja di FastPayTrack.</p>
      </div>
    ";

    try {
      MailerSMTP::send($to, $subject, $html);
    } catch (\Throwable $e){
      // tetap delivered walau email gagal (opsional)
      flash('error', 'Delivered tersimpan, tapi email gagal terkirim. Cek SMTP/log.');
      redirect('/admin/dashboard');
    }

    flash('success', 'Voucher berhasil dikirim & status berubah menjadi Delivered.');
    redirect('/admin/dashboard');
  }

  private function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }
}
