<?php
use function App\Lib\e;
use function App\Lib\money_idr;

$cart = $_SESSION['cart'] ?? [];
$cartCount = 0;
foreach ($cart as $it)
  $cartCount += (int) ($it['qty'] ?? 1);

$balanceIdr = (int) ($balance ?? 0);
$catalogs = $catalogs ?? [];
$products = $products ?? [];
$denByProduct = $denByProduct ?? [];
$ads = $ads ?? [];

/**
 * ✅ Ganti path logo kamu di sini
 */
$brandLogo = '/asset/brand/fastpaytrack-logo2.png';
?>

<style>
  /* =========================================================
     DASHBOARD THEME (Scoped)
     ========================================================= */
  body.page-dashboard {
    padding: 0 !important;
    margin: 0 !important;
    background:
      radial-gradient(900px 520px at 12% 12%, rgba(99, 102, 241, .18), transparent 60%),
      radial-gradient(900px 520px at 88% 18%, rgba(34, 211, 238, .18), transparent 60%),
      radial-gradient(900px 520px at 80% 90%, rgba(147, 51, 234, .10), transparent 55%),
      #ffffff !important;
    color: #0b1220;
    min-height: 100vh;
  }

  /* ✅ FULL SCREEN: hilangkan ruang kosong tepi atas/bawah/kiri/kanan untuk DASHBOARD SAJA */
  body.page-dashboard .wrap {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  body.page-dashboard .dashContainer {
    width: 100%;
    margin: 0;
  }

  body.page-dashboard .shell {
    border-radius: 0 !important;
    /* full screen */
    overflow: hidden;
    background: rgba(255, 255, 255, .70);
    backdrop-filter: blur(10px);
    border: 0 !important;
    /* supaya tidak ada garis tepi luar */
    box-shadow: none !important;
    /* full screen tanpa “kartu” luar */
  }

  body.page-dashboard .hero {
    position: relative;
    color: #fff;
    padding: 20px 20px 18px;
    background:
      radial-gradient(900px 420px at 12% 18%, rgba(37, 99, 235, .20), transparent 60%),
      radial-gradient(760px 420px at 92% 18%, rgba(34, 211, 238, .18), transparent 60%),
      linear-gradient(135deg, var(--bg1), var(--bg2));
  }

  body.page-dashboard .hero:before {
    content: "";
    position: absolute;
    inset: -60px -60px auto auto;
    width: 260px;
    height: 260px;
    background: rgba(255, 255, 255, .16);
    border-radius: 50%;
  }

  body.page-dashboard .card {
    background: rgba(255, 255, 255, .70);
    padding: 14px 16px 18px;
    /* sedikit dirapikan biar tidak longgar */
    border-radius: 22px 22px 0 0;
  }

  /* ✅ Panel clean supaya tidak ada layer/garis bertumpuk */
  .panelClean {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    overflow: visible !important;
    border-radius: 0 !important;
  }

  .panelClean .panelBody {
    padding: 0 !important;
  }

  /* =========================================================
     TOP ROW
     ========================================================= */
  .brandRow {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
  }

  .dashBrand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #fff;
    min-width: 0;
  }

  .dashBrand img {
    height: 28px;
    width: auto;
    display: block;
    filter: drop-shadow(0 10px 18px rgba(2, 6, 23, .18));
  }

  .settingsTopBtn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    border: none;
    background: transparent;
    color: #fff;
    text-decoration: none;
    margin-left: auto;
    opacity: .95;
  }

  .settingsTopBtn:hover {
    opacity: 1;
  }

  .settingsTopBtn svg {
    width: 20px;
    height: 20px;
    display: block;
  }

  /* =========================================================
     WALLET PILL
     ========================================================= */
  .walletPillRow {
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
  }

  .walletPill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-height: 46px;
    padding: 0 12px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .28);
    background: rgba(255, 255, 255, .14);
    color: #fff;
    backdrop-filter: blur(8px);
  }

  .walletIcon {
    display: grid;
    place-items: center;
  }

  .walletIcon svg {
    width: 18px;
    height: 18px;
    display: block;
  }

  .walletText {
    display: flex;
    align-items: baseline;
    gap: 8px;
    white-space: nowrap;
  }

  .walletRp {
    font-size: 13px;
    opacity: .92;
  }

  .walletAmt {
    font-size: 22px;
    letter-spacing: .2px;
    font-family: ui-monospace, Menlo, Consolas, monospace;
    font-weight: 600 !important;
  }

  .walletEye {
    border: none;
    background: transparent;
    display: grid;
    place-items: center;
    cursor: pointer;
    color: #fff;
    opacity: .92;
    padding: 2px;
  }

  .walletEye:hover {
    opacity: 1;
  }

  .walletEye svg {
    width: 18px;
    height: 18px;
    display: block;
  }

  @media (max-width: 520px) {
    .walletPill {
      min-height: 40px;
      padding: 0 10px;
      gap: 8px;
    }

    .walletRp {
      font-size: 12.5px;
    }

    .walletAmt {
      font-size: 18px;
    }

    .walletIcon svg {
      width: 16px;
      height: 16px;
    }

    .walletEye svg {
      width: 16px;
      height: 16px;
    }
  }

  /* =========================================================
     ICON BAR
     ========================================================= */
  .iconBar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 12px;
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
  }

  .iconItem {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #fff;
    min-width: 64px;
  }

  .iconPill {
    width: 46px;
    height: 46px;
    border-radius: 14px;
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

  .iconPill svg {
    width: 22px;
    height: 22px;
  }

  .iconLabel {
    font-size: 12.5px;
    font-weight: 500 !important;
    opacity: .92;
    line-height: 1.1;
    text-align: center;
    user-select: none;
  }

  @media (max-width: 520px) {
    .iconBar {
      flex-wrap: nowrap;
      justify-content: space-between;
      gap: 0;
      margin-top: 10px;
    }

    .iconItem {
      min-width: 0;
      width: 20%;
    }

    .iconPill {
      width: 44px;
      height: 44px;
      border-radius: 14px;
    }

    .iconPill svg {
      width: 21px;
      height: 21px;
    }

    .iconLabel {
      font-size: 12px;
    }
  }

  .cartBadge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 600 !important;
    border-radius: 999px;
    padding: 2px 6px;
    line-height: 1;
    box-shadow: 0 10px 18px rgba(239, 68, 68, .28);
  }

  /* =========================================================
     KATALOG
     ========================================================= */
  .catCard {
    border: 1px solid var(--border);
    border-radius: 20px;
    background: #fff;
    padding: 14px;
    margin-bottom: 10px;
    /* rapatkan */
    box-shadow: 0 10px 26px rgba(2, 6, 23, .04);
  }

  .catGrid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px 10px;
  }

  .catItem {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 8px 6px;
    border-radius: 14px;
    transition: transform .12s ease, background .12s ease;
    user-select: none;
  }

  .catItem:hover {
    background: #f8fafc;
    transform: translateY(-1px);
  }

  .catIcon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    border: 1px solid #e2e8f0;
    background: #fff;
    overflow: hidden;
  }

  .catIcon img {
    width: 30px;
    height: 30px;
    object-fit: contain;
    display: block;
  }

  .catFallback {
    font-weight: 600 !important;
    font-size: 14px;
    color: #475569;
  }

  .catLabel {
    text-align: center;
    font-weight: 500 !important;
    font-size: 12.5px;
    line-height: 1.15;
    color: #0f172a;
  }

  /* =========================================================
     ✅ ADS / BANNER (TRANSPARAN + FULL memenuhi card, tanpa dots)
     Posisi: di bawah katalog, di atas search
     ========================================================= */
  .adsWrap {
    margin: 8px 0 10px;
    /* rapatkan jarak */
  }

  .adsCard {
    background: transparent !important;
    /* ✅ transparan */
    border: none !important;
    /* ✅ hilangkan garis */
    box-shadow: none !important;
    /* ✅ tidak ada tepi */
    padding: 0 !important;
  }

  .adsViewport {
    width: 100%;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255, 255, 255, .55);
    /* nyatu, tapi tetap ada “soft layer” */
    border: 1px solid rgba(226, 232, 240, .55);
    /* sangat halus (nyaris tak terlihat) */
    box-shadow: 0 10px 26px rgba(2, 6, 23, .04);
  }

  /* Rasio seperti referensi #2: banner lebar & pendek */
  .adsStage {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 7;
    /* ✅ mirip banner “mini games” */
    background: transparent;
  }

  .adsSlide {
    position: absolute;
    inset: 0;
    opacity: 0;
    pointer-events: none;
    transform: translateX(10px);
    transition: opacity .28s ease, transform .28s ease;
  }

  .adsSlide.isActive {
    opacity: 1;
    pointer-events: auto;
    transform: none;
  }

  .adsLink {
    position: absolute;
    inset: 0;
    display: block;
    text-decoration: none;
  }

  .adsMedia {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
  }

  .adsMedia img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* ✅ full memenuhi card */
    display: block;
  }

  /* dotlottie-wc full */
  .adsMedia dotlottie-wc {
    width: 100% !important;
    height: 100% !important;
    display: block;
  }

  @media (max-width: 520px) {
    .adsViewport {
      border-radius: 18px;
    }

    .adsStage {
      border-radius: 18px;
    }
  }

  /* =========================================================
     SEARCH
     ========================================================= */
  .dashSearchRow {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    margin: 6px 0 10px;
    /* rapatkan */
  }

  .dashSearchWrap {
    flex: 1;
    min-width: 240px;
    position: relative;
  }

  .dashSearchInput {
    width: 100%;
    height: 46px;
    border-radius: 16px;
    border: 1px solid var(--border);
    padding: 0 44px 0 14px;
    font-size: 14px;
    background: #fff;
    outline: none;
    font-weight: 500 !important;
  }

  .dashSearchInput:focus {
    border-color: rgba(79, 70, 229, .55);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, .12);
  }

  .dashSearchBtn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 32px;
    height: 32px;
    border-radius: 12px;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    color: #475569;
    font-weight: 600 !important;
  }

  .dashSearchBtn:hover {
    background: #f1f5f9;
    border-color: #e2e8f0;
  }

  .dashCount {
    color: var(--muted);
    font-size: 12.5px;
  }

  /* =========================================================
     PRODUK SLIDER
     ========================================================= */
  .pCard {
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 14px;
    background: #fff;
  }

  .pTop {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
  }

  .pTitle {
    font-size: 16px;
    margin: 0;
    font-weight: 600 !important;
  }

  .pCat {
    color: var(--muted);
    font-size: 12.5px;
    margin-top: 6px;
  }

  .pDesc {
    color: var(--muted);
    font-size: 12.5px;
    margin-top: 10px;
    line-height: 1.35;
  }

  .pThumb {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: #fff;
    display: grid;
    place-items: center;
    overflow: hidden;
    flex: 0 0 auto;
  }

  .pThumb img {
    width: 44px;
    height: 44px;
    object-fit: contain;
  }

  .pFormRow {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    margin-top: 14px;
  }

  .pSelect {
    flex: 1;
    min-width: 180px;
    height: 44px;
    border-radius: 16px;
    font-weight: 500 !important;
  }

  .pBtn {
    height: 44px;
    border-radius: 16px;
    width: auto;
    padding: 0 16px;
  }

  @media(max-width:520px) {
    .dashCount {
      width: 100%;
    }

    .pFormRow {
      flex-direction: column;
      align-items: stretch;
    }

    .pSelect,
    .pBtn {
      width: 100%;
    }
  }

  .productSlider {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x mandatory;
    padding-bottom: 6px;
  }

  .productSlider::-webkit-scrollbar {
    height: 8px;
  }

  .productSlider::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, .45);
    border-radius: 999px;
  }

  .productSlider::-webkit-scrollbar-track {
    background: transparent;
  }

  .productTrack {
    display: flex;
    gap: 12px;
    align-items: stretch;
    min-width: 100%;
  }

  .productTrack .pCard {
    flex: 0 0 auto;
    width: 82%;
    max-width: 420px;
    scroll-snap-align: start;
  }

  @media(min-width:520px) {
    .productTrack .pCard {
      width: 320px;
    }
  }

  @media(min-width:980px) {
    .productTrack .pCard {
      width: 340px;
    }
  }
</style>

<script>
  (function () {
    try { document.body.classList.add('page-dashboard'); } catch (e) { }
  })();
</script>

<!-- ✅ Lottie dotlottie web component (buat banner type lottie) -->
<script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>

<div class="dashContainer">
  <div class="shell">
    <div class="hero">
      <div class="topbar">
        <div class="brandRow">
          <a class="dashBrand" href="/" aria-label="FastPayTrack Home">
            <img src="<?= e($brandLogo) ?>" alt="FASTPAYTRACK">
          </a>

          <a class="settingsTopBtn" href="/settings" title="Settings" aria-label="Settings">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="12" cy="12" r="3"></circle>
              <path
                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
              </path>
            </svg>
          </a>
        </div>
      </div>

      <div class="walletPillRow">
        <div class="walletPill">
          <div class="walletIcon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </button>
        </div>
      </div>

      <div class="iconBar" aria-label="Quick actions">
        <a class="iconItem" href="/logout" aria-label="Logout" title="Logout">
          <span class="iconPill" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M10 17l5-5-5-5" />
              <path d="M15 12H3" />
              <path d="M21 4v16a2 2 0 0 1-2 2h-7" />
            </svg>
          </span>
          <span class="iconLabel">Logout</span>
        </a>

        <a class="iconItem" href="/checkout" aria-label="Cart" title="Cart" style="position:relative;">
          <span class="iconPill" aria-hidden="true" style="position:relative;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M6 6h15l-2 9H7L6 6Z" />
              <path d="M6 6 5 3H2" />
              <path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
              <path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
            </svg>
            <?php if ($cartCount > 0): ?><span class="cartBadge"><?= (int) $cartCount ?></span><?php endif; ?>
          </span>
          <span class="iconLabel">Cart</span>
        </a>

        <a class="iconItem" href="/wallet/transfer" aria-label="Send" title="Send">
          <span class="iconPill" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M22 2L11 13"></path>
              <path d="M22 2l-7 20-4-9-9-4 20-7z"></path>
            </svg>
          </span>
          <span class="iconLabel">Send</span>
        </a>

        <a class="iconItem" href="/topup" aria-label="Topup" title="Topup">
          <span class="iconPill" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="9" />
              <path d="M12 8v8M8 12h8" />
            </svg>
          </span>
          <span class="iconLabel">Topup</span>
        </a>

        <a class="iconItem" href="/orders" aria-label="Orders" title="Orders">
          <span class="iconPill" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M9 6h10M9 10h10M9 14h6" />
              <path d="M6.5 6.5h.01M6.5 10.5h.01M6.5 14.5h.01" />
              <path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" />
            </svg>
          </span>
          <span class="iconLabel">Orders</span>
        </a>
      </div>
    </div>

    <div class="card">
      <div class="panelClean">
        <div class="panelBody">

          <?php if (!empty($catalogs)): ?>
            <div class="catCard">
              <div class="catGrid">
                <?php foreach ($catalogs as $c):
                  $title = (string) ($c['title'] ?? '');
                  $icon = (string) ($c['icon_url'] ?? '');
                  $link = (string) ($c['link_url'] ?? '');
                  $href = $link ? $link : '#';
                  $fallback = strtoupper(mb_substr(trim($title), 0, 1));
                  ?>
                  <a class="catItem" href="<?= e($href) ?>" aria-label="<?= e($title) ?>">
                    <div class="catIcon">
                      <?php if ($icon): ?>
                        <img src="<?= e($icon) ?>" alt="<?= e($title) ?>">
                      <?php else: ?>
                        <div class="catFallback"><?= e($fallback) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="catLabel"><?= e($title) ?></div>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- ✅ ADS: di bawah katalog, di atas search -->
          <?php if (!empty($ads)): ?>
            <div class="adsWrap">
              <div class="adsCard">
                <div class="adsViewport" id="adsViewport">
                  <div class="adsStage" id="adsStage" aria-label="Banner iklan">
                    <?php foreach ($ads as $i => $ad):
                      $type = strtolower(trim((string) ($ad['media_type'] ?? 'image')));
                      $url = (string) ($ad['media_url'] ?? '');
                      $click = (string) ($ad['click_url'] ?? '');
                      $title = (string) ($ad['title'] ?? ('Banner ' . ($i + 1)));

                      if (!$url)
                        continue;

                      $isActive = ($i === 0);
                      $href = $click ? $click : '#';
                      $isClickable = (bool) $click;
                      ?>
                      <div class="adsSlide <?= $isActive ? 'isActive' : '' ?>" data-idx="<?= (int) $i ?>">
                        <?php if ($isClickable): ?>
                          <a class="adsLink" href="<?= e($href) ?>" aria-label="<?= e($title) ?>">
                          <?php else: ?>
                            <a class="adsLink" href="#" aria-label="<?= e($title) ?>" data-no-click="1">
                            <?php endif; ?>

                            <div class="adsMedia" aria-hidden="true">
                              <?php if ($type === 'lottie'): ?>
                                <dotlottie-wc src="<?= e($url) ?>" autoplay loop
                                  style="width:100%;height:100%;"></dotlottie-wc>
                              <?php else: ?>
                                <img src="<?= e($url) ?>" alt="<?= e($title) ?>">
                              <?php endif; ?>
                            </div>

                          </a>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="dashSearchRow">
            <div class="dashSearchWrap">
              <input id="productSearch" class="dashSearchInput" type="text"
                placeholder="Voucher & Gift Card: Steam, Google Play, DANA, Netflix..." />
              <button id="clearSearch" class="dashSearchBtn" type="button" aria-label="Clear">✕</button>
            </div>
            <div class="dashCount" id="searchCount">Menampilkan semua</div>
          </div>

          <div id="productGrid" class="productSlider" aria-label="Daftar Produk (geser kiri/kanan)">
            <div class="productTrack">
              <?php foreach ($products as $p):
                $pid = (int) $p['id'];
                $denoms = $denByProduct[$pid] ?? [];
                $img = $p['image_url'] ?: '';
                $dataSearch = strtolower(($p['name'] ?? '') . ' ' . ($p['category'] ?? '') . ' ' . ($p['description'] ?? ''));
                ?>
                <div class="pCard productCard" data-search="<?= e($dataSearch) ?>">
                  <div class="pTop">
                    <div style="min-width:0;">
                      <p class="pTitle"><?= e($p['name']) ?></p>
                      <div class="pCat"><?= e($p['category']) ?></div>
                      <?php if (!empty($p['description'])): ?>
                        <div class="pDesc"><?= e((string) $p['description']) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="pThumb">
                      <?php if ($img): ?>
                        <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>">
                      <?php else: ?>
                        <div class="mono">V</div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <form method="POST" action="/cart/add">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="product_id" value="<?= (int) $pid ?>">
                    <div class="pFormRow">
                      <select name="amount_idr" class="pSelect" required>
                        <option value="">Pilih nominal</option>
                        <?php foreach ($denoms as $amt): ?>
                          <option value="<?= (int) $amt ?>"><?= e(money_idr((int) $amt)) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btnPrimary pBtn" type="submit">Tambah</button>
                    </div>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div id="noResult" class="note" style="display:none;margin-top:12px;font-weight:500;">
            Tidak ada produk yang cocok.
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

<script>
  (function () {
    // saldo hide/show
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

    // search filter
    const input = document.getElementById('productSearch');
    const clearBtn = document.getElementById('clearSearch');
    const cards = Array.from(document.querySelectorAll('.productCard'));
    const countEl = document.getElementById('searchCount');
    const noResult = document.getElementById('noResult');

    function update() {
      const q = (input && input.value ? input.value : '').trim().toLowerCase();
      let shown = 0;

      cards.forEach(card => {
        const hay = (card.getAttribute('data-search') || '').toLowerCase();
        const ok = !q || hay.includes(q);
        card.style.display = ok ? '' : 'none';
        if (ok) shown++;
      });

      if (countEl) countEl.textContent = q ? ('Hasil: ' + shown) : 'Menampilkan semua';
      if (noResult) noResult.style.display = (q && shown === 0) ? '' : 'none';
    }

    if (input) input.addEventListener('input', update);
    if (clearBtn) clearBtn.addEventListener('click', () => { input.value = ''; input.focus(); update(); });
    update();

    // horizontal wheel -> scroll
    const slider = document.getElementById('productGrid');
    if (slider) {
      slider.addEventListener('wheel', (e) => {
        if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
          slider.scrollLeft += e.deltaY;
          e.preventDefault();
        }
      }, { passive: false });
    }

    // disable catalog dummy
    document.querySelectorAll('.catItem[href="#"]').forEach(a => {
      a.addEventListener('click', (e) => e.preventDefault());
    });

    // ✅ Ads slider (tanpa dots)
    const stage = document.getElementById('adsStage');
    if (stage) {
      const slides = Array.from(stage.querySelectorAll('.adsSlide'));
      if (slides.length > 1) {
        let idx = 0;
        const intervalMs = 4500;

        function show(i) {
          slides.forEach((s, n) => s.classList.toggle('isActive', n === i));
        }

        // Prevent click if no click_url
        stage.querySelectorAll('a[data-no-click="1"]').forEach(a => {
          a.addEventListener('click', (e) => e.preventDefault());
        });

        setInterval(() => {
          idx = (idx + 1) % slides.length;
          show(idx);
        }, intervalMs);

        show(idx);
      } else {
        // single slide: still prevent click if no click_url
        stage.querySelectorAll('a[data-no-click="1"]').forEach(a => {
          a.addEventListener('click', (e) => e.preventDefault());
        });
      }
    }
  })();
</script>