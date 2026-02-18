<?php
use function App\Lib\e;

/**
 * ✅ Ganti path logo kamu di sini (samakan dengan dashboard)
 */
$brandLogo = '/asset/brand/fastpaytrack-logo2.png';
?>

<style>
/* =========================================================
   SETTINGS THEME (Scoping ke body.page-settings)
   - FULL SCREEN (tanpa ruang kosong tepi)
   - Tidak mengubah ukuran font/icon/logo
   ========================================================= */
body.page-settings{
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

/* ✅ wrap full (hilangkan padding default layout global) */
body.page-settings .wrap{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
  padding:0 !important;
}

/* ✅ container full */
body.page-settings .dashContainer{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
}

/* ✅ shell jadi “card utama” full layar */
body.page-settings .shell{
  width:100% !important;
  min-height:100vh;
  border-radius:0 !important;      /* biar nempel ke tepi layar */
  overflow:hidden;
  background: rgba(255,255,255,.70);
  backdrop-filter: blur(10px);
  border:0 !important;             /* hilangkan garis tepi luar */
  box-shadow:none !important;      /* biar bersih seperti full screen */
}

/* Hero */
body.page-settings .hero{
  position:relative;
  color:#fff;
  padding:20px 20px 18px;
  background:
    radial-gradient(900px 420px at 12% 18%, rgba(37,99,235,.20), transparent 60%),
    radial-gradient(760px 420px at 92% 18%, rgba(34,211,238,.18), transparent 60%),
    linear-gradient(135deg, var(--bg1), var(--bg2));
}
body.page-settings .hero:before{
  content:"";
  position:absolute;
  inset:-60px -60px auto auto;
  width:260px;height:260px;
  background:rgba(255,255,255,.16);
  border-radius:50%;
}

/* Card bawah */
body.page-settings .card{
  background: rgba(255,255,255,.70);
  padding:16px 16px 18px;
  border-radius:22px 22px 0 0;
}

/* ✅ di desktop boleh tetap “rapih” tapi full screen
   (content tetap center, tanpa bikin ada ruang kosong tepi luar) */
@media (min-width: 980px){
  body.page-settings .card{
    padding:18px 18px 22px;
  }
  body.page-settings .hero{
    padding:22px 22px 18px;
  }
}

/* Panel clean (biar 1 layer saja, tidak bertumpuk) */
.panelClean{
  border:none !important;
  box-shadow:none !important;
  background:transparent !important;
  overflow:visible !important;
  border-radius:0 !important;
}
.panelClean .panelBody{ padding:0 !important; }

/* =========================================================
   ✅ Semua teks tidak bold (patokan dashboard)
   ========================================================= */
body.page-settings,
body.page-settings p,
body.page-settings span,
body.page-settings a,
body.page-settings label,
body.page-settings input,
body.page-settings select,
body.page-settings button,
body.page-settings small{
  font-weight: 500 !important;
}
body.page-settings .heroTitle{ font-weight: 600 !important; }
body.page-settings .heroSub{ font-weight: 500 !important; }
body.page-settings .btn{ font-weight: 600 !important; }

/* =========================================================
   TOP ROW (logo kiri, dashboard icon kanan)
   ========================================================= */
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

/* ✅ Dashboard icon button: TANPA cover lingkaran/kotak */
.dashTopBtn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:auto;height:auto;
  padding:2px;
  border:none;
  background:transparent;
  color:#fff;
  text-decoration:none;
  margin-left:auto;
  opacity:.95;
}
.dashTopBtn:hover{ opacity:1; }
.dashTopBtn svg{ width:20px; height:20px; display:block; }

/* =========================================================
   HERO TITLES
   ========================================================= */
body.page-settings .heroTitle{
  margin:14px 0 6px;
  font-size:26px;
  letter-spacing:-.02em;
  position:relative;
  z-index:1;
}
body.page-settings .heroSub{
  margin:0;
  opacity:.92;
  font-size:13.5px;
  position:relative;
  z-index:1;
}

/* =========================================================
   SETTINGS CARD (menu) -> 1 card clean seperti dashboard
   ========================================================= */
.setCard{
  border: none !important;
  box-shadow: none !important;
  background: transparent !important;
  padding: 0 !important;
}

/* list menu */
.setList{
  display:grid;
  gap:10px;
  max-width:560px;
  margin:0 auto;
}

/* ✅ item style seperti list settings (icon kiri + text + chevron kanan) */
.setItem{
  height:56px;
  border-radius:16px;
  border:1px solid var(--border);
  background:#fff;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:0 14px;
  text-decoration:none;
  color:#0f172a;
  transition: background .12s ease, transform .05s ease, box-shadow .12s ease;
}
.setItem:hover{
  background:#f8fafc;
  box-shadow:0 10px 20px rgba(2,6,23,.06);
}
.setItem:active{ transform: translateY(1px); }

.setLeft{
  display:flex;
  align-items:center;
  gap:12px;
  min-width:0;
}

/* ✅ icon TANPA cover */
.setIcon{
  width:22px;height:22px;
  display:grid;
  place-items:center;
  color:#3b82f6;
  flex:0 0 auto;
}
.setIcon svg{ width:22px;height:22px; display:block; }

.setText{
  font-size:14.5px;
  font-weight: 600 !important;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

/* chevron kanan */
.setChevron{
  width:18px;height:18px;
  color:#94a3b8;
  flex:0 0 auto;
}
.setChevron svg{ width:18px;height:18px; display:block; }

/* tombol logout beda warna tapi tetap 1 gaya */
.setItemLogout{
  background: var(--primary);
  border-color: transparent;
  color:#fff;
  box-shadow:0 12px 24px rgba(79,70,229,.22);
}
.setItemLogout:hover{ background: var(--primaryHover); }
.setItemLogout .setIcon{ color:#fff; }
.setItemLogout .setChevron{ color: rgba(255,255,255,.92); }

/* note */
.setNote{
  margin-top:8px;
  font-size:12.5px;
  color:#64748b;
  line-height:1.4;
}

/* responsif */
@media (max-width:520px){
  .setList{ gap:9px; }
  .setItem{ height:54px; padding:0 12px; }
  .setText{ font-size:14px; }
}
</style>

<script>
(function(){ try{ document.body.classList.add('page-settings'); }catch(e){} })();
</script>

<div class="dashContainer">
  <div class="shell">

    <div class="hero">
      <div class="topbar">
        <div class="brandRow">
          <a class="dashBrand" href="/" aria-label="FastPayTrack Home">
            <img src="<?= e($brandLogo) ?>" alt="FASTPAYTRACK">
          </a>

          <!-- ✅ Icon Dashboard kanan atas (tanpa cover) -->
          <a class="dashTopBtn" href="/dashboard" title="Dashboard" aria-label="Dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 11l9-8 9 8"></path>
              <path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"></path>
            </svg>
          </a>
        </div>
      </div>


      <p class="heroSub"></p>
    </div>

    <div class="card">
      <div class="panelClean">
        <div class="panelBody">

          <div class="setCard">
            <div class="setList">

              <a class="setItem" href="/profile" aria-label="Profile Setting">
                <div class="setLeft">
                  <span class="setIcon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 21a8 8 0 0 0-16 0"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                  </span>
                  <span class="setText">Profile Setting</span>
                </div>
                <span class="setChevron" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"></path>
                  </svg>
                </span>
              </a>

              <a class="setItem" href="/pin" aria-label="Security Setting">
                <div class="setLeft">
                  <span class="setIcon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z"></path>
                      <path d="M9 12l2 2 4-4"></path>
                    </svg>
                  </span>
                  <span class="setText">Security Setting</span>
                </div>
                <span class="setChevron" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"></path>
                  </svg>
                </span>
              </a>

              <a class="setItem" href="/settings/security" aria-label="Manage Device">
                <div class="setLeft">
                  <span class="setIcon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="7" y="2" width="10" height="20" rx="2" ry="2"></rect>
                      <path d="M11 18h2"></path>
                    </svg>
                  </span>
                  <span class="setText">Manage Device</span>
                </div>
                <span class="setChevron" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"></path>
                  </svg>
                </span>
              </a>

              <a class="setItem" href="/settings/notifications" aria-label="Notification Setting">
                <div class="setLeft">
                  <span class="setIcon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"></path>
                      <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                  </span>
                  <span class="setText">Notification Setting</span>
                </div>
                <span class="setChevron" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"></path>
                  </svg>
                </span>
              </a>

              <a class="setItem" href="/settings/support" aria-label="Customer Support">
                <div class="setLeft">
                  <span class="setIcon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <path d="M9.09 9a3 3 0 0 1 5.82 1c0 2-3 2-3 4"></path>
                      <path d="M12 17h.01"></path>
                    </svg>
                  </span>
                  <span class="setText">Customer Support</span>
                </div>
                <span class="setChevron" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"></path>
                  </svg>
                </span>
              </a>

              <a class="setItem setItemLogout" href="/logout" aria-label="Logout">
                <div class="setLeft">
                  <span class="setIcon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                      <path d="M16 17l5-5-5-5"></path>
                      <path d="M21 12H9"></path>
                    </svg>
                  </span>
                  <span class="setText">Logout</span>
                </div>
                <span class="setChevron" aria-hidden="true">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"></path>
                  </svg>
                </span>
              </a>

              <div class="setNote">
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>
