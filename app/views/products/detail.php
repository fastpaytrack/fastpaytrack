<?php
use function App\Lib\e;
use function App\Lib\money_idr;

$balanceIdr = (int) ($balance ?? 0);
$product = $product ?? [];
$denoms = $denoms ?? [];

$pid = (int) ($product['id'] ?? 0);
$name = (string) ($product['name'] ?? '');
$img = (string) ($product['image_url'] ?? '');
$cat = (string) ($product['category'] ?? '');
$desc = (string) ($product['description'] ?? '');
$tag = strtolower((string) ($product['tag'] ?? ''));
$isSale = (strpos($tag, 'sale') !== false) || (strpos($tag, 'promo') !== false) || (strpos($tag, 'hot') !== false);
?>

<style>
  .brandRow {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
    padding-right: 2px;
  }

  .profileTopBtn {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .28);
    background: rgba(255, 255, 255, .16);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    margin-left: auto;
    transform: translateX(6px);
  }

  .profileTopBtn:hover {
    background: rgba(255, 255, 255, .22);
  }

  .walletPillRow {
    margin-top: 10px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
  }

  .walletPill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    height: 44px;
    padding: 0 10px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .28);
    background: rgba(255, 255, 255, .14);
    color: #fff;
    backdrop-filter: blur(8px);
  }

  .walletIcon {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .18);
    border: 1px solid rgba(255, 255, 255, .22);
    display: grid;
    place-items: center;
    flex: 0 0 auto;
  }

  .walletText {
    display: flex;
    align-items: baseline;
    gap: 8px;
    white-space: nowrap;
  }

  .walletRp {
    font-size: 13px;
    font-weight: 800;
    opacity: .92;
  }

  .walletAmt {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: .2px;
    font-family: ui-monospace, Menlo, Consolas, monospace;
  }

  .walletEye {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .22);
    background: rgba(255, 255, 255, .16);
    display: grid;
    place-items: center;
    cursor: pointer;
    flex: 0 0 auto;
  }

  .walletEye:hover {
    background: rgba(255, 255, 255, .22);
  }

  .iconBar {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 10px;
    position: relative;
    z-index: 1;
  }

  .iconPill {
    width: 36px;
    height: 36px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .28);
    background: rgba(255, 255, 255, .16);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
  }

  .iconPill:hover {
    background: rgba(255, 255, 255, .22);
  }

  .detailWrap {
    display: grid;
    gap: 14px;
    grid-template-columns: 1fr;
  }

  @media(min-width: 960px) {
    .detailWrap {
      grid-template-columns: 1.2fr .8fr;
      align-items: start;
    }
  }

  .mediaCard {
    border: 1px solid var(--border);
    border-radius: 18px;
    background: #fff;
    overflow: hidden;
  }

  .mediaTop {
    position: relative;
    padding: 14px;
    background: linear-gradient(180deg, rgba(99, 102, 241, .14), rgba(255, 255, 255, 0));
    border-bottom: 1px solid var(--border);
  }

  .badge {
    position: absolute;
    top: 12px;
    left: 12px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 999px;
    background: #f59e0b;
    color: #fff;
    letter-spacing: .2px;
  }

  .mediaFrame {
    height: 220px;
    border-radius: 16px;
    background: rgba(255, 255, 255, .75);
    border: 1px solid rgba(226, 232, 240, .95);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  @media(min-width: 960px) {
    .mediaFrame {
      height: 320px;
    }
  }

  .mediaFrame img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transform: scale(.94);
  }

  .mediaBody {
    padding: 14px;
  }

  .pTitle {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
  }

  .pMeta {
    margin-top: 8px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    color: var(--muted);
    font-size: 13px;
    font-weight: 600;
    align-items: center;
  }

  .metaDot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #22c55e;
    display: inline-block;
  }

  .pDesc {
    margin-top: 10px;
    color: var(--muted);
    font-size: 13.5px;
    font-weight: 600;
    line-height: 1.5;
  }

  .buyCard {
    border: 1px solid var(--border);
    border-radius: 18px;
    background: #fff;
    padding: 14px;
  }

  .buyHead {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
  }

  .buyHead h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
  }

  .priceHint {
    font-weight: 700;
    color: #ef4444;
    font-size: 14px;
  }

  .field {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .label {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
  }

  .select {
    height: 44px;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: #fff;
    padding: 0 12px;
    font-weight: 600;
    font-size: 14px;
  }

  .btnPrimary {
    margin-top: 12px;
    width: 100%;
    height: 46px;
    border-radius: 14px;
    border: 1px solid #1d4ed8;
    background: #2563eb;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
  }

  .btnPrimary:hover {
    filter: brightness(.97);
  }

  .btnGhost {
    margin-top: 10px;
    width: 100%;
    height: 44px;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: #fff;
    color: #0f172a;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
  }

  .btnGhost:hover {
    background: #f8fafc;
  }

  .hint {
    margin-top: 10px;
    color: var(--muted);
    font-weight: 600;
    font-size: 12.5px;
    line-height: 1.45;
  }
</style>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brandRow">
        <div class="brand">
          <div class="logo">$</div>
          FASTPAYTRACK
        </div>

        <a class="profileTopBtn" href="/profile" title="Profil" aria-label="Profil">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M20 21a8 8 0 0 0-16 0" />
            <circle cx="12" cy="8" r="4" />
          </svg>
        </a>
      </div>
    </div>

    <div class="walletPillRow">
      <div class="walletPill">
        <div class="walletIcon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M3 7a3 3 0 0 1 3-3h12a2 2 0 0 1 2 2v2" />
            <path d="M3 7v12a3 3 0 0 0 3 3h14a1 1 0 0 0 1-1v-9a2 2 0 0 0-2-2H6a3 3 0 0 1-3-3Z" />
            <path d="M17 14h.01" />
          </svg>
        </div>

        <div class="walletText">
          <div class="walletRp">Rp</div>
          <div id="saldoValue" class="walletAmt"><?= e(number_format($balanceIdr, 0, ',', '.')) ?></div>
        </div>

        <button id="toggleSaldo" class="walletEye" type="button" aria-label="Hide/Show saldo" title="Hide/Show saldo">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </button>
      </div>
    </div>

    <div class="iconBar">
      <a class="iconPill" href="/products" title="Kembali" aria-label="Kembali">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M15 18l-6-6 6-6" />
          <path d="M9 12h12" />
        </svg>
      </a>

      <a class="iconPill" href="/checkout" title="Keranjang" aria-label="Keranjang">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M6 6h15l-2 9H7L6 6Z" />
          <path d="M6 6 5 3H2" />
          <path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
          <path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
        </svg>
      </a>

      <a class="iconPill" href="/logout" title="Logout" aria-label="Logout">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M10 17l5-5-5-5" />
          <path d="M15 12H3" />
          <path d="M21 4v16a2 2 0 0 1-2 2h-7" />
        </svg>
      </a>
    </div>

    <div class="heroTitle">Detail Produk</div>
    <p class="heroSub">Pilih nominal lalu tambah ke keranjang.</p>
  </div>

  <div class="card">
    <div class="panel">
      <div class="panelBody">
        <div class="detailWrap">

          <div class="mediaCard">
            <div class="mediaTop">
              <?php if ($isSale): ?>
                <div class="badge"><?= (strpos($tag, 'hot') !== false ? 'HOT' : 'SALE') ?></div><?php endif; ?>
              <div class="mediaFrame">
                <?php if ($img): ?>
                  <img src="<?= e($img) ?>" alt="<?= e($name) ?>">
                <?php else: ?>
                  <div style="font-weight:700;color:#64748b;">No Image</div>
                <?php endif; ?>
              </div>
            </div>
            <div class="mediaBody">
              <h1 class="pTitle"><?= e($name) ?></h1>

              <div class="pMeta">
                <span class="metaDot"></span>
                <span>Globally redeemable</span>
                <?php if ($cat !== ''): ?>
                  <span>•</span>
                  <span><?= e($cat) ?></span>
                <?php endif; ?>
              </div>

              <?php if ($desc !== ''): ?>
                <div class="pDesc"><?= e($desc) ?></div>
              <?php else: ?>
                <div class="pDesc">Voucher digital & layanan top up. Proses cepat dan mudah.</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="buyCard">
            <div class="buyHead">
              <h3>Pilih Nominal</h3>
              <div class="priceHint">
                <?php
                $min = !empty($denoms) ? min($denoms) : 0;
                echo $min > 0 ? e(money_idr($min)) : '';
                ?>
              </div>
            </div>

            <form method="POST" action="/cart/add">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="product_id" value="<?= (int) $pid ?>">

              <div class="field">
                <div class="label">Nominal</div>
                <select class="select" name="amount_idr" required>
                  <option value="">Pilih nominal</option>
                  <?php foreach ($denoms as $amt): ?>
                    <?php if ((int) $amt <= 0)
                      continue; ?>
                    <option value="<?= (int) $amt ?>"><?= e(money_idr((int) $amt)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button class="btnPrimary" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                  <path d="M6 6h15l-2 9H7L6 6Z" />
                  <path d="M6 6 5 3H2" />
                  <path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                  <path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                </svg>
                Tambah ke Keranjang
              </button>
            </form>

            <a class="btnGhost" href="/products">Kembali ke Katalog</a>

            <div class="hint">
              Tips: setelah masuk keranjang, kamu bisa checkout seperti biasa dari menu keranjang.
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const saldoVal = document.getElementById('saldoValue');
    const btn = document.getElementById('toggleSaldo');
    let hidden = false;
    const real = saldoVal ? saldoVal.textContent : '';
    function mask() { return '••••••'; }
    if (btn && saldoVal) {
      btn.addEventListener('click', () => {
        hidden = !hidden;
        saldoVal.textContent = hidden ? mask() : real;
      });
    }
  })();
</script>