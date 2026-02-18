<?php
use App\Lib\CSRF;

if (!function_exists('e')) {
  function e($s)
  {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
  }
}

$metaTitle = $metaTitle ?? 'FastPayTrack';
$metaDesc = $metaDesc ?? '';
$bodyClass = $bodyClass ?? '';

// Mobile-portrait overlay hanya untuk halaman selain "/"
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$currentPath = rtrim($currentPath, '/') ?: '/';
$enableMobileOverlay = ($currentPath !== '/');
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= e($metaTitle) ?></title>
  <?php if ($metaDesc): ?>
    <meta name="description" content="<?= e($metaDesc) ?>" />
  <?php endif; ?>

  <!-- Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg1: #6d5efc;
      --bg2: #35c6ff;
      --card: #fff;
      --text: #0f172a;
      --muted: #64748b;
      --border: #e2e8f0;
      --primary: #4f46e5;
      --primaryHover: #4338ca;
      --danger: #ef4444;
      --success: #10b981;
      --shadow: 0 20px 45px rgba(2, 6, 23, .18);
    }

    * { box-sizing: border-box }

    body {
      margin: 0;
      font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
      color: var(--text);
      min-height: 100vh;
      background: radial-gradient(1200px 600px at 20% 10%, rgba(255, 255, 255, .28), transparent 60%),
        linear-gradient(135deg, var(--bg1), var(--bg2));
      padding: 24px 14px;
    }

    .wrap { width: min(980px, 100%); margin: 0 auto; }

    .shell {
      border-radius: 28px;
      overflow: hidden;
      background: rgba(255, 255, 255, .12);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, .22);
      box-shadow: var(--shadow);
    }

    .hero {
      padding: 22px 22px 18px;
      color: #fff;
      position: relative;
      overflow: hidden
    }

    .hero:before {
      content: "";
      position: absolute;
      inset: -60px -60px auto auto;
      width: 240px;
      height: 240px;
      background: rgba(255, 255, 255, .18);
      border-radius: 50%
    }

    .topbar {
      position: relative;
      z-index: 1;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap
    }

    .brand { display: flex; gap: 10px; align-items: center; font-weight: 700 }

    .logo {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      background: rgba(255, 255, 255, .22);
      border: 1px solid rgba(255, 255, 255, .25);
      display: grid;
      place-items: center;
      font-weight: 700
    }

    .pill {
      height: 36px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, .28);
      background: rgba(255, 255, 255, .16);
      color: #fff;
      font-weight: 600;
      font-size: 12.5px;
      padding: 0 12px;
      display: flex;
      gap: 8px;
      align-items: center;
      cursor: pointer;
      text-decoration: none
    }

    .pill:hover { background: rgba(255, 255, 255, .22) }

    .heroTitle {
      margin: 14px 0 6px;
      font-size: 22px;
      font-weight: 700;
      position: relative;
      z-index: 1
    }

    .heroSub {
      margin: 0;
      opacity: .92;
      font-size: 13.5px;
      font-weight: 400;
      position: relative;
      z-index: 1
    }

    .card {
      background: var(--card);
      padding: 16px 16px 18px;
      border-radius: 22px 22px 0 0
    }

    .panel {
      border: 1px solid var(--border);
      border-radius: 18px;
      background: #fff;
      overflow: hidden
    }

    .panelHead {
      padding: 12px 12px 10px;
      border-bottom: 1px solid var(--border)
    }

    .panelTitle { margin: 0; font-weight: 700; font-size: 14.5px }

    .panelSub {
      margin: 6px 0 0;
      color: var(--muted);
      font-weight: 500;
      font-size: 12.5px;
      line-height: 1.35
    }

    .panelBody { padding: 12px }

    .field { margin: 10px 0 }

    label {
      display: block;
      font-size: 12px;
      color: var(--muted);
      margin: 0 0 6px;
      font-weight: 600
    }

    input, select, textarea {
      width: 100%;
      border-radius: 14px;
      border: 1px solid var(--border);
      padding: 12px 14px;
      font-size: 14px;
      outline: none;
      background: #fff;
      font-family: inherit;
      transition: box-shadow .15s, border-color .15s;
    }

    input:focus, select:focus, textarea:focus {
      border-color: rgba(79, 70, 229, .55);
      box-shadow: 0 0 0 4px rgba(79, 70, 229, .12)
    }

    textarea { min-height: 90px; resize: vertical }

    .btn {
      height: 46px;
      border-radius: 14px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      font-size: 14.5px;
      letter-spacing: .2px;
      transition: transform .05s, background .15s, box-shadow .15s;
      padding: 0 14px;
      width: 100%;
      font-family: inherit
    }

    .btn:active { transform: translateY(1px) }

    .btnPrimary {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 12px 24px rgba(79, 70, 229, .25)
    }

    .btnPrimary:hover { background: var(--primaryHover) }

    .btnGhost {
      background: #fff;
      border: 1px solid var(--border);
      color: #0f172a
    }

    .btnGhost:hover {
      background: #f8fafc;
      box-shadow: 0 10px 20px rgba(2, 6, 23, .06)
    }

    .row2 { display: grid; grid-template-columns: 1fr; gap: 10px }
    @media(min-width:560px) { .row2 { grid-template-columns: 1fr 1fr } }

    .mono { font-family: ui-monospace, Menlo, Consolas, monospace; font-weight: 600 }

    .note { color: var(--muted); font-weight: 500; font-size: 12.5px; line-height: 1.4 }

    .link { color: var(--primary); font-weight: 700; text-decoration: none }
    .link:hover { text-decoration: underline }

    /* ===== Password toggle (eye icon) — TANPA LINGKARAN ===== */
    .pwWrap { position: relative }
    .pwWrap input { padding-right: 46px }

    .pwToggle {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      width: 34px;
      height: 34px;
      border-radius: 999px;
      border: none;
      background: transparent;
      display: grid;
      place-items: center;
      cursor: pointer;
      color: #64748b;
      transition: background .15s;
    }

    .pwToggle:hover { background: rgba(2, 6, 23, .05) }
    .pwToggle:active { transform: translateY(-50%) scale(.98) }

    .pwIcon { width: 18px; height: 18px }
    .pwIconEyeOff { display: none }
    .pwToggle.isShown .pwIconEye { display: none }
    .pwToggle.isShown .pwIconEyeOff { display: block }

    /* ===== Cookie Consent Modal (slide up) ===== */
    .cookieOverlay {
      position: fixed;
      inset: 0;
      display: none;
      align-items: flex-end;
      justify-content: center;
      background: rgba(2, 6, 23, .45);
      padding: 18px;
      z-index: 9999;
    }

    .cookieOverlay.isOpen { display: flex }

    .cookieModal {
      width: min(720px, 100%);
      background: #fff;
      border-radius: 18px;
      border: 1px solid rgba(226, 232, 240, .9);
      box-shadow: 0 26px 55px rgba(2, 6, 23, .35);
      overflow: hidden;
      transform: translateY(40px);
      opacity: 0;
      will-change: transform, opacity;
    }

    .cookieOverlay.isOpen .cookieModal { animation: cookieSlideUp .32s cubic-bezier(.2, .8, .2, 1) forwards; }

    @keyframes cookieSlideUp {
      from { transform: translateY(46px); opacity: 0 }
      to { transform: translateY(0); opacity: 1 }
    }

    @media (prefers-reduced-motion: reduce) {
      .cookieOverlay.isOpen .cookieModal { animation: none; transform: none; opacity: 1 }
    }

    .cookieHead {
      padding: 14px 16px 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      border-bottom: 1px solid var(--border);
      background: linear-gradient(180deg, #fff, #f8fafc);
    }

    .cookieTitle {
      margin: 0;
      font-weight: 700;
      font-size: 14px;
      color: #0f172a;
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .cookieBadge {
      width: 34px;
      height: 34px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: rgba(79, 70, 229, .10);
      border: 1px solid rgba(79, 70, 229, .20);
      font-weight: 700;
      color: var(--primary);
    }

    .cookieBody { padding: 12px 16px 14px }
    .cookieBody p { margin: 0; color: #334155; font-weight: 500; font-size: 12.8px; line-height: 1.45 }

    .cookieActions {
      padding: 12px 16px 16px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 10px;
    }

    .cookieBtn {
      height: 44px;
      border-radius: 14px;
      border: 1px solid transparent;
      font-weight: 700;
      cursor: pointer;
      font-size: 14px;
      font-family: inherit
    }

    .cookieAllow {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 12px 24px rgba(79, 70, 229, .22)
    }

    .cookieAllow:hover { background: var(--primaryHover) }

    .cookieDeny {
      background: #fff;
      border-color: var(--border);
      color: #0f172a
    }

    .cookieDeny:hover { background: #f8fafc; box-shadow: 0 10px 20px rgba(2, 6, 23, .06) }

    @media (min-width: 560px) { .cookieActions { grid-template-columns: 1fr 1fr } }

    /* =======================================================================
       AUTH PAGES — SIMPLE (Login/Register/Forgot/OTP)
       Active when body has class "page-auth"
       ======================================================================= */
    body.page-auth {
      padding: 0 !important;
      background:
        radial-gradient(900px 520px at 12% 12%, rgba(99, 102, 241, .18), transparent 60%),
        radial-gradient(900px 520px at 88% 18%, rgba(34, 211, 238, .18), transparent 60%),
        radial-gradient(900px 520px at 80% 90%, rgba(147, 51, 234, .10), transparent 55%),
        #ffffff !important;
      color: #0b1220;
      min-height: 100vh;
    }

    body.page-auth .wrap {
      width: 100% !important;
      max-width: none !important;
      margin: 0 !important;
      min-height: 100vh;
      padding: 24px 16px !important;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    body.page-auth .authShell {
      width: min(560px, 100%);
      border-radius: 30px;
      overflow: hidden;
      background: rgba(255, 255, 255, .78);
      border: 1px solid rgba(226, 232, 240, .95);
      box-shadow: 0 34px 90px rgba(2, 6, 23, .12);
      backdrop-filter: blur(10px);
    }

    body.page-auth .authTop {
      padding: 18px 18px 14px;
      background: rgba(248, 250, 252, .65);
      border-bottom: 1px solid rgba(226, 232, 240, .92);
    }

    body.page-auth .authTopbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    body.page-auth .authBrand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      color: #0b1220;
      font-weight: 700;
      letter-spacing: .2px;
      min-width: 0;
    }

    body.page-auth .authBrandImg {
      height: 28px;
      width: auto;
      display: block;
    }

    body.page-auth .authPill {
      height: 34px;
      padding: 0 12px;
      border-radius: 999px;
      border: 1px solid rgba(226, 232, 240, .95);
      background: #fff;
      color: #0b1220;
      font-weight: 600;
      font-size: 12.5px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      white-space: nowrap;
      box-shadow: 0 12px 26px rgba(2, 6, 23, .06);
    }

    body.page-auth .authPill:hover { background: #f8fafc }

    body.page-auth .authKicker {
      margin-top: 12px;
      display: inline-flex;
      align-items: center;
      height: 30px;
      padding: 0 12px;
      border-radius: 999px;
      background: rgba(99, 102, 241, .08);
      border: 1px solid rgba(99, 102, 241, .14);
      color: rgba(11, 18, 32, .72);
      font-weight: 600;
      letter-spacing: .14em;
      font-size: 11px;
    }

    body.page-auth .authTitle {
      margin: 10px 0 6px;
      font-size: 30px;
      letter-spacing: -.02em;
      line-height: 1.1;
      font-weight: 800;
      color: #0b1220;
    }

    body.page-auth .authSub {
      margin: 0;
      color: rgba(11, 18, 32, .70);
      font-weight: 600;
      line-height: 1.6;
      font-size: 13.5px;
    }

    body.page-auth .authBody { padding: 16px 18px 18px; background: rgba(255, 255, 255, .65); }

    body.page-auth .authForm {
      border-radius: 22px;
      background: rgba(255, 255, 255, .82);
      border: 1px solid rgba(226, 232, 240, .85);
      padding: 16px;
    }

    body.page-auth input {
      border-radius: 999px;
      padding: 12px 16px;
      border: 1px solid rgba(226, 232, 240, .95);
      background: #fff;
    }

    body.page-auth .btn { border-radius: 999px; height: 46px; font-weight: 700; }
    body.page-auth .btnPrimary { box-shadow: 0 16px 34px rgba(79, 70, 229, .22); }

    body.page-auth .rememberRow { margin: 12px 0 12px; }

    body.page-auth .rememberBox {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 12px 12px;
      border: 1px solid rgba(226, 232, 240, .95);
      border-radius: 16px;
      background: #f8fafc;
      cursor: pointer;
    }

    body.page-auth .rememberBox input {
      width: 18px;
      height: 18px;
      margin: 2px 0 0;
      flex: 0 0 auto;
      accent-color: var(--primary);
    }

    body.page-auth .rememberText {
      display: flex;
      flex-direction: column;
      gap: 4px;
      font-weight: 600;
      color: #0b1220;
      font-size: 13px;
      line-height: 1.2;
      min-width: 0;
    }

    body.page-auth .rememberHint {
      font-weight: 700;
      color: rgba(11, 18, 32, .62);
      font-size: 12px;
      line-height: 1.35;
    }

    body.page-auth .rememberBox input:disabled { accent-color: #cbd5e1 }
    body.page-auth .rememberBox:has(input:disabled) { opacity: .75; cursor: not-allowed }

    body.page-auth .authNote {
      margin-top: 12px;
      text-align: center;
      color: rgba(11, 18, 32, .70);
      font-weight: 600;
      font-size: 12.5px;
      line-height: 1.4;
    }

    body.page-auth .authNote a { color: var(--primary); font-weight: 700; text-decoration: none; }
    body.page-auth .authNote a:hover { text-decoration: underline; }

    @media (max-width: 420px) {
      body.page-auth .authTitle { font-size: 26px; }
      body.page-auth .authTop { padding: 16px 14px 12px; }
      body.page-auth .authBody { padding: 12px 14px 14px; }
      body.page-auth .authForm { padding: 14px; }
    }

    /* =======================================================================
       DASHBOARD — styles only active when body has class "page-dashboard"
       ======================================================================= */
    body.page-dashboard {
      padding: 0 !important;
      background:
        radial-gradient(1100px 560px at 12% 18%, rgba(37, 99, 235, .12), transparent 60%),
        radial-gradient(980px 560px at 88% 18%, rgba(34, 211, 238, .14), transparent 60%),
        radial-gradient(980px 560px at 75% 92%, rgba(147, 51, 234, .08), transparent 55%),
        #ffffff !important;
      color: #0b1220;
      min-height: 100vh;
    }

    body.page-dashboard .wrap {
      width: 100% !important;
      max-width: 1180px !important;
      margin: 0 auto !important;
      padding: 24px 16px 44px !important;
    }

    body.page-dashboard .shell {
      background: rgba(255, 255, 255, .60);
      border: 1px solid rgba(226, 232, 240, .95);
      box-shadow: 0 34px 90px rgba(2, 6, 23, .10);
      backdrop-filter: blur(12px);
    }

    body.page-dashboard .hero {
      color: #fff;
      background:
        radial-gradient(900px 420px at 15% 15%, rgba(255, 255, 255, .20), transparent 60%),
        linear-gradient(135deg, #6d5efc, #35c6ff);
    }

    body.page-dashboard .card { background: rgba(255, 255, 255, .92); }

    @media (min-width: 980px) {
      body.page-dashboard .wrap { padding: 34px 18px 60px !important; }
    }

    /* =======================================================================
       HOMEPAGE (PUBLIC) — styles only active when body has class "page-home"
       ======================================================================= */
    body.page-home {
      background: #fff;
      padding: 0;
      color: #0b1220;
    }

    body.page-home .wrap {
      width: 100%;
      margin: 0;
      max-width: none;
    }

    body.page-home .reveal {
      opacity: 0;
      transform: translateY(18px);
      transition: opacity .6s ease, transform .6s cubic-bezier(.2, .8, .2, 1);
      will-change: opacity, transform;
    }

    body.page-home .reveal.is-visible { opacity: 1; transform: none; }

    @media (prefers-reduced-motion: reduce) {
      body.page-home .reveal { opacity: 1; transform: none; transition: none }
    }

    /* =======================================================================
       GLOBAL RESPONSIVE IMPROVEMENTS
       ======================================================================= */
    @media (max-width: 480px) {
      body { padding: 16px 10px; }
      .hero { padding: 18px 16px 14px; }
      .heroTitle { font-size: 20px; }
      .heroSub { font-size: 12.5px; }
      .card { padding: 14px 12px 16px; }
      .panelHead { padding: 10px 10px 8px; }
      .panelBody { padding: 10px; }
      .panelTitle { font-size: 13.5px; }
      .btn { height: 44px; font-size: 13.5px; }
      .pill { height: 34px; font-size: 12px; padding: 0 10px; }
      .brand { font-size: 14px; gap: 8px; }
      .logo { width: 34px; height: 34px; border-radius: 10px; }
      input, select, textarea { padding: 10px 12px; font-size: 13.5px; }
    }

    @media (max-width: 360px) {
      body { padding: 12px 8px; }
      .heroTitle { font-size: 18px; }
      .card { padding: 12px 10px 14px; }
    }

    /* =======================================================================
       MOBILE ORIENTATION GUARD (Portrait only) — overlay
       Aktif untuk semua halaman selain "/"
       ======================================================================= */
    .mgOverlay{
      position:fixed;
      inset:0;
      display:none;
      align-items:center;
      justify-content:center;
      padding:18px;
      background:rgba(2,6,23,.55);
      z-index: 99999;
    }
    .mgOverlay.isOpen{ display:flex; }
    .mgModal{
      width:min(560px,100%);
      background: rgba(255,255,255,.92);
      border:1px solid rgba(226,232,240,.95);
      border-radius:22px;
      box-shadow: 0 34px 90px rgba(2,6,23,.22);
      overflow:hidden;
    }
    .mgHead{
      padding:14px 16px 12px;
      border-bottom:1px solid rgba(226,232,240,.9);
      background: linear-gradient(180deg,#fff,#f8fafc);
      display:flex;
      gap:10px;
      align-items:center;
      justify-content:space-between;
    }
    .mgTitle{
      margin:0;
      font-weight:800;
      font-size:14.5px;
      color:#0f172a;
    }
    .mgBody{
      padding:12px 16px 16px;
      color:#334155;
      font-weight:600;
      font-size:13px;
      line-height:1.45;
    }
    .mgActions{
      padding:0 16px 16px;
      display:grid;
      gap:10px;
    }
    .mgBtn{
      height:46px;
      border-radius:14px;
      border:none;
      cursor:pointer;
      font-family:inherit;
      font-weight:800;
      font-size:14.5px;
      width:100%;
    }
    .mgBtnPrimary{
      background: var(--primary);
      color:#fff;
      box-shadow: 0 12px 24px rgba(79,70,229,.25);
    }
    .mgBtnPrimary:hover{ background: var(--primaryHover); }
    .mgBtnGhost{
      background:#fff;
      border:1px solid var(--border);
      color:#0f172a;
    }
    .mgBtnGhost:hover{
      background:#f8fafc;
      box-shadow:0 10px 20px rgba(2,6,23,.06);
    }
  </style>
</head>

<body class="<?= e($bodyClass) ?>">

<?php if ($enableMobileOverlay): ?>
  <!-- Portrait only overlay -->
  <div id="mgOverlay" class="mgOverlay" aria-hidden="true">
    <div class="mgModal" role="dialog" aria-modal="true">
      <div class="mgHead">
        <p class="mgTitle">Ubah ke mode Portrait</p>
      </div>
      <div class="mgBody">
        Halaman ini hanya bisa digunakan pada <b>mode mobile portrait</b>.  
        Silakan putar layar kamu ke posisi <b>portrait</b> (bukan landscape).
      </div>
      <div class="mgActions">
        <button class="mgBtn mgBtnPrimary" type="button" onclick="location.reload()">Reload</button>
        <button class="mgBtn mgBtnGhost" type="button" onclick="location.href='/'">Ke Home</button>
      </div>
    </div>
  </div>

  <script>
  (function(){
    const overlay = document.getElementById('mgOverlay');
    if(!overlay) return;

    function shouldBlockByOrientation(){
      // Portrait only: block saat landscape (width > height)
      const w = window.innerWidth || 0;
      const h = window.innerHeight || 0;
      return (w > h);
    }

    function apply(){
      const block = shouldBlockByOrientation();
      overlay.classList.toggle('isOpen', block);
      overlay.setAttribute('aria-hidden', block ? 'false' : 'true');
      // prevent scroll behind overlay
      document.documentElement.style.overflow = block ? 'hidden' : '';
      document.body.style.overflow = block ? 'hidden' : '';
    }

    window.addEventListener('resize', apply, {passive:true});
    window.addEventListener('orientationchange', apply);
    apply();
  })();
  </script>
<?php endif; ?>

  <div class="wrap">
