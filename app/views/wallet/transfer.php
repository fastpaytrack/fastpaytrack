<?php
use function App\Lib\e;
use function App\Lib\money_idr;

$bal = (int)($me['balance_idr'] ?? 0);
$hasPin = (int)($me['has_pin'] ?? 0) === 1;

/** ✅ samakan dengan dashboard */
$brandLogo = '/asset/brand/fastpaytrack-logo2.png';

/** recent activity (10) dari controller */
$recent = $recent ?? [];
?>

<style>
  /* =========================================================
     WALLET TRANSFER THEME (scoped) - FULLSCREEN like show/settings
     ========================================================= */
  body.page-wallet-transfer{
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
  body.page-wallet-transfer .wrap{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
    padding:0 !important;
  }

  /* ✅ shell full screen */
  body.page-wallet-transfer .shell{
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
     ✅ Semua font/weight ikut show/settings
     ========================================================= */
  body.page-wallet-transfer,
  body.page-wallet-transfer p,
  body.page-wallet-transfer span,
  body.page-wallet-transfer a,
  body.page-wallet-transfer label,
  body.page-wallet-transfer input,
  body.page-wallet-transfer select,
  body.page-wallet-transfer button,
  body.page-wallet-transfer small{
    font-weight: 500 !important;
  }
  body.page-wallet-transfer .btn,
  body.page-wallet-transfer .btnPrimary,
  body.page-wallet-transfer .btnGhost{
    font-weight: 600 !important;
  }
  .titleStrong{ font-weight:600 !important; }

  /* ✅ inner width konsisten */
  .wtInner{
    width:min(980px, 100%);
    margin:0 auto;
    padding: 0 16px;
  }
  @media (max-width:520px){
    .wtInner{ padding: 0 14px; }
  }

  /* =========================================================
     HERO (sama feel show/settings)
     ========================================================= */
  .hero{
    position:relative;
    color:#fff;
    padding:20px 0 18px;
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

  /* =========================================================
     TOP ROW: logo kiri, icon riwayat kanan (tanpa cover)
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
    z-index:1;
  }
  .topIconBtn:hover{ opacity:1; }
  .topIconBtn svg{ width:20px; height:20px; display:block; }

  /* =========================================================
     BODY CARD AREA
     ========================================================= */
  .card{
    background: rgba(255,255,255,.70);
    padding:16px 0 18px;
  }

  /* Transfer main panel (card putih) */
  .panel{
    border:1px solid var(--border);
    border-radius:20px;
    background:#fff;
    box-shadow:0 10px 26px rgba(2,6,23,.04);
    overflow:hidden;
  }
  .panelHead{
    padding:14px;
    border-bottom:1px solid var(--border);
  }
  .panelTitle{ margin:0; font-size:15px; font-weight:600 !important; color:#0f172a; }
  .panelSub{ margin:6px 0 0; font-size:12.5px; color:var(--muted); line-height:1.35; }
  .panelBody{ padding:14px; }

  .warnPin{
    background:#fff;
    border:1px solid rgba(245,158,11,.35);
    border-radius:16px;
    padding:12px;
    color:#92400e;
    font-weight:600 !important;
    margin-bottom:12px;
  }
  .pinLink{ font-weight:600 !important; color:var(--primary); text-decoration:none; }
  .pinLink:hover{ text-decoration:underline; }

  .field{ margin-top:12px; display:flex; flex-direction:column; gap:8px; }
  .field label{ font-size:12.5px; font-weight:600 !important; color:#0f172a; }
  .field input{
    height:46px;
    border-radius:16px;
    border:1px solid var(--border);
    padding:0 14px;
    font-size:14px;
    outline:none;
    background:#fff;
  }
  .field input:focus{
    border-color: rgba(79,70,229,.55);
    box-shadow: 0 0 0 4px rgba(79,70,229,.12);
  }

  /* ✅ tombol style ikut show/settings + show page */
  .btnPrimary{
    width:100%;
    height:52px;
    border-radius:16px;
    border:1px solid #1d4ed8;
    background:#2563eb;
    color:#fff;
    font-size:15px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    margin-top:14px;
  }
  .btnPrimary:hover{ filter:brightness(.97); }

  .btnGhost{
    width:100%;
    height:50px;
    border-radius:16px;
    border:1px solid var(--border);
    background:#fff;
    color:#0f172a;
    font-size:15px;
    cursor:pointer;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-top:10px;
  }
  .btnGhost:hover{ background:#f8fafc; }

  /* =========================================================
     MODAL BASE
     ========================================================= */
  .modalBackdrop{
    position:fixed;
    inset:0;
    background:rgba(2,6,23,.55);
    display:none;
    align-items:center;
    justify-content:center;
    padding:18px;
    z-index:9999;
  }
  .modal{
    width:min(520px,100%);
    background:#fff;
    border:1px solid var(--border);
    border-radius:18px;
    box-shadow: 0 22px 55px rgba(2,6,23,.28);
    overflow:hidden;
  }
  .modalHead{
    padding:14px 14px 12px;
    border-bottom:1px solid var(--border);
  }
  .modalTitle{ margin:0; font-weight:600 !important; font-size:16px; }
  .modalSub{ margin:6px 0 0; color:var(--muted); font-size:12.5px; line-height:1.35; }
  .modalBody{ padding:14px; }

  /* ✅ dots loader */
  .dotsCenter{
    min-height:140px;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .dots{ display:flex; align-items:center; justify-content:center; gap:12px; }
  .dot{
    width:12px; height:12px;
    border-radius:999px;
    background: rgba(79,70,229,.55);
    animation: dotPulse 1.05s ease-in-out infinite;
  }
  .dot:nth-child(2){ animation-delay: .14s; opacity:.85; }
  .dot:nth-child(3){ animation-delay: .28s; opacity:.70; }
  @keyframes dotPulse{
    0%, 100%{ transform: translateY(0); opacity:.55; }
    50%{ transform: translateY(-6px); opacity:1; }
  }

  /* =========================================================
     POPUP INPUT NOMINAL (task 3 sudah benar: jangan ubah layout)
     -> style tombol/field sudah mengikuti base di atas
     ========================================================= */
  .miniHint{
    margin-top:8px;
    font-size:12.5px;
    color:var(--muted);
    line-height:1.4;
  }

  .quickRow{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:10px;
    margin-top:10px;
  }
  .quickBtn{
    height:40px;
    border-radius:14px;
    border:1px solid var(--border);
    background:#fff;
    cursor:pointer;
    font-weight:600 !important;
    padding:0 14px;
  }
  .quickBtn:hover{ background:#f8fafc; box-shadow: 0 10px 20px rgba(2,6,23,.06); }
  .quickBtn.active{
    border-color: rgba(79,70,229,.55);
    box-shadow: 0 0 0 4px rgba(79,70,229,.12);
  }

  .stackActions{ margin-top:14px; display:flex; flex-direction:column; gap:10px; }

  /* =========================================================
     PAYMENT CONFIRMATION (TASK 4 FIX)
     - gabung Email + Total jadi 1 card
     ========================================================= */
  .confirmCard{
    border:1px solid var(--border);
    border-radius:16px;
    background:#fff;
    padding:12px;
  }
  .confirmRow{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
    padding:6px 0;
  }
  .confirmLbl{
    font-size:12.5px;
    color:var(--muted);
    font-weight:600 !important;
    line-height:1.2;
  }
  .confirmVal{
    font-size:14px;
    color:#0f172a;
    font-weight:600 !important;
    line-height:1.2;
    text-align:right;
    word-break:break-word;
  }

  /* =========================================================
     PIN POPUP (task 5 benar: jangan ubah)
     ========================================================= */
  .pinCenterTitle{
    text-align:center;
    margin:0;
    font-weight:600 !important;
    font-size:16px;
  }
  .pinCenterSub{
    text-align:center;
    margin:8px 0 10px;
    color:#0f172a;
    font-weight:600 !important;
    font-size:13.5px;
  }

  .pinInput{
    width:100%;
    height:52px;
    border-radius:16px;
    border:1px solid var(--border);
    padding:0 14px;
    font-size:20px;
    font-weight:600 !important;
    letter-spacing:6px;
    text-align:center;
    outline:none;
    background:#fff;
  }
  .pinInput:focus{
    border-color: rgba(79,70,229,.55);
    box-shadow: 0 0 0 4px rgba(79,70,229,.12);
  }

  .pinHelp{
    text-align:center;
    margin-top:10px;
    font-size:12.5px;
    color:var(--muted);
  }

  /* =========================================================
     PROCESSING OVERLAY (task 6 benar)
     ========================================================= */
  .processingOverlay{
    position:fixed;
    inset:0;
    background:rgba(2,6,23,.55);
    display:none;
    align-items:center;
    justify-content:center;
    padding:18px;
    z-index:10000;
  }
  .processingCard{
    width:min(520px,100%);
    background:#fff;
    border:1px solid var(--border);
    border-radius:18px;
    padding:14px 14px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:12px;
    box-shadow: 0 22px 55px rgba(2,6,23,.28);
    min-height:140px;
  }
  .processingText{
    text-align:center;
    color:var(--muted);
    font-size:12.5px;
    font-weight:500 !important;
  }

  /* =========================================================
     RECENT ACTIVITY (TASK 7 & 8 FIX)
     - font sama show/settings
     - layout seperti contoh: icon kiri, judul+status kiri, amount kanan
     - tanpa garis pemisah
     - ada "See more" di bawah card
     ========================================================= */
  .raWrap{
    margin-top:14px;
  }
  .raTitle{
    margin:14px 0 10px;
    font-size:18px;
    font-weight:600 !important;
    color:#0f172a;
  }

  .raCard{
    border:1px solid var(--border);
    border-radius:18px;
    background:#fff;
    box-shadow:0 10px 26px rgba(2,6,23,.04);
    overflow:hidden;
    padding:10px;
  }

  .raItem{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:10px 6px;
  }

  .raLeft{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
  }

  .raIcon{
    width:34px;height:34px;
    border-radius:999px;
    border:1px solid rgba(226,232,240,.95);
    background:#f8fafc;
    display:grid;
    place-items:center;
    flex:0 0 auto;
    color:#2563eb;
  }
  .raIcon svg{ width:18px;height:18px; display:block; }

  .raText{
    display:flex;
    flex-direction:column;
    gap:3px;
    min-width:0;
  }

  .raMain{
    font-size:14px;
    font-weight:600 !important;
    color:#0f172a;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width: 260px;
  }
  .raSub{
    font-size:12.5px;
    color:var(--muted);
    font-weight:500 !important;
    line-height:1.2;
  }

  .raAmt{
    font-size:14px;
    font-weight:600 !important;
    white-space:nowrap;
    text-align:right;
  }
  .raAmt.out{ color:#ef4444; }
  .raAmt.in{ color:#16a34a; }

  .raMore{
    display:block;
    text-align:center;
    padding:12px 0 6px;
    font-size:14px;
    font-weight:600 !important;
    color:#2563eb;
    text-decoration:none;
  }
  .raMore:hover{ text-decoration:underline; }

  @media (max-width:520px){
    .raMain{ max-width: 200px; }
  }
</style>

<script>
(function(){
  try{ document.body.classList.add('page-wallet-transfer'); }catch(e){}
})();
</script>

<div class="shell">
  <div class="hero">
    <div class="wtInner">
      <div class="brandRow">
        <a class="dashBrand" href="/dashboard" aria-label="FastPayTrack Dashboard">
          <img src="<?= e($brandLogo) ?>" alt="FASTPAYTRACK">
        </a>

        <!-- ✅ Icon kanan atas: Riwayat Transfer -->
        <a class="topIconBtn" href="/wallet/transfer/history" title="Riwayat Transfer" aria-label="Riwayat Transfer">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 3v6h6"/>
            <path d="M12 7v5l4 2"/>
          </svg>
        </a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="wtInner">

      <div class="panel">
        <div class="panelHead">
          <p class="panelTitle">Transfer</p>
          <p class="panelSub">Masukkan email tujuan terlebih dahulu, lalu lanjutkan.</p>
        </div>

        <div class="panelBody">
          <?php if (!$hasPin): ?>
            <div class="warnPin">
              Kamu belum membuat PIN. <a class="pinLink" href="/pin">Buat PIN sekarang</a>
            </div>
          <?php endif; ?>

          <form method="POST" action="/wallet/transfer" id="transferForm" style="max-width:560px;">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="pin" id="pinHidden" value="">
            <input type="hidden" name="amount_idr" id="amountRaw" value="">
            <input type="hidden" name="note" id="noteHidden" value="">

            <div class="field">
              <label>Email Tujuan</label>
              <input name="to_email" id="toEmail" type="email" placeholder="target@email.com" required>
            </div>

            <button class="btnPrimary btn btnPrimary" type="button" id="openRecipientBtn">
              Continue
            </button>

            <a class="btnGhost btn btnGhost" href="/dashboard">Back to Dashboard</a>
          </form>
        </div>
      </div>

      <!-- =========================
           RECENT ACTIVITY (Task 7 & 8)
           ========================= -->
      <div class="raWrap">
        <div class="raTitle">Recent activity</div>

        <div class="raCard">
          <?php if (empty($recent)): ?>
            <div style="padding:10px 6px; color:var(--muted); font-size:12.5px;">Belum ada aktivitas.</div>
          <?php else: ?>
            <?php foreach ($recent as $r): ?>
              <?php
                $uid = (int)\App\Lib\Auth::id();
                $isOut = ((int)($r['from_user_id'] ?? 0) === $uid);
                $toEmailRow = (string)($r['to_email'] ?? '');
                $fromEmailRow = (string)($r['from_email'] ?? '');
                $amt = (int)($r['amount_idr'] ?? 0);

                $main = $isOut ? ('Transfer to ' . $toEmailRow) : ('Transfer from ' . $fromEmailRow);
                $sub  = $isOut ? 'Transfer Completed' : 'Posted';
                $amtText = ($isOut ? '-Rp ' : '+Rp ') . number_format($amt, 0, ',', '.');
              ?>
              <div class="raItem">
                <div class="raLeft">
                  <div class="raIcon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M22 2L11 13"></path>
                      <path d="M22 2l-7 20-4-9-9-4 20-7z"></path>
                    </svg>
                  </div>
                  <div class="raText">
                    <div class="raMain"><?= e($main) ?></div>
                    <div class="raSub"><?= e($sub) ?></div>
                  </div>
                </div>

                <div class="raAmt <?= e($isOut ? 'out' : 'in') ?>">
                  <?= e($amtText) ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <a class="raMore" href="/wallet/transfer/history">See more</a>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- =========================
     MODAL 1: Recipient -> loading dots -> input nominal
     ========================= -->
<div class="modalBackdrop" id="recipientModal">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modalHead">
      <p class="modalTitle">Recipient</p>
      <p class="modalSub" id="recSub">Menyiapkan data...</p>
    </div>

    <div class="modalBody">
      <!-- loading view (task 2 benar) -->
      <div id="recLoading" class="dotsCenter">
        <div class="dots" aria-label="Loading">
          <span class="dot"></span><span class="dot"></span><span class="dot"></span>
        </div>
      </div>

      <!-- form view -->
      <div id="recForm" style="display:none;">
        <div class="field" style="margin-top:0;">
          <label>Email</label>
          <input id="recEmailView" type="text" readonly>
        </div>

        <div class="field">
          <label>Nominal Transfer (IDR)</label>
          <input id="amountDisplay" type="text" inputmode="numeric" placeholder="contoh: 25.000" autocomplete="off" />
          <div class="miniHint" id="saldoHint">Saldo tersedia <?= e(money_idr($bal)) ?></div>
        </div>

        <div class="quickRow" aria-label="Nominal cepat">
          <button class="quickBtn" type="button" data-amt="5000">Rp 5.000</button>
          <button class="quickBtn" type="button" data-amt="10000">Rp 10.000</button>
          <button class="quickBtn" type="button" data-amt="25000">Rp 25.000</button>
          <button class="quickBtn" type="button" data-amt="50000">Rp 50.000</button>
        </div>

        <div class="field">
          <label>Catatan (opsional)</label>
          <input id="noteInput" type="text" placeholder="Contoh: hadiah / refund / dll">
        </div>

        <!-- tombol posisi atas-bawah seperti show (task 3 benar) -->
        <div class="stackActions">
          <button class="btnPrimary btn btnPrimary" type="button" id="goConfirmBtn">Continue</button>
          <button class="btnGhost btn btnGhost" type="button" id="cancelRecipient">Batal</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- =========================
     MODAL 2: Payment Confirmation (TASK 4 FIX: 1 card)
     ========================= -->
<div class="modalBackdrop" id="confirmModal">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modalHead">
      <p class="modalTitle">Payment Confirmation</p>
      <p class="modalSub">Periksa detail transfer sebelum lanjut.</p>
    </div>

    <div class="modalBody">
      <div class="confirmCard">
        <div class="confirmRow">
          <div class="confirmLbl">Email</div>
          <div class="confirmVal" id="confirmEmailVal">-</div>
        </div>
        <div class="confirmRow">
          <div class="confirmLbl">Total</div>
          <div class="confirmVal" id="confirmTotalVal">-</div>
        </div>
      </div>

      <div class="stackActions">
        <button class="btnPrimary btn btnPrimary" type="button" id="goPinBtn">Continue</button>
        <button class="btnGhost btn btnGhost" type="button" id="backToRecipient">Back</button>
      </div>
    </div>
  </div>
</div>

<!-- =========================
     MODAL 3: PIN (task 5 benar)
     ========================= -->
<div class="modalBackdrop" id="pinModal">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modalBody">
      <p class="pinCenterTitle">Verification Process</p>
      <p class="pinCenterSub">Enter your PIN code</p>

      <input id="pinInput" class="pinInput" type="password" inputmode="numeric" pattern="\d{6}"
             maxlength="6" placeholder="••••••" />

      <div class="pinHelp">
        Lupa PIN? <a class="pinLink" href="/pin">Ubah PIN</a>
      </div>

      <div class="stackActions">
        <button class="btnPrimary btn btnPrimary" type="button" id="doTransferBtn">Transfer</button>
        <button class="btnGhost btn btnGhost" type="button" id="pinBackBtn">Back</button>
      </div>
    </div>
  </div>
</div>

<!-- =========================
     PROCESSING OVERLAY (task 6 benar)
     ========================= -->
<div class="processingOverlay" id="processingOverlay" aria-hidden="true">
  <div class="processingCard" role="status" aria-live="polite">
    <div class="dots" aria-hidden="true">
      <span class="dot"></span><span class="dot"></span><span class="dot"></span>
    </div>
    <div class="processingText">Memproses transfer...</div>
  </div>
</div>

<script>
(function(){
  // helpers
  const fmtIDR = (digits) => {
    const s = String(digits || '').replace(/[^\d]/g,'');
    if (!s) return '';
    return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  };
  const moneyText = (n) => 'Rp ' + String(n || 0).replace(/[^\d]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  const onlyDigits = (s) => String(s||'').replace(/[^\d]/g,'');

  // elements
  const form = document.getElementById('transferForm');
  const toEmail = document.getElementById('toEmail');

  const openRecipientBtn = document.getElementById('openRecipientBtn');
  const recipientModal = document.getElementById('recipientModal');
  const recLoading = document.getElementById('recLoading');
  const recForm = document.getElementById('recForm');
  const recEmailView = document.getElementById('recEmailView');
  const cancelRecipient = document.getElementById('cancelRecipient');

  const amountDisplay = document.getElementById('amountDisplay');
  const amountRaw = document.getElementById('amountRaw');
  const noteInput = document.getElementById('noteInput');
  const noteHidden = document.getElementById('noteHidden');

  const goConfirmBtn = document.getElementById('goConfirmBtn');
  const confirmModal = document.getElementById('confirmModal');
  const confirmEmailVal = document.getElementById('confirmEmailVal');
  const confirmTotalVal = document.getElementById('confirmTotalVal');
  const backToRecipient = document.getElementById('backToRecipient');
  const goPinBtn = document.getElementById('goPinBtn');

  const pinModal = document.getElementById('pinModal');
  const pinInput = document.getElementById('pinInput');
  const pinHidden = document.getElementById('pinHidden');
  const pinBackBtn = document.getElementById('pinBackBtn');
  const doTransferBtn = document.getElementById('doTransferBtn');

  const processing = document.getElementById('processingOverlay');

  function showModal(el){ if(el) el.style.display='flex'; }
  function hideModal(el){ if(el) el.style.display='none'; }

  function showProcessing(){
    if(!processing) return;
    processing.style.display='flex';
    processing.setAttribute('aria-hidden','false');
  }

  // close on backdrop click
  [recipientModal, confirmModal, pinModal].forEach(m => {
    if(!m) return;
    m.addEventListener('click', (e) => { if(e.target === m) hideModal(m); });
  });

  // step 1 open recipient -> loading -> show form
  if(openRecipientBtn){
    openRecipientBtn.addEventListener('click', () => {
      const email = (toEmail && toEmail.value ? toEmail.value.trim() : '');
      if(!email){
        alert('Email tujuan wajib diisi.');
        toEmail && toEmail.focus();
        return;
      }
      // simple email check
      if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
        alert('Email tujuan tidak valid.');
        toEmail && toEmail.focus();
        return;
      }

      // reset
      if(recEmailView) recEmailView.value = email;
      if(recLoading) recLoading.style.display = '';
      if(recForm) recForm.style.display = 'none';
      if(amountDisplay) amountDisplay.value = '';
      if(amountRaw) amountRaw.value = '';
      if(noteInput) noteInput.value = '';
      document.querySelectorAll('.quickBtn').forEach(b => b.classList.remove('active'));

      showModal(recipientModal);

      // loading dots (task 2 benar)
      setTimeout(() => {
        if(recLoading) recLoading.style.display = 'none';
        if(recForm) recForm.style.display = '';
        try{ amountDisplay && amountDisplay.focus(); }catch(e){}
      }, 900);
    });
  }

  if(cancelRecipient){
    cancelRecipient.addEventListener('click', () => hideModal(recipientModal));
  }

  // amount input formatting
  if(amountDisplay){
    amountDisplay.addEventListener('input', () => {
      const d = onlyDigits(amountDisplay.value);
      amountRaw.value = d;
      amountDisplay.value = fmtIDR(d);
      document.querySelectorAll('.quickBtn').forEach(b => b.classList.remove('active'));
    });
  }

  // quick buttons
  document.querySelectorAll('.quickBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      const amt = btn.getAttribute('data-amt') || '';
      amountRaw.value = onlyDigits(amt);
      amountDisplay.value = fmtIDR(amt);
      document.querySelectorAll('.quickBtn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  // go confirm
  if(goConfirmBtn){
    goConfirmBtn.addEventListener('click', () => {
      const email = (toEmail && toEmail.value ? toEmail.value.trim() : '');
      const amt = parseInt(amountRaw.value || '0', 10);
      if(amt < 1000){
        alert('Minimal transfer Rp 1.000');
        return;
      }

      // set hidden fields
      noteHidden.value = (noteInput && noteInput.value ? noteInput.value.trim() : '');

      // fill confirm
      if(confirmEmailVal) confirmEmailVal.textContent = email || '-';
      if(confirmTotalVal) confirmTotalVal.textContent = 'Rp ' + (amt ? amt.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.') : '-');

      hideModal(recipientModal);
      showModal(confirmModal);
    });
  }

  if(backToRecipient){
    backToRecipient.addEventListener('click', () => {
      hideModal(confirmModal);
      showModal(recipientModal);
    });
  }

  if(goPinBtn){
    goPinBtn.addEventListener('click', () => {
      hideModal(confirmModal);
      // reset pin
      if(pinInput) pinInput.value = '';
      if(pinHidden) pinHidden.value = '';
      showModal(pinModal);
      setTimeout(() => { try{ pinInput && pinInput.focus(); }catch(e){} }, 60);
    });
  }

  if(pinBackBtn){
    pinBackBtn.addEventListener('click', () => {
      hideModal(pinModal);
      showModal(confirmModal);
    });
  }

  // submit transfer
  if(doTransferBtn){
    doTransferBtn.addEventListener('click', () => {
      const pin = (pinInput && pinInput.value ? pinInput.value.trim() : '');
      if(!/^\d{6}$/.test(pin)){
        alert('PIN harus 6 digit angka.');
        pinInput && pinInput.focus();
        return;
      }

      pinHidden.value = pin;

      hideModal(pinModal);
      showProcessing();

      setTimeout(() => {
        try{ form && form.submit(); }catch(e){}
      }, 150);
    });
  }

  // allow Enter in PIN
  if(pinInput){
    pinInput.addEventListener('keydown', (e) => {
      if(e.key === 'Enter'){ e.preventDefault(); doTransferBtn && doTransferBtn.click(); }
      if(e.key === 'Escape'){ e.preventDefault(); hideModal(pinModal); }
    });
  }
})();
</script>
