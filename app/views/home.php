<?php
// app/views/home.php

// GANTI INI sesuai lokasi logo kamu di folder public
$brandLogo = '/asset/brand/fastpaytrack-logo.png';
?>
<style>
  /* ===== REAL SCROLL CONTAINER ===== */
  html.fptHomeScroll,
  body.fptHomeBody {
    height: 100%;
    overflow-y: auto;
    scroll-behavior: smooth;
  }

  @media (min-width: 900px) {
    html.fptHomeScroll {
      scroll-snap-type: y mandatory;
    }

    .fptHome-snap {
      scroll-snap-align: start;
    }
  }

  /* ===========================
   HOMEPAGE ONLY (SCOPED)
   =========================== */



  body.fptHomeBody {
    margin: 0;
    padding: 0 !important;
    /* override head.php padding */
    background: #ffffff !important;
    color: #0b1220;
    font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
    overflow-x: hidden;
    scroll-behavior: smooth;
  }

  body.fptHomeBody .wrap {
    width: 100% !important;
    margin: 0 !important;
    max-width: none !important;
  }

  /* Snap only desktop/tablet */
  @media (min-width: 900px) {
    body.fptHomeBody {
      scroll-snap-type: y mandatory;
      scroll-padding-top: 76px;
      /* biar anchor gak ketutup nav */
    }

    body.fptHomeBody .fptHome-snap {
      scroll-snap-align: start;
      scroll-snap-stop: always;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    body.fptHomeBody {
      scroll-behavior: auto;
    }

    body.fptHomeBody {
      scroll-snap-type: none;
    }
  }

  /* Design tokens */
  :root {
    --fptText: #0b1220;
    --fptMuted: #3a4a63;
    --fptLine: rgba(15, 23, 42, .10);
    --fptBlue: #1d4ed8;
    --fptBlue2: #2563eb;
    --fptCyan: #22d3ee;
    --fptBg: #ffffff;
    --fptSoft: #f5f7ff;
    --fptCard: #ffffff;
    --fptShadow: 0 18px 60px rgba(2, 6, 23, .12);
    --fptRadius: 22px;
  }

  .fptHome {
    min-height: 100vh;
    background: var(--fptBg);
  }

  /* ===========================
   TOP NAV (sticky)
   =========================== */
  .fptHome-nav {
    position: sticky;
    top: 0;
    z-index: 50;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, .78);
    border-bottom: 1px solid var(--fptLine);
  }

  .fptHome-navInner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  /* BRAND: logo only (no "$" and no text) */
  .fptHome-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--fptText);
    white-space: nowrap;
  }

  .fptHome-brandLogo {
    display: block;
    height: 28px;
    /* desktop feel */
    width: auto;
  }

  @media (max-width: 520px) {
    .fptHome-brandLogo {
      height: 24px;
    }
  }

  .fptHome-links {
    display: none;
    gap: 18px;
    align-items: center;
    font-weight: 600;
    color: rgba(11, 18, 32, .78);
  }

  .fptHome-links a {
    text-decoration: none;
    color: inherit;
    padding: 10px 8px;
    border-radius: 12px;
  }

  .fptHome-links a:hover {
    background: rgba(15, 23, 42, .04);
  }

  .fptHome-ctaRow {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .fptHome-btn {
    height: 38px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, .10);
    background: #fff;
    color: var(--fptText);
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    transition: transform .06s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
  }

  .fptHome-btn:active {
    transform: translateY(1px);
  }

  .fptHome-btnPrimary {
    border-color: rgba(29, 78, 216, .22);
    background: linear-gradient(135deg, var(--fptBlue), var(--fptBlue2));
    color: #fff;
    box-shadow: 0 14px 34px rgba(29, 78, 216, .20);
  }

  .fptHome-btnPrimary:hover {
    box-shadow: 0 18px 40px rgba(29, 78, 216, .26);
  }

  .fptHome-btnGhost:hover {
    background: rgba(15, 23, 42, .04);
  }

  @media (min-width: 920px) {
    .fptHome-links {
      display: flex;
    }

    .fptHome-btn {
      height: 40px;
      padding: 0 16px;
    }
  }

  /* ===========================
   HERO WRAP + BLOBS
   =========================== */
  .fptHome-heroWrap {
    position: relative;
    overflow: hidden;
    background:
      radial-gradient(900px 420px at 12% 18%, rgba(37, 99, 235, .10), transparent 60%),
      radial-gradient(760px 420px at 92% 18%, rgba(34, 211, 238, .10), transparent 60%),
      linear-gradient(180deg, #ffffff, #ffffff 45%, #f7f9ff 100%);
  }

  .fptHome-blob {
    position: absolute;
    width: 520px;
    height: 520px;
    border-radius: 50%;
    filter: blur(46px);
    opacity: .62;
    pointer-events: none;
    transform: translateZ(0);
    will-change: transform;
  }

  .fptHome-blobA {
    left: -160px;
    top: -220px;
    background: radial-gradient(circle at 30% 30%, rgba(37, 99, 235, .55), rgba(34, 211, 238, .15) 60%, transparent 72%);
    animation: fptBlobFloat 10s ease-in-out infinite;
  }

  .fptHome-blobB {
    right: -180px;
    top: -220px;
    background: radial-gradient(circle at 30% 30%, rgba(34, 211, 238, .55), rgba(37, 99, 235, .12) 60%, transparent 72%);
    animation: fptBlobFloat2 12s ease-in-out infinite;
  }

  @keyframes fptBlobFloat {

    0%,
    100% {
      transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
      transform: translate3d(44px, 22px, 0) scale(1.04);
    }
  }

  @keyframes fptBlobFloat2 {

    0%,
    100% {
      transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
      transform: translate3d(-50px, 18px, 0) scale(1.05);
    }
  }

  @media (prefers-reduced-motion: reduce) {

    .fptHome-blobA,
    .fptHome-blobB {
      animation: none;
    }
  }

  /* ===========================
   HERO (mobile-first)
   =========================== */
  .fptHome-hero {
    max-width: 1180px;
    margin: 0 auto;
    padding: 34px 18px 26px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
  }

  .fptHome-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(29, 78, 216, .08);
    border: 1px solid rgba(29, 78, 216, .14);
    color: rgba(11, 18, 32, .86);
    font-weight: 600;
    letter-spacing: .6px;
    font-size: 11.5px;
    text-transform: uppercase;
    width: max-content;
  }

  .fptHome-h1 {
    margin: 10px 0 10px;
    font-weight: 800;
    letter-spacing: -1.2px;
    line-height: 1.02;
    font-size: 42px;
    color: var(--fptText);
  }

  .fptHome-h1 .fptHome-accent {
    color: var(--fptBlue);
  }

  .fptHome-p {
    margin: 0;
    color: rgba(11, 18, 32, .78);
    font-size: 15.5px;
    line-height: 1.55;
    max-width: 58ch;
    font-weight: 500;
  }

  .fptHome-heroBtns {
    margin-top: 14px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  .fptHome-heroMeta {
    margin-top: 10px;
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    align-items: center;
    color: rgba(11, 18, 32, .70);
    font-size: 12.5px;
    font-weight: 600;
  }

  .fptHome-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(29, 78, 216, .35);
    border: 1px solid rgba(29, 78, 216, .35);
  }

  /* Faux “video” card */
  .fptHome-faux {
    margin-top: 16px;
    border-radius: var(--fptRadius);
    border: 1px solid rgba(15, 23, 42, .10);
    background: #fff;
    box-shadow: var(--fptShadow);
    overflow: hidden;
  }

  .fptHome-fauxMedia {
    position: relative;
    aspect-ratio: 16 / 10;
    background:
      radial-gradient(900px 420px at 20% 20%, rgba(34, 211, 238, .55), transparent 60%),
      radial-gradient(900px 420px at 80% 30%, rgba(37, 99, 235, .55), transparent 60%),
      linear-gradient(135deg, rgba(29, 78, 216, .85), rgba(34, 211, 238, .72));
    background-size: 140% 140%;
    animation: fptFauxMove 7.5s ease-in-out infinite;
  }

  @keyframes fptFauxMove {

    0%,
    100% {
      background-position: 0% 0%;
    }

    50% {
      background-position: 100% 60%;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .fptHome-fauxMedia {
      animation: none;
    }
  }

  .fptHome-fauxOverlay {
    position: absolute;
    inset: 0;
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .fptHome-fauxTitle {
    color: #fff;
    font-weight: 800;
    letter-spacing: -.8px;
    line-height: 1.04;
    font-size: 34px;
    text-shadow: 0 18px 45px rgba(0, 0, 0, .22);
  }

  .fptHome-fauxTitle span {
    opacity: .92;
    font-weight: 600;
  }

  .fptHome-fauxFoot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    color: rgba(255, 255, 255, .92);
    font-weight: 600;
    font-size: 12.5px;
  }

  .fptHome-fauxBtn {
    width: 46px;
    height: 46px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .92);
    border: 1px solid rgba(255, 255, 255, .85);
    display: grid;
    place-items: center;
    box-shadow: 0 18px 40px rgba(0, 0, 0, .16);
  }

  .fptHome-fauxBtn i {
    display: block;
    width: 12px;
    height: 18px;
    border-radius: 2px;
    background: var(--fptBlue);
    box-shadow: 10px 0 0 var(--fptBlue);
  }

  /* Desktop layout */
  @media (min-width: 980px) {
    .fptHome-hero {
      padding: 56px 18px 44px;
      grid-template-columns: 1.05fr .95fr;
      align-items: center;
      gap: 34px;
    }

    .fptHome-h1 {
      font-size: 64px;
    }

    .fptHome-p {
      font-size: 16.5px;
    }

    .fptHome-faux {
      margin-top: 0;
    }

    .fptHome-fauxTitle {
      font-size: 42px;
    }
  }

  /* ===========================
   SECTION BASE
   =========================== */
  .fptHome-section {
    max-width: 1180px;
    margin: 0 auto;
    padding: 54px 18px;
  }

  @media (min-width: 900px) {
    .fptHome-section.fptHome-snap {
      padding: 72px 18px;
    }
  }

  .fptHome-kicker {
    font-size: 13px;
    font-weight: 600;
    color: rgba(11, 18, 32, .72);
    margin: 0 0 8px;
  }

  .fptHome-h2 {
    margin: 0 0 8px;
    font-size: 34px;
    line-height: 1.08;
    letter-spacing: -.8px;
    font-weight: 700;
  }

  .fptHome-sub {
    margin: 0;
    color: rgba(11, 18, 32, .75);
    font-size: 15px;
    line-height: 1.55;
    font-weight: 500;
    max-width: 70ch;
  }

  /* ===========================
   STICKY "HOW IT WORKS"
   =========================== */
  .fptHome-stickyGrid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
    margin-top: 18px;
  }

  .fptHome-steps {
    display: grid;
    gap: 12px;
  }

  .fptHome-step {
    border: 1px solid rgba(15, 23, 42, .10);
    border-radius: 18px;
    background: #fff;
    padding: 14px 14px;
    box-shadow: 0 10px 30px rgba(2, 6, 23, .06);
  }

  .fptHome-stepTop {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 6px;
  }

  .fptHome-num {
    width: 34px;
    height: 34px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: rgba(29, 78, 216, .10);
    border: 1px solid rgba(29, 78, 216, .18);
    color: var(--fptBlue);
    font-weight: 800;
  }

  .fptHome-step h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: -.2px;
  }

  .fptHome-step p {
    margin: 0;
    color: rgba(11, 18, 32, .72);
    font-size: 13.5px;
    line-height: 1.5;
    font-weight: 500;
  }

  .fptHome-stickyCard {
    border-radius: var(--fptRadius);
    border: 1px solid rgba(15, 23, 42, .10);
    background: linear-gradient(180deg, #ffffff, #f7f9ff);
    box-shadow: var(--fptShadow);
    overflow: hidden;
  }

  .fptHome-stickyCardInner {
    padding: 16px;
  }

  .fptHome-miniUI {
    border: 1px solid rgba(15, 23, 42, .08);
    border-radius: 18px;
    background: #fff;
    padding: 14px;
    display: grid;
    gap: 10px;
  }

  .fptHome-miniRow {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }

  .fptHome-pill {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    height: 28px;
    padding: 0 10px;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, .10);
    background: #fff;
    font-weight: 600;
    font-size: 12px;
    color: rgba(11, 18, 32, .85);
  }

  .fptHome-miniBalance {
    font-weight: 800;
    letter-spacing: -.2px;
  }

  .fptHome-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap
  }

  .fptHome-chip {
    height: 30px;
    padding: 0 12px;
    border-radius: 999px;
    border: 1px solid rgba(29, 78, 216, .18);
    background: rgba(29, 78, 216, .08);
    color: var(--fptBlue);
    font-weight: 600;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .fptHome-miniList {
    display: grid;
    gap: 10px;
  }

  .fptHome-miniItem {
    display: flex;
    gap: 12px;
    align-items: center;
    border: 1px solid rgba(15, 23, 42, .08);
    background: #fff;
    border-radius: 16px;
    padding: 12px;
  }

  .fptHome-miniIcon {
    width: 36px;
    height: 36px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(15, 23, 42, .10);
    background: rgba(15, 23, 42, .03);
    font-weight: 700;
  }

  .fptHome-miniTxt b {
    display: block;
    font-size: 13px;
    letter-spacing: -.1px
  }

  .fptHome-miniTxt span {
    display: block;
    color: rgba(11, 18, 32, .72);
    font-size: 12.5px;
    font-weight: 500
  }

  @media (min-width: 980px) {
    .fptHome-stickyGrid {
      grid-template-columns: 1.05fr .95fr;
      gap: 22px;
      align-items: start;
    }

    .fptHome-stickyCard {
      position: sticky;
      top: 86px;
    }

    .fptHome-h2 {
      font-size: 42px;
    }
  }

  /* ===========================
   FEATURES
   =========================== */
  .fptHome-featGrid {
    margin-top: 18px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .fptHome-feat {
    border: 1px solid rgba(15, 23, 42, .10);
    border-radius: 18px;
    background: #fff;
    padding: 14px;
    box-shadow: 0 10px 28px rgba(2, 6, 23, .05);
  }

  .fptHome-feat b {
    display: block;
    font-weight: 800;
    letter-spacing: -.2px;
    margin-bottom: 6px;
  }

  .fptHome-feat p {
    margin: 0;
    color: rgba(11, 18, 32, .72);
    font-weight: 500;
    line-height: 1.5;
    font-size: 13.5px;
  }

  @media (min-width: 780px) {
    .fptHome-featGrid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  /* ===========================
   FOOTER
   =========================== */
  .fptHome-footer {
    border-top: 1px solid var(--fptLine);
    padding: 26px 18px;
    color: rgba(11, 18, 32, .68);
    font-weight: 500;
    font-size: 13px;
  }

  .fptHome-footerInner {
    max-width: 1180px;
    margin: 0 auto;
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .fptHome-footer a {
    color: var(--fptBlue);
    text-decoration: none;
    font-weight: 600;
  }

  .fptHome-footer a:hover {
    text-decoration: underline;
  }
</style>

<script>
  (function () {
    try {
      document.body.classList.add('fptHomeBody');
      document.documentElement.classList.add('fptHomeScroll');
    } catch (e) { }
  })();
</script>

<div class="fptHome">
  <!-- NAV -->
  <div class="fptHome-nav">
    <div class="fptHome-navInner">
      <!-- LOGO ONLY -->
      <a class="fptHome-brand" href="/" aria-label="FastPayTrack Home">
        <img class="fptHome-brandLogo" src="<?= htmlspecialchars($brandLogo, ENT_QUOTES, 'UTF-8') ?>"
          alt="FASTPAYTRACK">
      </a>

      <div class="fptHome-links" aria-label="Primary navigation">
        <a href="#how">How it works</a>
        <a href="#features">Features</a>
        <a href="#security">Security</a>
        <a href="#faq">FAQ</a>
      </div>

      <div class="fptHome-ctaRow">
        <a class="iconPill" href="/checkout" title="Keranjang" aria-label="Keranjang" style="position:relative;">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="30" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M6 6h15l-2 9H7L6 6Z" />
            <path d="M6 6 5 3H2" />
            <path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
            <path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
          </svg>
        </a>
      </div>
    </div>
  </div>

  <!-- HERO (Snap section) -->
  <section class="fptHome-heroWrap fptHome-snap" id="top">
    <div class="fptHome-blob fptHome-blobA"></div>
    <div class="fptHome-blob fptHome-blobB"></div>

    <div class="fptHome-hero">
      <div>
        <h1 class="fptHome-h1">
          Jelajahi berbagai kebutuhan <br /><span class="fptHome-accent">
            digital </span><span class="fptHome-h1">Kamu.</span>
        </h1>
        <p class="fptHome-p">
          Cari berbagai kebutuhan digital Anda dan kirim uang dengan mudah ke teman Anda menggunakan FastPayTrack
          wallet, bayar semua* yang Anda inginkan secara online, di toko, dan di aplikasi.
        </p>

        <div class="fptHome-heroBtns">
          <a class="fptHome-btn fptHome-btnPrimary" href="/register">Bergabung Sekarang</a>
        </div>

        <div class="fptHome-heroMeta" aria-label="Highlights">
          *Gabung untuk mendapatkan cashback hingga 5%
        </div>
      </div>

      <!-- Faux video card -->
      <div class="fptHome-faux" aria-label="Faux video preview">
        <div class="fptHome-fauxMedia">
          <div class="fptHome-fauxOverlay">
            <div class="fptHome-fauxTitle">Fastpaytrack <span>Gift Card.</span></div>
            <div class="fptHome-fauxFoot">
              <span>Diskon hingga 2% menggunakan Kartu Hadiah.</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- STICKY HOW IT WORKS (Snap section) -->
  <section class="fptHome-section fptHome-snap" id="how">
    <p class="fptHome-kicker">Dompet FastPayTrack</p>
    <h2 class="fptHome-h2">Kirim uang ke teman-temanmu</h2>
    <p class="fptHome-sub">
      Dompet FastPayTrack membuat proses pembayaran dengan teman terasa lebih... ramah. Kirim dan terima uang dengan
      teman-teman secara instan untuk membagi kebutuhan sehari-hari, tagihan, dan aktivitas lainnya.
    </p>

    <div class="fptHome-stickyGrid">
      <div class="fptHome-steps">
        <div class="fptHome-step">
          <div class="fptHome-stepTop">
            <div class="fptHome-num">1</div>
            <h3>Buat akun</h3>
          </div>
          <p>Registrasi cepat dan langsung siap dipakai.</p>
        </div>

        <div class="fptHome-step">
          <div class="fptHome-stepTop">
            <div class="fptHome-num">2</div>
            <h3>Deposit saldo</h3>
          </div>
          <p>Deposit menggunakan rekening bank Anda.</p>
        </div>

        <div class="fptHome-step">
          <div class="fptHome-stepTop">
            <div class="fptHome-num">3</div>
            <h3>Kirim dan terima uang</h3>
          </div>
          <p>Kirim dan terima uang menggunakan saldo.</p>
        </div>

        <div class="fptHome-step">
          <div class="fptHome-stepTop">
            <div class="fptHome-num">4</div>
            <h3>Belanja kebutuhan digital</h3>
          </div>
          <p>Belanja menggunakan saldo atau berbagai metode pembayaran lainnya.</p>
        </div>
      </div>

      <div class="fptHome-stickyCard" aria-label="Sticky demo card">
        <div class="fptHome-stickyCardInner">
          <div class="fptHome-miniUI">
            <div class="fptHome-miniRow">
              <span class="fptHome-pill"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  viewBox="0 0 24 24">
                  <path d="M3 7a3 3 0 0 1 3-3h12a2 2 0 0 1 2 2v2" />
                  <path d="M3 7v12a3 3 0 0 0 3 3h14a1 1 0 0 0 1-1v-9a2 2 0 0 0-2-2H6a3 3 0 0 1-3-3Z" />
                  <path d="M17 14h.01" />
                </svg><span class="home-rowBig"> FastPayTrack Wallet</span>
            </div>
            <div class="fptHome-miniList">
              <div class="fptHome-miniItem">
                <a class="iconPill" href="/topup" title="Topup Saldo" aria-label="Topup Saldo">
                  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="18" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v8M8 12h8" />
                  </svg>
                </a>
                <div class="fptHome-miniTxt">
                  <b>Deposit instan</b>
                  <span>Deposit cepat dan ramah.</span>
                </div>
              </div>

              <div class="fptHome-miniItem">
                <a class="iconPill" href="/checkout" title="Keranjang" aria-label="Keranjang"
                  style="position:relative;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="18" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M6 6h15l-2 9H7L6 6Z" />
                    <path d="M6 6 5 3H2" />
                    <path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                    <path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                  </svg>
                </a>
                <div class="fptHome-miniTxt">
                  <b>Pembayaran cepat</b>
                  <span>Pembayaran ringkas, minim langkah.</span>
                </div>
              </div>

              <div class="fptHome-miniItem">
                <a class="iconPill" href="/wallet/transfer" title="Transfer Saldo" aria-label="Transfer Saldo">
                  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="18" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M22 2L11 13"></path>
                    <path d="M22 2l-7 20-4-9-9-4 20-7z"></path>
                  </svg>
                </a>
                <div class="fptHome-miniTxt">
                  <b>Transfer cepat</b>
                  <span>Transfer secara real-time.</span>
                </div>
              </div>

              <div class="fptHome-miniItem">
                <a class="iconPill" href="/topup" title="Keamanan" aria-label="Keamanan">
                  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="18" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  </svg>
                </a>
                <div class="fptHome-miniTxt">
                  <b>Transaksi aman</b>
                  <span>Lakukan transaksi dengan keamanan berlapis.</span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES (Snap section) -->
  <section class="fptHome-section fptHome-snap" id="features">
    <p class="fptHome-kicker">Features</p>
    <h2 class="fptHome-h2">Modern, clean, and fast</h2>
    <p class="fptHome-sub">
      Fokus ke UX: simple, elegan, dan responsif di semua device—tanpa bikin backend yang sudah jalan jadi bentrok.
    </p>

    <div class="fptHome-featGrid">
      <div class="fptHome-feat">
        <b>OTP + Trusted Device</b>
        <p>Keamanan tetap kuat, tapi pengalaman login jauh lebih nyaman.</p>
      </div>
      <div class="fptHome-feat">
        <b>Wallet & Transfer</b>
        <p>Saldo jelas, riwayat rapi, dan flow transaksi enak dipakai.</p>
      </div>
      <div class="fptHome-feat">
        <b>Checkout & Orders</b>
        <p>Proses pembelian ringkas: cepat, minim friction, tetap aman.</p>
      </div>
    </div>
  </section>

  <!-- SECURITY (Snap section) -->
  <section class="fptHome-section fptHome-snap" id="security">
    <p class="fptHome-kicker">Security</p>
    <h2 class="fptHome-h2">Security you can feel</h2>
    <p class="fptHome-sub">
      Sesi login tercatat, device bisa dikelola, dan OTP gate tetap menjaga akses—cocok untuk kebutuhan modern.
    </p>
  </section>

  <!-- FAQ (Snap section) -->
  <section class="fptHome-section fptHome-snap" id="faq">
    <p class="fptHome-kicker">FAQ</p>
    <h2 class="fptHome-h2">Common questions</h2>

    <div class="fptHome-featGrid" style="grid-template-columns:1fr;">
      <div class="fptHome-feat">
        <b>Kenapa sebelumnya scroll-snap tidak jalan?</b>
        <p>Karena snap dipasang ke elemen yang tidak scroll. Sekarang snap ada di <code>body.fptHomeBody</code> jadi
          jalan di desktop.</p>
      </div>
      <div class="fptHome-feat">
        <b>Kalau animasi tetap tidak muncul?</b>
        <p>Cek OS/browser apakah aktif “Reduce motion”. Kalau aktif, CSS mematikan animasi.</p>
      </div>
      <div class="fptHome-feat">
        <b>Apakah ini mengganggu halaman lain?</b>
        <p>Tidak. Semua style hanya aktif kalau <code>body</code> punya class <code>fptHomeBody</code> (homepage saja).
        </p>
      </div>
    </div>
  </section>

  <div class="fptHome-footer">
    <div class="fptHome-footerInner">
      <div>© <?= date('Y') ?> PT. Digi Karya Harmoni</div>
      <div>
        <a href="/login">Log in</a> · <a href="/register">Get started</a>
      </div>
    </div>
  </div>
</div>

<script>
  /* Parallax ringan untuk blob (tanpa JS berat) */
  (function () {
    var blobs = document.querySelectorAll('.fptHome-blob');
    if (!blobs || !blobs.length) return;

    var ticking = false;

    function onScroll() {
      if (ticking) return;
      ticking = true;

      requestAnimationFrame(function () {
        var y = window.scrollY || window.pageYOffset || 0;
        for (var i = 0; i < blobs.length; i++) {
          var speed = (i === 0) ? 0.12 : 0.08;
          blobs[i].style.transform = 'translate3d(0,' + (y * speed) + 'px,0)';
        }
        ticking = false;
      });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();
</script>