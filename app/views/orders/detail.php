<?php
use function App\Lib\e;
use function App\Lib\money_idr;
use App\Lib\CSRF;

$orderCode = (string) ($order['order_code'] ?? '');
$status = strtoupper(trim((string) ($order['status'] ?? '')));
$provider = (string) ($order['payment_provider'] ?? '');
$csrf = CSRF::token();
?>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brand">
        <div class="logo">A</div>FastPayTrack
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="pill" href="/orders">Orders</a>
        <a class="pill" href="/dashboard">Dashboard</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>
    <div class="heroTitle">Detail Order</div>
    <p class="heroSub">Lihat item & status pembayaran order kamu.</p>
  </div>

  <div class="card">
    <div style="display:grid;grid-template-columns:1fr;gap:12px;">

      <!-- Info Order -->
      <div class="panel">
        <div class="panelHead">
          <p class="panelTitle">Order <span class="mono"><?= e($orderCode) ?></span></p>
          <p class="panelSub">
            Status: <span class="mono"><?= e($status) ?></span>
            <?php if ($provider): ?>
              • Provider: <span class="mono"><?= e($provider) ?></span>
            <?php endif; ?>
          </p>
        </div>

        <div class="panelBody">
          <div class="row2">
            <div class="field">
              <label>Email Pembeli</label>
              <input value="<?= e((string) $order['buyer_email']) ?>" disabled>
            </div>
            <div class="field">
              <label>Email Penerima</label>
              <input value="<?= e((string) $order['receiver_email']) ?>" disabled>
            </div>
          </div>

          <div class="row2">
            <div class="field">
              <label>No. WhatsApp</label>
              <input value="<?= e((string) ($order['phone'] ?? '-')) ?>" disabled>
            </div>
            <div class="field">
              <label>Tanggal</label>
              <input value="<?= e((string) ($order['created_at'] ?? '')) ?>" disabled>
            </div>
          </div>

          <!-- Tombol aksi (ukuran sama + responsif) -->
          <div class="actionsRow">
            <a class="actionBtn actionBtnGhost" href="/invoice?code=<?= e($orderCode) ?>">
              Invoice
            </a>

            <?php if ($status === 'PENDING'): ?>
              <a class="actionBtn actionBtnPrimary" href="/pay?order=<?= e($orderCode) ?>">
                Bayar Sekarang
              </a>
            <?php else: ?>
              <form method="POST" action="/invoice/resend" style="margin:0;width:auto;">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="code" value="<?= e($orderCode) ?>">
                <button class="actionBtn actionBtnPrimary" type="submit">
                  Resend Invoice Email
                </button>
              </form>
            <?php endif; ?>
          </div>

          <?php if ($status !== 'PENDING'): ?>
            <div style="margin-top:10px;font-weight:700;color:var(--success);">
              Pembayaran sukses ✅ Email konfirmasi sudah dikirim.
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Items -->
      <div class="panel">
        <div class="panelHead">
          <p class="panelTitle">Item Order</p>
          <p class="panelSub">Daftar voucher yang dibeli</p>
        </div>

        <div class="panelBody">
          <?php if (empty($items)): ?>
            <div class="note">Tidak ada item.</div>
          <?php else: ?>
            <?php foreach ($items as $it): ?>
              <div
                style="border:1px solid var(--border);border-radius:16px;padding:12px;background:#fff;margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                  <div style="min-width:220px;">
                    <div style="font-weight:700;"><?= e((string) $it['product_name']) ?></div>
                    <div class="note">
                      Nominal: <?= e(money_idr((int) $it['amount_idr'])) ?> • Qty: <?= (int) $it['qty'] ?>
                    </div>
                  </div>
                  <div style="text-align:right;font-weight:700;">
                    <?= e(money_idr((int) $it['line_total'])) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <div style="border:1px solid var(--border);border-radius:16px;padding:12px;background:#fff;margin-top:12px;">
            <div style="display:flex;justify-content:space-between;font-weight:700;margin:6px 0;">
              <div>Subtotal</div>
              <div><?= e(money_idr((int) $order['subtotal'])) ?></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;margin:6px 0;">
              <div>Biaya layanan</div>
              <div><?= e(money_idr((int) $order['fee'])) ?></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;margin:6px 0;">
              <div>Diskon</div>
              <div>- <?= e(money_idr((int) $order['discount'])) ?></div>
            </div>
            <div style="height:1px;background:var(--border);margin:10px 0;"></div>
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:16px;">
              <div>Total</div>
              <div><?= e(money_idr((int) $order['total'])) ?></div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>