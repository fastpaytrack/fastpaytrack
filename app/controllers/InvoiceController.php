<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\MailerSMTP;
use function App\Lib\redirect;
use function App\Lib\flash;

final class InvoiceController
{
  private function renderInvoiceHtml(array $order, array $items): string
  {
    $orderCode = htmlspecialchars((string)$order['order_code']);
    $buyer = htmlspecialchars((string)$order['buyer_email']);
    $receiver = htmlspecialchars((string)$order['receiver_email']);
    $provider = htmlspecialchars((string)($order['payment_provider'] ?? '-'));
    $status = htmlspecialchars((string)($order['status'] ?? '-'));
    $created = htmlspecialchars((string)($order['created_at'] ?? '-'));

    $rows = '';
    foreach ($items as $it) {
      $name = htmlspecialchars((string)$it['product_name']);
      $qty  = (int)$it['qty'];
      $amt  = (int)$it['amount_idr'];
      $line = (int)$it['line_total'];

      $rows .= "<tr>
        <td style='padding:12px;border-bottom:1px solid #e5e7eb;'>
          <div style='font-weight:900;font-size:14px;line-height:1.2'>{$name}</div>
          <div style='color:#64748b;font-size:12px;font-weight:700;margin-top:4px;'>
            Nominal: Rp " . number_format($amt,0,',','.') . " • Qty: {$qty}
          </div>
        </td>
        <td style='padding:12px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:900;white-space:nowrap;'>
          Rp " . number_format($line,0,',','.') . "
        </td>
      </tr>";
    }

    $subtotal = (int)$order['subtotal'];
    $fee = (int)$order['fee'];
    $discount = (int)$order['discount'];
    $total = (int)$order['total'];

    return "<!doctype html>
<html lang='id'>
<head>
  <meta charset='utf-8' />
  <meta name='viewport' content='width=device-width,initial-scale=1' />
  <title>Invoice {$orderCode}</title>
  <style>
    :root{
      --border:#e5e7eb;
      --muted:#64748b;
      --text:#0f172a;
      --bg:#f1f5f9;
      --card:#ffffff;
      --primary:#4f46e5;
      --primary2:#4338ca;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      background:var(--bg);
      font-family: ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      color:var(--text);
    }
    .page{
      max-width:920px;
      margin:18px auto;
      padding:0 14px 22px;
    }

    .invoiceCard{
      background:var(--card);
      border:1px solid var(--border);
      border-radius:18px;
      overflow:hidden;
    }
    .invTop{
      padding:16px 16px 12px;
      border-bottom:1px solid var(--border);
      display:flex;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
      align-items:flex-start;
    }
    .brand{
      font-weight:1000;
      font-size:18px;
      letter-spacing:.2px;
    }
    .sub{
      color:var(--muted);
      font-weight:800;
      margin-top:4px;
      font-size:13px;
    }
    .metaRight{
      text-align:right;
      min-width:240px;
    }
    .metaLine{
      color:var(--muted);
      font-weight:800;
      font-size:12.5px;
      margin-top:4px;
    }
    .mono{ font-family: ui-monospace, Menlo, Consolas, monospace; font-weight:900; }

    .grid2{
      display:grid;
      grid-template-columns:1fr;
      gap:12px;
      padding:14px 16px;
    }
    @media(min-width:720px){
      .grid2{ grid-template-columns:1fr 1fr; }
    }
    .box{
      border:1px solid var(--border);
      border-radius:16px;
      padding:12px;
      background:#fff;
    }
    .boxLabel{
      color:var(--muted);
      font-weight:900;
      font-size:12px;
    }
    .boxValue{
      margin-top:6px;
      font-weight:1000;
      font-size:14px;
      word-break:break-word;
    }

    .tableWrap{
      padding:0 16px 14px;
    }
    table{ width:100%; border-collapse:collapse; }
    thead th{
      text-align:left;
      padding:10px 12px;
      background:#f8fafc;
      color:var(--muted);
      font-size:12px;
      font-weight:900;
      border-top:1px solid var(--border);
      border-bottom:1px solid var(--border);
    }
    thead th:last-child{ text-align:right; }

    .sumWrap{
      padding:0 16px 18px;
      display:flex;
      justify-content:flex-end;
    }
    .sumBox{
      width:min(380px,100%);
      border:1px solid var(--border);
      border-radius:16px;
      padding:12px;
      background:#fff;
    }
    .sumRow{
      display:flex;
      justify-content:space-between;
      gap:10px;
      margin:6px 0;
      font-weight:900;
      color:#0f172a;
    }
    .sumRow small{ color:var(--muted); font-weight:900; }
    .hr{ height:1px; background:var(--border); margin:10px 0; }
    .totalRow{ font-size:16px; font-weight:1000; }

    /* Print/PDF: rapikan */
    @media print{
      body{ background:#fff; }
      .page{ margin:0; padding:0; }
      .invoiceCard{ border:none; border-radius:0; }
    }
  </style>
</head>
<body>
  <div class='page'>

    <div class='invoiceCard'>
      <div class='invTop'>
        <div>
          <div class='brand'>FastPayTrack</div>
          <div class='sub'>Invoice</div>
        </div>
        <div class='metaRight'>
          <div style='font-weight:1000;'>Order: <span class='mono'>{$orderCode}</span></div>
          <div class='metaLine'>Tanggal: {$created}</div>
          <div class='metaLine'>Status: <b>{$status}</b></div>
          <div class='metaLine'>Provider: {$provider}</div>
        </div>
      </div>

      <div class='grid2'>
        <div class='box'>
          <div class='boxLabel'>Pembeli</div>
          <div class='boxValue'>{$buyer}</div>
        </div>
        <div class='box'>
          <div class='boxLabel'>Penerima</div>
          <div class='boxValue'>{$receiver}</div>
        </div>
      </div>

      <div class='tableWrap'>
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>{$rows}</tbody>
        </table>
      </div>

      <div class='sumWrap'>
        <div class='sumBox'>
          <div class='sumRow'><span>Subtotal</span><span>Rp " . number_format($subtotal,0,',','.') . "</span></div>
          <div class='sumRow'><span>Biaya layanan</span><span>Rp " . number_format($fee,0,',','.') . "</span></div>
          <div class='sumRow'><span>Diskon</span><span>- Rp " . number_format($discount,0,',','.') . "</span></div>
          <div class='hr'></div>
          <div class='sumRow totalRow'><span>Total</span><span>Rp " . number_format($total,0,',','.') . "</span></div>
        </div>
      </div>

      <div style='padding:0 16px 16px;color:var(--muted);font-size:12px;font-weight:800;'>
        Terima kasih sudah menggunakan FastPayTrack.
      </div>

    </div>

  </div>
</body>
</html>";
  }

  private function loadOrderAndItems(string $code): array
  {
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("SELECT * FROM orders WHERE order_code=? AND user_id=? LIMIT 1");
    $st->execute([$code, $uid]);
    $order = $st->fetch();

    if (!$order) {
      flash('error', 'Order tidak ditemukan.');
      redirect('/orders');
    }

    $it = $pdo->prepare("SELECT product_name, amount_idr, qty, line_total FROM order_items WHERE order_id=? ORDER BY id ASC");
    $it->execute([(int)$order['id']]);
    $items = $it->fetchAll();

    return [$order, $items];
  }

  // Halaman invoice normal (tombol responsive)
  public function show(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['code'] ?? ''));
    if (!$code) redirect('/orders');

    [$order, $items] = $this->loadOrderAndItems($code);
    $invoiceHtml = $this->renderInvoiceHtml($order, $items);
    $csrf = CSRF::token();

    // Toolbar responsive + hide on print
    echo "
    <style>
      .invToolbar{
        max-width:920px;margin:14px auto 12px;padding:0 14px;
        display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;
        font-family: ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      }
      .invLeftLink{
        color:#4f46e5;font-weight:1000;text-decoration:none;display:inline-flex;gap:8px;align-items:center;
      }
      .invBtns{
        display:grid;gap:10px;grid-template-columns:1fr 1fr;
        width:min(560px,100%);
      }
      .invBtn{
        height:44px;border-radius:16px;border:1px solid rgba(15,23,42,.12);
        font-weight:1000;text-decoration:none;display:flex;align-items:center;justify-content:center;
        padding:0 12px;cursor:pointer;
      }
      .invBtnPrimary{ background:#4f46e5;color:#fff;border-color:rgba(79,70,229,.25); }
      .invBtnPrimary:hover{ background:#4338ca; }
      .invBtnDark{ background:#0f172a;color:#fff;border-color:rgba(15,23,42,.25); }
      .invBtnDark:hover{ background:#020617; }
      .invBtnGhost{ background:#0ea5e9;color:#fff;border-color:rgba(14,165,233,.25); }
      .invBtnGhost:hover{ background:#0284c7; }

      @media (max-width:520px){
        .invToolbar{ justify-content:center; }
        .invBtns{ grid-template-columns:1fr; }
      }
      @media print{
        .invToolbar{ display:none !important; }
      }
    </style>

    <div class='invToolbar'>
      <a class='invLeftLink' href='/orders'>← Kembali</a>

      <div class='invBtns'>
        <a class='invBtn invBtnPrimary' href='/invoice/html?code=" . htmlspecialchars($code) . "'>Download HTML</a>
        <a class='invBtn invBtnDark' href='/invoice/pdf?code=" . htmlspecialchars($code) . "'>Download PDF</a>
        <form method='POST' action='/invoice/resend' style='margin:0;grid-column:1 / -1;'>
          <input type='hidden' name='_csrf' value='".htmlspecialchars($csrf, ENT_QUOTES)."'>
          <input type='hidden' name='code' value='".htmlspecialchars($code, ENT_QUOTES)."'>
          <button class='invBtn invBtnGhost' type='submit' style='width:100%;border:none;'>Resend Invoice Email</button>
        </form>
      </div>
    </div>
    ";

    echo $invoiceHtml;
  }

  public function downloadHtml(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['code'] ?? ''));
    if (!$code) redirect('/orders');

    [$order, $items] = $this->loadOrderAndItems($code);
    $html = $this->renderInvoiceHtml($order, $items);

    $filename = 'invoice-' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $code) . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo $html;
  }

  public function pdf(): void
  {
    Auth::requireAuth();
    $code = trim((string)($_GET['code'] ?? ''));
    if (!$code) redirect('/orders');

    [$order, $items] = $this->loadOrderAndItems($code);
    $html = $this->renderInvoiceHtml($order, $items);

    $html = str_replace("</body>", "
      <script>
        window.onload = function(){ setTimeout(function(){ window.print(); }, 450); };
      </script>
    </body>", $html);

    echo $html;
  }

  // Resend invoice (tetap)
  public function resend(): void
  {
    CSRF::check();
    Auth::requireAuth();

    $code = trim((string)($_POST['code'] ?? ''));
    if (!$code) redirect('/orders');

    [$order, $items] = $this->loadOrderAndItems($code);

    if (strtoupper(trim((string)$order['status'])) !== 'PAID') {
      flash('error', 'Invoice hanya bisa dikirim ulang jika status order sudah PAID.');
      redirect('/invoice?code=' . urlencode($code));
    }

    $buyer = (string)$order['buyer_email'];
    $receiver = (string)$order['receiver_email'];

    $subject = "FastPayTrack - Invoice Order {$code}";
    $html = $this->renderInvoiceHtml($order, $items);

    try { if ($buyer) MailerSMTP::send($buyer, $subject, $html); } catch (\Throwable $e) { error_log("RESEND INVOICE BUYER ERROR: ".$e->getMessage()); }
    try { if ($receiver && strtolower($receiver) !== strtolower($buyer)) MailerSMTP::send($receiver, $subject, $html); } catch (\Throwable $e) { error_log("RESEND INVOICE RECEIVER ERROR: ".$e->getMessage()); }

    flash('success', 'Invoice berhasil dikirim ulang ke email pembeli/penerima.');
    redirect('/invoice?code=' . urlencode($code));
  }
}
