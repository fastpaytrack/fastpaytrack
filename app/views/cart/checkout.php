<?php
use function App\Lib\e;
use function App\Lib\money_idr;

?>
<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brand">
        <div class="logo">A</div>FastPayTrack
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="pill" href="/dashboard">Dashboard</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>
    <div class="heroTitle">Checkout</div>
    <p class="heroSub">Periksa item, isi email penerima, lalu buat order.</p>
  </div>

  <div class="card">
    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
      <div class="panel">
        <div class="panelHead">
          <p class="panelTitle">Keranjang</p>
          <p class="panelSub">Ubah qty / hapus item</p>
        </div>
        <div class="panelBody">

          <?php if (!$items): ?>
            <div class="note">Keranjang kosong. <a class="link" href="/dashboard">Kembali ke dashboard</a></div>
          <?php else: ?>
            <?php foreach ($items as $it): ?>
              <div
                style="border:1px solid var(--border);border-radius:16px;padding:12px;background:#fff;margin-bottom:10px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <div
                  style="width:52px;height:52px;border-radius:16px;border:1px solid var(--border);background:#fff;display:grid;place-items:center;overflow:hidden;">
                  <?php if ($it['image_url']): ?>
                    <img src="<?= e($it['image_url']) ?>" style="width:44px;height:44px;object-fit:contain" alt="">
                  <?php else: ?>
                    <div class="mono">V</div>
                  <?php endif; ?>
                </div>
                <div style="flex:1;min-width:220px;">
                  <div style="font-weight:700;"><?= e($it['name']) ?></div>
                  <div class="note"><?= e($it['category']) ?> • Nominal <?= e(money_idr((int) $it['amount_idr'])) ?></div>
                </div>
                <div style="text-align:right;min-width:220px;">
                  <div style="font-weight:700;"><?= e(money_idr((int) $it['line_total'])) ?></div>

                  <div style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;margin-top:8px;">
                    <form method="POST" action="/cart/update" style="display:flex;gap:8px;align-items:center;">
                      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                      <input type="hidden" name="key" value="<?= e($it['key']) ?>">
                      <input type="number" name="qty" min="1" value="<?= (int) $it['qty'] ?>"
                        style="width:90px;height:38px;border-radius:12px;">
                      <button class="btn btnGhost" type="submit" style="width:auto;height:38px;">Update</button>
                    </form>

                    <form method="POST" action="/cart/remove">
                      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                      <input type="hidden" name="key" value="<?= e($it['key']) ?>">
                      <button class="btn btnGhost" type="submit" style="width:auto;height:38px;">Hapus</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>

      <div class="panel">
        <div class="panelHead">
          <p class="panelTitle">Detail & Ringkasan</p>
          <p class="panelSub">Order akan tersimpan di database</p>
        </div>
        <div class="panelBody">
          <form method="POST" action="/checkout">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

            <div class="row2">
              <div class="field">
                <label>Email Pembeli</label>
                <input name="buyer_email" type="email" placeholder="nama@email.com" required>
              </div>
              <div class="field">
                <label>Email Penerima</label>
                <input name="receiver_email" type="email" placeholder="penerima@email.com" required>
              </div>
            </div>

            <div class="row2">
              <div class="field">
                <label>No WhatsApp (opsional)</label>
                <input name="phone" placeholder="08xxxxxxxxxx">
              </div>
              <div class="field">
                <label>Catatan (opsional)</label>
                <input name="note" placeholder="Pesan untuk penerima...">
              </div>
            </div>

            <div
              style="border:1px solid var(--border);border-radius:16px;padding:12px;background:#fff;margin-top:10px;">
              <div style="display:flex;justify-content:space-between;font-weight:700;margin:6px 0;">
                <div>Subtotal</div>
                <div><?= e(money_idr((int) $subtotal)) ?></div>
              </div>
              <div style="display:flex;justify-content:space-between;font-weight:700;margin:6px 0;">
                <div>Biaya layanan</div>
                <div><?= e(money_idr((int) $fee)) ?></div>
              </div>
              <div style="display:flex;justify-content:space-between;font-weight:700;margin:6px 0;">
                <div>Diskon</div>
                <div>- <?= e(money_idr((int) $discount)) ?></div>
              </div>
              <div style="height:1px;background:var(--border);margin:10px 0;"></div>
              <div style="display:flex;justify-content:space-between;font-weight:700;font-size:16px;margin:6px 0;">
                <div>Total</div>
                <div><?= e(money_idr((int) $total)) ?></div>
              </div>
            </div>

            <button class="btn btnPrimary" type="submit" style="margin-top:12px;">Buat Order</button>
            <div class="note" style="margin-top:10px;">
              Setelah order dibuat, kita akan lanjut integrasi payment (Stripe/PayPal/QRIS).
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>