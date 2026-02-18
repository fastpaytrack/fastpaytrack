<?php
use function App\Lib\e;

/** ✅ samakan dengan dashboard */
$brandLogo = '/asset/brand/fastpaytrack-logo2.png';
?>

<style>
/* =========================================================
   TOPUP THEME (Scoping ke body.page-topup)
   - Konsisten dengan settings/show/transfer
   ========================================================= */
body.page-topup{
  padding:0 !important;
  margin:0 !important;
  background:
    radial-gradient(900px 520px at 12% 12%, rgba(99,102,241,.18), transparent 60%),
    radial-gradient(900px 520px at 88% 18%, rgba(34,211,238,.18), transparent 60%),
    radial-gradient(900px 520px at 80% 90%, rgba(147,51,234,.10), transparent 55%),
    #ffffff !important;
  color:#0b1220;
  min-height:100vh;
}

/* ✅ wrap full */
body.page-topup .wrap{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
  padding:0 !important;
}

/* ✅ container full */
body.page-topup .dashContainer{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
}

/* ✅ shell full screen */
body.page-topup .shell{
  width:100% !important;
  min-height:100vh;
  border-radius:0 !important;
  overflow:hidden;
  background: rgba(255,255,255,.70);
  backdrop-filter: blur(10px);
  border:0 !important;
  box-shadow:none !important;
}

/* =========================================================
   Typography konsisten
   ========================================================= */
body.page-topup,
body.page-topup p,
body.page-topup span,
body.page-topup a,
body.page-topup label,
body.page-topup input,
body.page-topup select,
body.page-topup button,
body.page-topup small{
  font-weight: 500 !important;
}
body.page-topup .heroTitle{ font-weight:600 !important; }
body.page-topup .panelTitle{ font-weight:600 !important; }
body.page-topup .btn{ font-weight:600 !important; }

/* =========================================================
   Hero (header)
   ========================================================= */
body.page-topup .hero{
  position:relative;
  color:#fff;
  padding:20px 16px 18px;
  background:
    radial-gradient(900px 420px at 12% 18%, rgba(37,99,235,.20), transparent 60%),
    radial-gradient(760px 420px at 92% 18%, rgba(34,211,238,.18), transparent 60%),
    linear-gradient(135deg, var(--bg1), var(--bg2));
}
body.page-topup .hero:before{
  content:"";
  position:absolute;
  inset:-60px -60px auto auto;
  width:260px;height:260px;
  background:rgba(255,255,255,.16);
  border-radius:50%;
}

/* top row: brand kiri, icons kanan */
.brandRow{
  width:100%;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
  position:relative;
  z-index:1;
}
.dashBrand{
  display:flex;
  align-items:center;
  gap:12px;
  text-decoration:none;
  color:#fff;
  min-width:0;
}
.dashBrand img{
  height:28px;
  width:auto;
  display:block;
  filter: drop-shadow(0 10px 18px rgba(2,6,23,.18));
}
.topIcons{
  display:flex;
  align-items:center;
  gap:14px;
  margin-left:auto;
}
.topIconBtn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:auto;height:auto;
  padding:2px;
  border:none;
  background:transparent;
  color:#fff;
  text-decoration:none;
  opacity:.95;
}
.topIconBtn:hover{ opacity:1; }
.topIconBtn svg{ width:20px;height:20px; display:block; }

/* title/sub */
body.page-topup .heroTitle{
  margin:14px 0 6px;
  font-size:26px;
  letter-spacing:-.02em;
  position:relative;
  z-index:1;
}
body.page-topup .heroSub{
  margin:0;
  opacity:.92;
  font-size:13.5px;
  position:relative;
  z-index:1;
}

/* =========================================================
   Card area
   ========================================================= */
body.page-topup .card{
  background: rgba(255,255,255,.70);
  padding:16px 16px 18px;
  border-radius:22px 22px 0 0;
}
@media (min-width: 980px){
  body.page-topup .card{ padding:18px 18px 22px; }
  body.page-topup .hero{ padding:22px 22px 18px; }
}

/* panel */
.panel{
  border:1px solid var(--border);
  border-radius:20px;
  background:#fff;
  box-shadow:0 10px 26px rgba(2,6,23,.04);
  overflow:hidden;
  max-width:560px;
  margin:0 auto;
}
.panelHead{
  padding:14px;
  border-bottom:1px solid var(--border);
}
.panelTitle{ margin:0; font-size:15px; color:#0f172a; }
.panelSub{ margin:6px 0 0; font-size:12.5px; color:var(--muted); line-height:1.35; }
.panelBody{ padding:14px; }

/* fields */
.field{ margin-top:12px; display:flex; flex-direction:column; gap:8px; }
.field label{ font-size:12.5px; font-weight:600 !important; color:#0f172a; }
.field input{
  height:46px;
  border-radius:16px;
  border:1px solid var(--border);
  padding:0 14px;
  font-size:14px;
  font-weight:500 !important;
  outline:none;
  background:#fff;
}
.field input:focus{
  border-color: rgba(79,70,229,.55);
  box-shadow: 0 0 0 4px rgba(79,70,229,.12);
}

.hint{
  color:var(--muted);
  font-size:12.5px;
  line-height:1.4;
  margin-top:8px;
}

/* quick amount -> seperti show (grid 2 kolom) */
.quickGrid{
  margin-top:10px;
  display:grid;
  grid-template-columns: repeat(2, minmax(0,1fr));
  gap:10px;
}
.quickBtn{
  height:44px;
  border-radius:16px;
  border:1px solid var(--border);
  background:#fff;
  cursor:pointer;
  font-weight:600 !important;
  font-size:13.5px;
}
.quickBtn:hover{
  background:#f8fafc;
  box-shadow: 0 10px 20px rgba(2,6,23,.06);
}

/* buttons -> samakan feel show/transfer */
.btnBigPrimary{
  width:100%;
  height:52px;
  border-radius:16px;
  border:1px solid #1d4ed8;
  background:#2563eb;
  color:#fff;
  font-weight:600 !important;
  font-size:15px;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  margin-top:14px;
}
.btnBigPrimary:hover{ filter:brightness(.97); }

.btnBigGhost{
  width:100%;
  height:50px;
  border-radius:16px;
  border:1px solid var(--border);
  background:#fff;
  color:#0f172a;
  font-weight:600 !important;
  font-size:15px;
  cursor:pointer;
  text-decoration:none;
  display:flex;
  align-items:center;
  justify-content:center;
  margin-top:10px;
}
.btnBigGhost:hover{ background:#f8fafc; }

/* spacing kecil */
@media (max-width:520px){
  body.page-topup .card{ padding:16px 14px 18px; }
}
</style>

<script>
(function(){ try{ document.body.classList.add('page-topup'); }catch(e){} })();
</script>

<div class="dashContainer">
  <div class="shell">

    <div class="hero">
      <div class="topbar">
        <div class="brandRow">
          <a class="dashBrand" href="/" aria-label="FastPayTrack Home">
            <img src="<?= e($brandLogo) ?>" alt="FASTPAYTRACK">
          </a>

          <div class="topIcons" aria-label="Top actions">
            <!-- HOME -->
            <a class="topIconBtn" href="/dashboard" title="Home" aria-label="Home">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11l9-8 9 8"></path>
                <path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"></path>
              </svg>
            </a>

            <!-- BACK -->
            <a class="topIconBtn" href="/profile" title="Back" aria-label="Back">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"></path>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <div class="heroTitle">Topup Saldo</div>
      <p class="heroSub">Isi saldo wallet menggunakan Stripe (TEST).</p>
    </div>

    <div class="card">
      <div class="panel">
        <div class="panelHead">
          <p class="panelTitle">Nominal Topup</p>
          <p class="panelSub">Custom nominal • minimal Rp 10.000</p>
        </div>

        <div class="panelBody">
          <form method="POST" action="/topup" id="topupForm">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

            <div class="field">
              <label>Masukkan nominal (IDR)</label>
              <input id="amountDisplay" type="text" inputmode="numeric" placeholder="contoh: 50.000" autocomplete="off" />
              <input id="amountRaw" name="amount_idr" type="hidden" />
              <div class="hint">Minimal Rp 10.000 • Maksimal Rp 10.000.000</div>
            </div>

            <div class="quickGrid" aria-label="Nominal cepat">
              <button class="quickBtn" type="button" data-amt="10000">Rp 10.000</button>
              <button class="quickBtn" type="button" data-amt="20000">Rp 20.000</button>
              <button class="quickBtn" type="button" data-amt="50000">Rp 50.000</button>
              <button class="quickBtn" type="button" data-amt="100000">Rp 100.000</button>
              <button class="quickBtn" type="button" data-amt="200000">Rp 200.000</button>
            </div>

            <button class="btnBigPrimary" type="submit">
              Topup via Stripe
            </button>

            <div class="hint">
              Setelah bayar di Stripe, saldo otomatis bertambah lewat webhook Stripe.
            </div>

            <a class="btnBigGhost" href="/topup/history">Lihat Riwayat Topup</a>
            <a class="btnBigGhost" href="/dashboard">Back to Dashboard</a>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function(){
  const display = document.getElementById('amountDisplay');
  const raw = document.getElementById('amountRaw');
  const form = document.getElementById('topupForm');

  function onlyDigits(s){ return (s || '').replace(/[^\d]/g, ''); }
  function formatID(n){
    if (!n) return '';
    return n.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function setAmount(numStr){
    const digits = onlyDigits(numStr);
    if (raw) raw.value = digits;
    if (display) display.value = formatID(digits);
  }

  if (display) display.addEventListener('input', () => setAmount(display.value));

  document.querySelectorAll('[data-amt]').forEach(btn => {
    btn.addEventListener('click', () => setAmount(btn.getAttribute('data-amt')));
  });

  if (form) {
    form.addEventListener('submit', (e) => {
      const v = parseInt((raw && raw.value) ? raw.value : '0', 10);
      if (v < 10000){
        e.preventDefault();
        alert('Minimal topup Rp 10.000');
        return;
      }
      if (v > 10000000){
        e.preventDefault();
        alert('Maksimal topup Rp 10.000.000');
        return;
      }
    });
  }
})();
</script>
