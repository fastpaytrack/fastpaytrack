<?php
use App\Lib\AdminUtil;
$money = $money ?? fn(int $n)=>AdminUtil::moneyIdr($n);
$o = $o ?? [];
?>
<div class="topbar">
  <div class="brand">
    <div class="brandTitle">Order PAID</div>
    <div class="brandSub">Form Kirim Produk Voucher ke User</div>
  </div>
</div>

<div class="grid2">
  <div class="panel">
    <div class="panelHead">Invoice</div>
    <div class="panelBody">
      <div style="font-weight:800;margin-bottom:10px;">FastPayTrack</div>

      <div style="display:grid;gap:10px;">
        <div class="field">
          <label>Order</label>
          <input value="<?= ae((string)($o['order_code'] ?? $o['id'] ?? '')) ?>" disabled>
        </div>

        <div class="formRow">
          <div class="field">
            <label>Tanggal</label>
            <input value="<?= ae((string)($o['created_at'] ?? '')) ?>" disabled>
          </div>
          <div class="field">
            <label>Status</label>
            <input value="<?= ae((string)($o['status'] ?? '')) ?>" disabled>
          </div>
        </div>

        <div class="field">
          <label>Pembeli</label>
          <input value="<?= ae((string)($o['user_email'] ?? '')) ?>" disabled>
        </div>

        <div class="field">
          <label>Total</label>
          <input value="<?= ae($money((int)($o['total_amount_idr'] ?? 0))) ?>" disabled>
        </div>

        <?php if (!empty($items)): ?>
          <div style="margin-top:8px;border-top:1px solid #eef2f7;padding-top:10px;">
            <div style="font-weight:800;margin-bottom:8px;">Items</div>
            <?php foreach ($items as $it): ?>
              <div style="padding:8px 0;border-bottom:1px solid #f3f4f6;">
                <div style="font-weight:700;"><?= ae((string)($it['product_name'] ?? '')) ?></div>
                <div style="color:var(--muted);font-size:12px;font-weight:600;">
                  Nominal: <?= ae($money((int)($it['price_idr'] ?? 0))) ?> • Qty: <?= ae((string)($it['qty'] ?? 1)) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panelHead">Kirim Voucher / Gift Card</div>
    <div class="panelBody">
      <div class="field">
        <label>Email penerima produk</label>
        <input value="<?= ae((string)($o['user_email'] ?? '')) ?>" disabled>
      </div>

      <form method="POST" action="/admin/orders/send">
        <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
        <input type="hidden" name="order_id" value="<?= (int)($o['id'] ?? 0) ?>">

        <div class="field">
          <label>Input product voucher / gift card</label>
          <textarea name="voucher_text" placeholder="Tempel voucher disini..."><?php
            if (!empty($del['voucher_text'])) echo ae((string)$del['voucher_text']);
          ?></textarea>
        </div>

        <button class="btn btnBlue" type="submit" style="width:100%;height:44px;">Kirim Voucher/Gift Card</button>

        <div style="margin-top:10px;color:var(--muted);font-size:12px;font-weight:600;line-height:1.4;">
          *input voucher/gift card ke kolom input, pastikan voucher valid sesuai dengan nominal yang dibeli pelanggan,
          lalu klik tombol "Kirim Voucher/Gift Card" agar pelanggan menerima voucher ke email.
        </div>
      </form>
    </div>
  </div>
</div>
