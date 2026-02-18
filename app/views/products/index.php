<?php
use function App\Lib\e;

$q = (string)($q ?? '');

/**
 * ✅ Samakan logo dengan dashboard
 */
$brandLogo = '/asset/brand/fastpaytrack-logo2.png';

$products = $products ?? [];
?>

<style>
  /* =========================================================
     PRODUCTS THEME (Scoping ke body.page-products)
     - FULL SCREEN (tanpa ruang kosong tepi)
     - Header simpel: logo kiri, BACK kanan (ke dashboard)
     - Product list lebih flat (tidak banyak layer menumpuk)
     ========================================================= */
  body.page-products{
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

  /* ✅ FULLSCREEN: hilangkan padding bawaan layout */
  body.page-products .wrap{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
    padding:0 !important;
  }

  /* container full */
  body.page-products .dashContainer{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
  }

  /* ✅ shell flush ke tepi layar */
  body.page-products .shell{
    width:100% !important;
    min-height:100vh;
    border-radius:0 !important;
    overflow:hidden;
    background: rgba(255,255,255,.70);
    backdrop-filter: blur(10px);
    border:0 !important;
    box-shadow:none !important;
  }

  /* Hero */
  body.page-products .hero{
    position:relative;
    color:#fff;
    padding:20px 16px 18px;
    background:
      radial-gradient(900px 420px at 12% 18%, rgba(37,99,235,.20), transparent 60%),
      radial-gradient(760px 420px at 92% 18%, rgba(34,211,238,.18), transparent 60%),
      linear-gradient(135deg, var(--bg1), var(--bg2));
  }
  body.page-products .hero:before{
    content:"";
    position:absolute;
    inset:-60px -60px auto auto;
    width:260px;height:260px;
    background:rgba(255,255,255,.16);
    border-radius:50%;
  }

  /* area konten bawah */
  body.page-products .card{
    background: rgba(255,255,255,.92);
    padding:14px 14px 18px;
    border-radius:0 !important;
  }

  @media (min-width: 980px){
    body.page-products .card{ padding:18px 18px 22px; }
    body.page-products .hero{ padding:22px 22px 18px; }
  }

  /* ✅ panel clean supaya tidak ada layer bertumpuk */
  .panelClean{
    border:none !important;
    box-shadow:none !important;
    background:transparent !important;
    overflow:visible !important;
    border-radius:0 !important;
  }
  .panelClean .panelBody{ padding:0 !important; }

  /* ✅ semua teks konsisten (tidak ubah ukuran font/icon/logo) */
  body.page-products,
  body.page-products p,
  body.page-products span,
  body.page-products a,
  body.page-products label,
  body.page-products input,
  body.page-products select,
  body.page-products button,
  body.page-products small{
    font-weight: 500 !important;
  }
  body.page-products .pName{ font-weight: 600 !important; }

  /* =========================================================
     TOP ROW (logo kiri, BACK kanan)
     ========================================================= */
  .brandRow{
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:nowrap;
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

  /* ✅ back button: tanpa cover */
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
    margin-left:auto;
    opacity:.95;
  }
  .topIconBtn:hover{ opacity:1; }
  .topIconBtn svg{ width:22px; height:22px; display:block; }

  /* =========================================================
     SEARCH ROW
     ========================================================= */
  .dashSearchRow{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
    margin: 2px 0 10px;
  }
  .dashSearchWrap{ flex:1; min-width:240px; position:relative; }
  .dashSearchInput{
    width:100%;
    height:46px;
    border-radius:16px;
    border:1px solid var(--border);
    padding:0 44px 0 14px;
    font-size:14px;
    background:#fff;
    outline:none;
  }
  .dashSearchInput:focus{
    border-color: rgba(79,70,229,.55);
    box-shadow: 0 0 0 4px rgba(79,70,229,.12);
  }
  .dashSearchBtn{
    position:absolute; right:10px; top:50%; transform:translateY(-50%);
    width:32px;height:32px; border-radius:12px;
    border:1px solid transparent; background:transparent;
    cursor:pointer; color:#475569;
    font-weight: 600 !important;
  }
  .dashSearchBtn:hover{ background:#f1f5f9; border-color:#e2e8f0; }
  .dashCount{ color:var(--muted); font-size:12.5px; }

  @media(max-width:520px){
    .dashCount{ width:100%; }
  }

  /* =========================================================
     PRODUCT GRID - dibuat lebih simpel (lebih flat)
     ========================================================= */
  .prodGrid{
    margin-top:10px;
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:12px;
  }
  @media(min-width:720px){
    .prodGrid{ grid-template-columns: repeat(3, minmax(0, 1fr)); gap:14px; }
  }
  @media(min-width:1040px){
    .prodGrid{ grid-template-columns: repeat(4, minmax(0, 1fr)); gap:14px; }
  }

  /* ✅ card lebih flat (nggak banyak layer) */
  .prodCard{
    display:flex;
    flex-direction:column;
    text-decoration:none;
    color:inherit;
    border:1px solid rgba(226,232,240,.95);
    border-radius:16px;
    background:#fff;
    overflow:hidden;
    box-shadow: 0 6px 16px rgba(2,6,23,.04); /* lebih soft */
    transition: transform .10s ease, box-shadow .10s ease;
  }
  .prodCard:hover{
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(2,6,23,.06);
  }

  /* ✅ thumbnail dibuat 1 layer saja */
  .prodThumb{
    height:92px;
    background:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    border-bottom:1px solid rgba(226,232,240,.85);
  }
  .prodThumb img{
    width:100%;
    height:100%;
    object-fit:contain;
    transform: scale(.92);
    display:block;
  }
  .prodThumbFallback{
    font-weight:600 !important;
    color:#64748b;
  }

  .prodBody{
    padding:12px;
    display:flex;
    flex-direction:column;
    gap:8px;
    min-width:0;
  }

  .pName{
    margin:0;
    font-size:14.5px;
    line-height:1.2;
    color:#0f172a;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
  }

  .pMeta{
    display:flex;
    align-items:center;
    gap:8px;
    color:var(--muted);
    font-size:12.5px;
    line-height:1.2;
  }
  .pDot{
    width:6px;height:6px;border-radius:999px;
    background:#22c55e;
    flex:0 0 auto;
  }

  .pSub{
    color:var(--muted);
    font-size:12.5px;
    line-height:1.35;
    min-height: 34px;
    overflow:hidden;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
  }

  .noteEmpty{
    margin-top:12px;
    font-weight:500;
    color:var(--muted);
  }
</style>

<script>
(function(){
  try{ document.body.classList.add('page-products'); }catch(e){}
})();
</script>

<div class="dashContainer">
  <div class="shell">

    <div class="hero">
      <div class="brandRow">
        <a class="dashBrand" href="/" aria-label="FastPayTrack Home">
          <img src="<?= e($brandLogo) ?>" alt="FASTPAYTRACK">
        </a>

        <!-- ✅ Back icon (klik -> dashboard) -->
        <a class="topIconBtn" href="/dashboard" title="Kembali" aria-label="Kembali ke Dashboard">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6"/><path d="M21 12H9"/>
          </svg>
        </a>
      </div>

      <!-- ✅ sengaja kosong (simpel seperti settings/show) -->
      <div style="height:2px;"></div>
    </div>

    <div class="card">
      <div class="panelClean">
        <div class="panelBody">

          <!-- SEARCH -->
          <div class="dashSearchRow">
            <div class="dashSearchWrap">
              <input id="productSearch" class="dashSearchInput" type="text"
                     value="<?= e($q) ?>"
                     placeholder="Cari produk: Steam, Google Play, DANA, Netflix..." />
              <button id="clearSearch" class="dashSearchBtn" type="button" aria-label="Clear">✕</button>
            </div>
            <div class="dashCount" id="searchCount">Menampilkan semua</div>
          </div>

          <?php if (empty($products)): ?>
            <div class="noteEmpty">Tidak ada produk ditemukan.</div>
          <?php else: ?>
            <div class="prodGrid" id="prodGrid">
              <?php foreach ($products as $p):
                $pid = (int)($p['id'] ?? 0);
                $name = (string)($p['name'] ?? '');
                $img  = (string)($p['image_url'] ?? '');
                $cat  = (string)($p['category'] ?? '');
                $desc = (string)($p['description'] ?? '');

                $detailUrl = '/product?id=' . $pid;

                $dataSearch = strtolower(trim($name . ' ' . $cat . ' ' . $desc));
                $fallback = strtoupper(mb_substr(trim($name ?: 'P'), 0, 1));
              ?>
                <a class="prodCard productCard"
                   href="<?= e($detailUrl) ?>"
                   aria-label="<?= e($name ?: 'Product') ?>"
                   data-search="<?= e($dataSearch) ?>">

                  <div class="prodThumb">
                    <?php if ($img): ?>
                      <img src="<?= e($img) ?>" alt="<?= e($name) ?>">
                    <?php else: ?>
                      <div class="prodThumbFallback"><?= e($fallback) ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="prodBody">
                    <p class="pName"><?= e($name) ?></p>

                    <div class="pMeta">
                      <span class="pDot" aria-hidden="true"></span>
                      <span>Globally redeemable</span>
                    </div>

                    <div class="pSub">
                      <?php if ($cat !== '' && $desc !== ''): ?>
                        <?= e($cat) ?> • <?= e($desc) ?>
                      <?php elseif ($cat !== ''): ?>
                        <?= e($cat) ?>
                      <?php elseif ($desc !== ''): ?>
                        <?= e($desc) ?>
                      <?php else: ?>
                        Voucher digital & layanan top up.
                      <?php endif; ?>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>

            <div id="noResult" class="noteEmpty" style="display:none;">
              Tidak ada produk yang cocok.
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function(){
  // client-side search + count
  const input = document.getElementById('productSearch');
  const clearBtn = document.getElementById('clearSearch');
  const cards = Array.from(document.querySelectorAll('.productCard'));
  const countEl = document.getElementById('searchCount');
  const noResult = document.getElementById('noResult');

  function update(){
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
  if (clearBtn) clearBtn.addEventListener('click', () => { input.value=''; input.focus(); update(); });
  update();
})();
</script>
