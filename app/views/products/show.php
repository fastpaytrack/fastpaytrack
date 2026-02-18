<?php
use function App\Lib\e;
use function App\Lib\money_idr;

$cart = $_SESSION['cart'] ?? [];
$cartCount = 0;
foreach ($cart as $it) $cartCount += (int)($it['qty'] ?? 1);

$p = $product ?? null;

$name = $p ? (string)($p['name'] ?? '') : '';
$img  = $p ? (string)($p['image_url'] ?? '') : '';
$cat  = $p ? (string)($p['category'] ?? '') : '';
$desc = $p ? (string)($p['description'] ?? '') : '';

$pid  = $p ? (int)($p['id'] ?? 0) : 0;
$denoms  = $denoms ?? [];

/**
 * ✅ Samakan dengan dashboard (brand tidak hilang)
 */
$brandLogo = '/asset/brand/fastpaytrack-logo2.png';

/** Denom buttons (sanitize) */
$btnDenoms = [];
foreach ($denoms as $amt) {
  $a = (int)$amt;
  if ($a > 0) $btnDenoms[] = $a;
}
?>

<style>
  /* =========================================================
     PRODUCT SHOW - FULL SCREEN (samakan feel seperti Settings)
     - tanpa ruang kosong tepi
     - tidak ubah ukuran font/icon/logo global
     ========================================================= */
     
     /* ✅ Rapihin jarak Total ↔ tombol (khusus page product show) */
body.page-product-show .sumTotal{
  margin-top: 10px;     /* jarak dari row sebelumnya */
  margin-bottom: 14px;  /* ✅ ini yang bikin tombol gak nempel */
}

body.page-product-show .btnBigPrimary{
  margin-top: 0;        /* biar jaraknya patokan dari sumTotal */
}

  body.page-product-show{
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

  body.page-product-show .wrap{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
    padding:0 !important;
  }

  .psContainer{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
  }

  .shell{
    width:100% !important;
    min-height:100vh;
    border-radius:0 !important;
    overflow:hidden;
    background: rgba(255,255,255,.70);
    backdrop-filter: blur(10px);
    border:0 !important;
    box-shadow:none !important;
  }

  .hero{
    position:relative;
    color:#fff;
    padding:20px 20px 18px;
    background:
      radial-gradient(900px 420px at 12% 18%, rgba(37,99,235,.20), transparent 60%),
      radial-gradient(760px 420px at 92% 18%, rgba(34,211,238,.18), transparent 60%),
      linear-gradient(135deg, var(--bg1), var(--bg2));
  }
  .hero:before{
    content:"";
    position:absolute;
    inset:-60px -60px auto auto;
    width:260px;height:260px;
    background:rgba(255,255,255,.16);
    border-radius:50%;
  }

  .card{
    background: rgba(255,255,255,.70);
    padding:16px 16px 18px;
    border-radius:22px 22px 0 0;
  }

  @media (min-width: 980px){
    .hero{ padding:22px 22px 18px; }
    .card{ padding:18px 18px 22px; }
  }

  /* =========================================================
     Typography weight (ikut pola dashboard/settings)
     ========================================================= */
  body.page-product-show,
  body.page-product-show p,
  body.page-product-show span,
  body.page-product-show a,
  body.page-product-show label,
  body.page-product-show input,
  body.page-product-show select,
  body.page-product-show button,
  body.page-product-show small{
    font-weight:500 !important;
  }

  /* =========================================================
     Header row: brand kiri, icon kanan (tanpa cover)
     ========================================================= */
  .brandRow{
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
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
    position:relative;
  }
  .topIconBtn:hover{ opacity:1; }
  .topIconBtn svg{ width:22px; height:22px; display:block; }

  .cartBadge{
    position:absolute;
    top:-6px; right:-8px;
    background:#ef4444;
    color:#fff;
    font-size:11px;
    font-weight:600 !important;
    border-radius:999px;
    padding:2px 6px;
    line-height:1;
    box-shadow: 0 10px 18px rgba(239,68,68,.28);
  }

  /* =========================================================
     Content panels
     ========================================================= */
  .detailWrap{
    display:grid;
    gap:12px;
    grid-template-columns: 1fr;
  }
  @media(min-width: 960px){
    .detailWrap{
      grid-template-columns: 1.15fr .85fr;
      align-items:start;
    }
  }

  .panel{
    border:1px solid var(--border);
    border-radius:20px;
    background:#fff;
    box-shadow:0 10px 26px rgba(2,6,23,.04);
    overflow:hidden;
  }

  /* LEFT: media + info (kurangi layer menumpuk) */
  .mediaTop{
    padding:14px;
    background: linear-gradient(180deg, rgba(99,102,241,.10), rgba(255,255,255,0));
    border-bottom:1px solid var(--border);
  }

  /* ✅ FIX: hilangkan “card/frame” tambahan di dalam gambar */
  .mediaFrame{
    height:260px;
    border-radius:16px;
    background: transparent;
    border:0;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
  }
  @media(min-width: 960px){
    .mediaFrame{ height:340px; }
  }
  .mediaFrame img{
    width:100%;
    height:100%;
    object-fit:contain;
    display:block;
    transform: scale(.96);
  }

  .mediaBody{ padding:14px; }
  .pTitle{
    margin:0;
    font-size:18px;
    font-weight:600 !important;
    color:#0f172a;
    line-height:1.2;
  }
  .pMeta{
    margin-top:10px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    color:var(--muted);
    font-size:12.5px;
    align-items:center;
  }
  .metaDot{
    width:7px;height:7px;border-radius:999px;background:#22c55e;display:inline-block;
  }
  .pDesc{
    margin-top:10px;
    color:var(--muted);
    font-size:13px;
    line-height:1.5;
  }

  /* RIGHT: choose amount */
  .buyHead{
    padding:14px 14px 10px;
    border-bottom:1px solid var(--border);
  }
  .buyTitle{
    margin:0;
    font-size:15px;
    font-weight:600 !important;
    color:#0f172a;
  }
  .stockRow{
    margin-top:8px;
    display:flex;
    align-items:center;
    gap:8px;
    color:var(--muted);
    font-size:12.5px;
  }
  .stockDot{
    width:7px;height:7px;border-radius:999px;background:#22c55e;display:inline-block;
  }

  .buyBody{ padding:14px; }

  .amountGrid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:10px;
    margin-top:10px;
  }
  @media(min-width: 960px){
    .amountGrid{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
  }

  .amtBtn{
    height:44px;
    border-radius:14px;
    border:1px solid var(--border);
    background:#fff;
    font-weight:600 !important;
    font-size:13.5px;
    color:#0f172a;
    cursor:pointer;
    transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
  }
  .amtBtn:hover{
    transform: translateY(-1px);
    box-shadow:0 10px 22px rgba(2,6,23,.06);
  }
  .amtBtnActive{
    border-color: rgba(37,99,235,.55);
    box-shadow: 0 0 0 4px rgba(37,99,235,.12);
  }

  .divider{
    margin:14px 0;
    border-top:1px dashed rgba(148,163,184,.55);
  }

  .sumRow{
    display:flex;
    align-items:baseline;
    justify-content:space-between;
    gap:12px;
    color:var(--muted);
    font-size:12.5px;
    margin-top:8px;
  }
  .sumTotal{
    display:flex;
    align-items:baseline;
    justify-content:space-between;
    gap:12px;
    margin-top:10px;
  }
  .sumTotal .label{
    color:#0f172a;
    font-size:13px;
    font-weight:600 !important;
  }
  .sumTotal .val{
    font-family: ui-monospace, Menlo, Consolas, monospace;
    font-size:18px;
    font-weight:600 !important;
    color:#16a34a;
  }

  /* =========================================================
     ✅ Buttons: samakan dengan halaman Settings (setItem)
     - tinggi 56px, radius 16px, font 14.5px, weight 600
     ========================================================= */
  .btnSetPrimary,
  .btnSetGhost{
    width:100%;
    height:56px;
    border-radius:16px;
    padding:0 14px;
    font-size:14.5px;
    font-weight:600 !important;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    text-decoration:none;
  }

  .btnSetPrimary{
    border:1px solid transparent;
    background: var(--primary);
    color:#fff;
    box-shadow:0 12px 24px rgba(79,70,229,.22);
  }
  .btnSetPrimary:hover{ background: var(--primaryHover); }
  .btnSetPrimary:disabled{
    opacity:.55;
    cursor:not-allowed;
    box-shadow:none;
  }

  .btnSetGhost{
    border:1px solid var(--border);
    background:#fff;
    color:#0f172a;
    margin-top:10px;
  }
  .btnSetGhost:hover{
    background:#f8fafc;
    box-shadow:0 10px 20px rgba(2,6,23,.06);
  }

  .hint{
    margin-top:10px;
    color:var(--muted);
    font-size:12.5px;
    line-height:1.45;
  }
</style>

<script>
(function(){
  try{ document.body.classList.add('page-product-show'); }catch(e){}
})();
</script>

<div class="psContainer">
  <div class="shell">

    <div class="hero">
      <div class="brandRow">
        <a class="dashBrand" href="/products" aria-label="FASTPAYTRACK">
          <img src="<?= e($brandLogo) ?>" alt="FASTPAYTRACK" onerror="this.style.display='none';">
        </a>

        <div class="topIcons" aria-label="Actions">
          <a class="topIconBtn" href="/products" title="Kembali" aria-label="Kembali">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 18l-6-6 6-6"/><path d="M21 12H9"/>
            </svg>
          </a>

          <a class="topIconBtn" href="/checkout" title="Cart" aria-label="Cart">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 6h15l-2 9H7L6 6Z"/><path d="M6 6 5 3H2"/>
              <path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
              <path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
            </svg>
            <?php if ($cartCount > 0): ?><span class="cartBadge"><?= (int)$cartCount ?></span><?php endif; ?>
          </a>
        </div>
      </div>

      <!-- ✅ Sesuai request: hilangkan title & sub di hero (jadi simpel seperti settings) -->
      <p style="margin:0; height:6px;"></p>
    </div>

    <div class="card">

      <?php if (!$p): ?>
        <div class="panel" style="padding:14px;">
          <div style="font-weight:600;">Produk tidak ditemukan / sudah nonaktif.</div>
          <div style="margin-top:12px;">
            <a class="btnSetGhost" href="/products">Kembali ke katalog</a>
          </div>
        </div>
      <?php else: ?>

        <div class="detailWrap">
          <!-- LEFT -->
          <div class="panel">
            <div class="mediaTop">
              <div class="mediaFrame">
                <?php if ($img): ?>
                  <img src="<?= e($img) ?>" alt="<?= e($name) ?>">
                <?php else: ?>
                  <div style="font-weight:600;color:#64748b;">No Image</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="mediaBody">
              <h1 class="pTitle"><?= e($name) ?></h1>
              <div class="pMeta">
                <span class="metaDot"></span>
                <span>Globally redeemable</span>
                <?php if ($cat !== ''): ?>
                  <span>•</span><span><?= e($cat) ?></span>
                <?php endif; ?>
              </div>

              <div class="pDesc">
                <?= $desc ? e($desc) : 'Voucher digital & layanan top up. Proses cepat dan mudah.' ?>
              </div>
            </div>
          </div>

          <!-- RIGHT -->
          <div class="panel">
            <div class="buyHead">
              <p class="buyTitle">Choose Amount</p>
              <div class="stockRow">
                <span class="stockDot"></span><span>In Stock</span>
              </div>
            </div>

            <div class="buyBody">
              <form id="buyForm" method="POST" action="/cart/add">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="product_id" value="<?= (int)$pid ?>">
                <input type="hidden" id="amountHidden" name="amount_idr" value="">

                <?php if (empty($btnDenoms)): ?>
                  <div style="color:var(--muted);">
                    Nominal belum tersedia untuk produk ini.
                  </div>
                  <a class="btnSetGhost" href="/products" style="margin-top:12px;">Back</a>
                <?php else: ?>

                  <div class="amountGrid" id="amountGrid" aria-label="Pilih nominal">
                    <?php foreach ($btnDenoms as $amt): ?>
                      <button class="amtBtn" type="button" data-amt="<?= (int)$amt ?>">
                        <?= e(money_idr((int)$amt)) ?>
                      </button>
                    <?php endforeach; ?>
                  </div>

                  <div class="divider"></div>

                  <div class="sumRow">
                    <div>Payable Amount:</div>
                    <div id="payableText">—</div>
                  </div>

                  <div class="sumTotal">
                    <div class="label">Total:</div>
                    <div id="totalText" class="val">Rp —</div>
                  </div>

                  <button id="addBtn" class="btnSetPrimary" type="submit" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         width="20" height="20" aria-hidden="true">
                      <path d="M6 6h15l-2 9H7L6 6Z"/>
                      <path d="M6 6 5 3H2"/>
                      <path d="M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                      <path d="M18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                    </svg>
                    Add to Cart
                  </button>

                  <a class="btnSetGhost" href="/products">Back</a>

                  <div class="hint">
                    Tips: setelah masuk keranjang, kamu bisa checkout dari menu Cart.
                  </div>

                <?php endif; ?>
              </form>
            </div>
          </div>
        </div>

        <!-- ✅ Sesuai request: HAPUS TOTAL “Produk terkait” -->

      <?php endif; ?>

    </div>
  </div>
</div>

<script>
(function(){
  const grid = document.getElementById('amountGrid');
  const hidden = document.getElementById('amountHidden');
  const payable = document.getElementById('payableText');
  const total = document.getElementById('totalText');
  const addBtn = document.getElementById('addBtn');

  function fmtIDR(n){
    try { return new Intl.NumberFormat('id-ID').format(n); }
    catch(e){ return (n+'').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
  }

  function setAmount(v){
    if (!hidden) return;
    hidden.value = String(v || '');
    if (payable) payable.textContent = v ? ('Rp ' + fmtIDR(v)) : '—';
    if (total) total.textContent = v ? ('Rp ' + fmtIDR(v)) : 'Rp —';
    if (addBtn) addBtn.disabled = !v;

    if (grid) {
      grid.querySelectorAll('.amtBtn').forEach(btn=>{
        const a = parseInt(btn.getAttribute('data-amt') || '0', 10);
        btn.classList.toggle('amtBtnActive', a === v);
      });
    }
  }

  if (grid) {
    grid.addEventListener('click', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLElement)) return;
      const btn = t.closest('.amtBtn');
      if (!btn) return;
      const v = parseInt(btn.getAttribute('data-amt') || '0', 10);
      if (v > 0) setAmount(v);
    });
  }

  setAmount(0);
})();
</script>
