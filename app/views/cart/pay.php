<?php
use function App\Lib\e;
use function App\Lib\money_idr;

$orderCode = (string) ($order['order_code'] ?? '');
$total = (int) ($order['total'] ?? 0);

$balanceIdr = (int) ($balance ?? 0);
$canPayBalance = $balanceIdr >= $total;
?>

<style>
  .payCard {
    border: 1px solid var(--border);
    border-radius: 18px;
    background: #fff;
    overflow: hidden;
  }

  .payCardHead {
    padding: 12px 12px 10px;
    border-bottom: 1px solid var(--border);
  }

  .payCardTitle {
    margin: 0;
    font-weight: 700;
    font-size: 14.5px;
  }

  .payCardSub {
    margin: 6px 0 0;
    color: var(--muted);
    font-weight: 600;
    font-size: 12.5px;
    line-height: 1.35;
  }

  .payBtns {
    padding: 12px;
    display: grid;
    gap: 10px;
  }

  .payBtn {
    height: 52px;
    border-radius: 16px;
    border: 1px solid rgba(15, 23, 42, .10);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 14px;
    transition: transform .05s, filter .15s;
  }

  .payBtn:active {
    transform: translateY(1px)
  }

  .payBtn:hover {
    filter: brightness(.95)
  }

  .payLeft {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
  }

  .payLogo {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .16);
    border: 1px solid rgba(255, 255, 255, .25);
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    overflow: hidden;
  }

  .payLogo img {
    width: 22px;
    height: 22px;
    object-fit: contain;
    display: block;
    filter: brightness(0) invert(1);
  }

  .payText {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
  }

  .payName {
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .payHint {
    font-weight: 600;
    font-size: 12px;
    opacity: .92;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .payArrow {
    font-weight: 700;
    font-size: 18px;
    opacity: .95;
  }

  .payNote {
    padding: 0 12px 12px;
    color: var(--muted);
    font-weight: 600;
    font-size: 12.5px;
    line-height: 1.4;
  }

  /* brand colors */
  .payStripe {
    background: #635BFF;
    border-color: rgba(99, 91, 255, .35);
    box-shadow: 0 12px 22px rgba(99, 91, 255, .22);
  }

  .payPaypal {
    background: #003087;
    border-color: rgba(0, 48, 135, .35);
    box-shadow: 0 12px 22px rgba(0, 48, 135, .22);
  }

  .payQris {
    background: var(--primary);
    border-color: rgba(79, 70, 229, .35);
    box-shadow: 0 12px 22px rgba(79, 70, 229, .18);
  }

  /* wallet button */
  .payWallet {
    background: #10b981;
    border-color: rgba(16, 185, 129, .35);
    box-shadow: 0 12px 22px rgba(16, 185, 129, .20);
  }

  .payWalletDisabled {
    background: #94a3b8;
    border-color: #94a3b8;
    box-shadow: none;
    cursor: not-allowed;
    filter: none;
  }

  .payWalletDisabled:hover {
    filter: none;
  }

  @media(max-width:520px) {
    .payBtn {
      height: 56px;
    }

    .payHint {
      white-space: normal;
    }
  }
</style>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brand">
        <div class="logo">A</div>FastPayTrack
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="pill" href="/orders">Orders</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>

    <div class="heroTitle">Pilih Metode Pembayaran</div>
    <div style="color:rgba(255,255,255,.92);font-weight:600;font-size:13px;margin-top:6px;line-height:1.35;">
      Order <span class="mono"><?= e($orderCode) ?></span> • Total <b><?= e(money_idr($total)) ?></b><br>
      Saldo kamu: <span class="mono"><?= e(money_idr($balanceIdr)) ?></span>
    </div>
  </div>

  <div class="card">
    <div class="payCard">
      <div class="payCardHead">
        <p class="payCardTitle">Payment Gateway</p>
        <p class="payCardSub">Pilih salah satu metode di bawah</p>
      </div>

      <div class="payBtns">

        <!-- ✅ Wallet -->
        <?php if ($canPayBalance): ?>
          <form method="POST" action="/pay/balance" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="order" value="<?= e($orderCode) ?>">
            <button class="payBtn payWallet" type="submit" style="width:100%;background:#10b981;">
              <div class="payLeft">
                <div class="payLogo" aria-hidden="true" style="background:rgba(255,255,255,.18);">
                  <!-- wallet icon -->
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3 7a3 3 0 0 1 3-3h12a2 2 0 0 1 2 2v2" />
                    <path d="M3 7v12a3 3 0 0 0 3 3h14a1 1 0 0 0 1-1v-9a2 2 0 0 0-2-2H6a3 3 0 0 1-3-3Z" />
                    <path d="M17 14h.01" />
                  </svg>
                </div>
                <div class="payText">
                  <div class="payName">Saldo Wallet</div>
                  <div class="payHint">Bayar langsung dari saldo</div>
                </div>
              </div>
              <div class="payArrow">›</div>
            </button>
          </form>
        <?php else: ?>
          <div class="payBtn payWalletDisabled" style="justify-content:center;">
            Saldo tidak cukup untuk bayar (Topup dulu)
          </div>
        <?php endif; ?>

        <!-- Stripe -->
        <a class="payBtn payStripe" href="/pay/stripe?order=<?= e($orderCode) ?>" aria-label="Bayar dengan Stripe">
          <div class="payLeft">
            <div class="payLogo" aria-hidden="true">
              <img alt="Stripe"
                src="https://upload.wikimedia.org/wikipedia/commons/3/3f/Stripe_Logo%2C_revised_2016.svg">
            </div>
            <div class="payText">
              <div class="payName">Stripe</div>
              <div class="payHint">Kartu debit/kredit (TEST)</div>
            </div>
          </div>
          <div class="payArrow">›</div>
        </a>

        <!-- PayPal -->
        <a class="payBtn payPaypal" href="/pay/paypal?order=<?= e($orderCode) ?>" aria-label="Bayar dengan PayPal">
          <div class="payLeft">
            <div class="payLogo" aria-hidden="true">
              <img alt="PayPal" src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg">
            </div>
            <div class="payText">
              <div class="payName">PayPal</div>
              <div class="payHint">Login PayPal (SANDBOX)</div>
            </div>
          </div>
          <div class="payArrow">›</div>
        </a>

        <!-- QRIS -->
        <a class="payBtn payQris" href="/pay/qris?order=<?= e($orderCode) ?>" aria-label="Bayar dengan QRIS">
          <div class="payLeft">
            <div class="payLogo" aria-hidden="true">
              <img alt="Midtrans" src="https://upload.wikimedia.org/wikipedia/commons/7/72/Midtrans_logo.svg">
            </div>
            <div class="payText">
              <div class="payName">QRIS</div>
              <div class="payHint">QRIS via Midtrans (SANDBOX)</div>
            </div>
          </div>
          <div class="payArrow">›</div>
        </a>

      </div>

      <div class="payNote">
        Setelah payment sukses, status order otomatis berubah menjadi <span class="mono">PAID</span> dan email invoice
        dikirim.
      </div>
    </div>
  </div>
</div>