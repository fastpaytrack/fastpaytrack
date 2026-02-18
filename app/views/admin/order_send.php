<?php
use App\Lib\AdminUtil;

function money_idr(int $n): string { return AdminUtil::money_idr($n); }
?>
<div class="adminWrap">
  <aside class="side">
    <div class="profile">
      <div class="avatar"></div>
      <div style="min-width:0;">
        <div class="pname">Admin</div>
        <div class="pmail">FastPayTrack</div>
      </div>
    </div>

    <nav class="nav">
      <a class="active" href="/admin/dashboard">Dashboard</a>
      <a href="/admin/products">Product</a>
      <a href="/admin/users">Manage User</a>
      <a href="/admin/transactions">Transactions</a>
      <a href="/admin/store">Manage Store</a>
      <a href="/admin/settings">Settings</a>
    </nav>

    <a class="logout" href="/admin/logout">Logout</a>
  </aside>

  <main class="main">
    <div class="topbar">
      <div class="brand">
        <div>
          FASTPAYTRACK.
          <small>Order PAID</small>
        </div>
      </div>
      <div class="topIcons">
        <a class="iconBtn" href="/admin/dashboard" title="Back">←</a>
      </div>
    </div>

    <div style="margin-top:14px; display:grid; grid-template-columns: 1.2fr .8fr; gap:12px;">
      <div class="panel">
        <div class="panelHead">Order: <?= e((string)$o['order_code']) ?></div>
        <div class="panelBody">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div style="border:1px solid var(--border);border-radius:12px;padding:10px;">
              <div style="font-weight:800;font-size:12px;">Pembeli</div>
              <div style="margin-top:6px;font-weight:600;font-size:12px;"><?= e((string)$o['customer_email']) ?></div>
            </div>
            <div style="border:1px solid var(--border);border-radius:12px;padding:10px;">
              <div style="font-weight:800;font-size:12px;">Penerima</div>
              <div style="margin-top:6px;font-weight:600;font-size:12px;"><?= e((string)$o['customer_email']) ?></div>
            </div>
          </div>

          <div style="margin-top:12px; border:1px solid var(--border);border-radius:12px;padding:10px;">
            <div style="font-weight:800;font-size:12px;">Items</div>
            <div style="margin-top:8px;font-size:12px;">
              <?php foreach (($items ?? []) as $it): ?>
                <div style="display:flex;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px solid rgba(229,231,235,.7);">
                  <div>
                    <div style="font-weight:700;"><?= e((string)$it['title']) ?></div>
                    <div style="color:var(--muted);font-weight:600;font-size:11px;">
                      Nominal: <?= e(money_idr((int)($it['denom_amount'] ?? 0))) ?> • Qty: <?= (int)$it['qty'] ?>
                    </div>
                  </div>
                  <div style="font-weight:800;">
                    <?= e(money_idr((int)$it['line_total_idr'])) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div style="margin-top:10px; text-align:right; font-weight:900;">
              Total: <?= e(money_idr((int)$o['grand_total_idr'])) ?>
            </div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panelHead">FORM KIRIM PRODUK VOUCHER KE USER</div>
        <div class="panelBody">
          <form method="POST" action="/admin/orders/send">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">

            <label style="display:block;font-weight:700;font-size:12px;color:var(--muted);">Email penerima produk:</label>
            <input type="text" value="<?= e((string)$o['customer_email']) ?>" disabled
              style="width:100%;margin-top:6px;border:1px solid var(--border);border-radius:10px;padding:12px;font-family:inherit;font-weight:600;">

            <label style="display:block;margin-top:12px;font-weight:700;font-size:12px;color:var(--muted);">Input produk voucher/gift card:</label>
            <textarea name="voucher_text" placeholder="Masukkan voucher/gift card di sini..."
              style="width:100%;min-height:140px;margin-top:6px;border:1px solid var(--border);border-radius:10px;padding:12px;font-family:inherit;font-weight:600;"></textarea>

            <button type="submit" style="margin-top:12px;width:100%;height:44px;border:none;border-radius:10px;background:var(--blue);color:#fff;font-weight:800;cursor:pointer;">
              Kirim Voucher/Gift Card
            </button>

            <div style="margin-top:10px;color:var(--muted);font-size:11px;font-weight:600;line-height:1.45;">
              *input voucher/gift card ke kolom input, pastikan voucher valid sesuai nominal, lalu klik tombol “Kirim Voucher/Gift Card” agar pelanggan menerima via email.
            </div>

            <a href="/admin/dashboard" class="btnSmall gray" style="display:inline-flex;align-items:center;justify-content:center;width:100%;height:42px;border-radius:10px;margin-top:10px;">
              Back
            </a>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
