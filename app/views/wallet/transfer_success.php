<?php
use function App\Lib\e;
use function App\Lib\money_idr;

function id_month_short(int $m): string {
  $months = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  return $months[$m] ?? '???';
}

$tr = $tr ?? [];

$amount = (int)($tr['amount_idr'] ?? 0);

$dtStr = (string)($tr['created_at'] ?? '');
$dt = $dtStr ? new DateTime($dtStr) : new DateTime();

// Format: 25 Feb 2023 · 14:35 WIB
$day   = $dt->format('d');
$month = id_month_short((int)$dt->format('n'));
$year  = $dt->format('Y');
$time  = $dt->format('H:i');
$dtNice = $day . ' ' . $month . ' ' . $year . ' · ' . $time . ' WIB';

$toName  = (string)($tr['to_name'] ?? '');
$toEmail = (string)($tr['to_email'] ?? '');
$fromName  = (string)($tr['from_name'] ?? '');
$fromEmail = (string)($tr['from_email'] ?? '');
$note = (string)($tr['note'] ?? '');

$receiverTitle = trim($toName) !== '' ? $toName : ($toEmail ?: 'Penerima');
$receiverSub   = trim($toEmail) !== '' ? $toEmail : ($toName ?: '');

// Kalau kamu punya bank/akun di data transfer, bisa otomatis tampil.
// Kalau belum ada, aman (nggak muncul).
$bankName   = (string)($tr['to_bank_name'] ?? '');
$bankAcc    = (string)($tr['to_bank_account'] ?? '');
$fundSource = (string)($tr['fund_source'] ?? 'FastPayTrack Wallet'); // fallback
$feeIdr     = (int)($tr['fee_idr'] ?? 0);

// Tombol share: teks yang akan dibagikan
$shareText = 'Transfer berhasil: ' . money_idr($amount) . ' (' . $dtNice . ')';
?>

<style>
  /* =========================================================
     TRANSFER SUCCESS THEME (scope aman)
     ========================================================= */
  body.page-transfer-success{
    padding:0 !important;
    background:
      radial-gradient(900px 520px at 12% 12%, rgba(99,102,241,.18), transparent 60%),
      radial-gradient(900px 520px at 88% 18%, rgba(34,211,238,.18), transparent 60%),
      radial-gradient(900px 520px at 80% 90%, rgba(147,51,234,.10), transparent 55%),
      #ffffff !important;
    color:#0b1220;
    min-height:100vh;
  }
  body.page-transfer-success .wrap{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
    padding: min(6vh, 64px) 16px 44px;
    display:flex;
    align-items:flex-start;
    justify-content:center;
  }

  .tsContainer{ width:min(980px, 100%); margin:0 auto; }
  .tsShell{
    border-radius:28px;
    overflow:hidden;
    background: rgba(255,255,255,.70);
    backdrop-filter: blur(10px);
    border:1px solid rgba(226,232,240,.95);
    box-shadow: 0 34px 90px rgba(2,6,23,.12);
  }

  /* konten utama */
  .tsMain{
    padding: 24px 18px 18px;
    background: rgba(255,255,255,.70);
  }
  @media(min-width:720px){
    .tsMain{ padding: 28px 24px 22px; }
  }

  .tsTop{
    display:flex;
    flex-direction:column;
    align-items:center;
    text-align:center;
    gap:10px;
  }

  .tsTitle{
    margin:0;
    font-size:22px;
    font-weight:600;
    letter-spacing:.1px;
    color:#0f172a;
  }
  @media(min-width:720px){ .tsTitle{ font-size:24px; } }

  .tsSub{
    margin:0;
    color:rgba(15,23,42,.70);
    font-size:13px;
    font-weight:500;
  }

  /* area animasi */
  .tsAnimWrap{
    width: 260px;
    height: 260px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-top:4px;
  }
  @media(min-width:720px){
    .tsAnimWrap{ width: 300px; height: 300px; }
  }

  /* card detail (mirip referensi) */
  .tsCard{
    margin-top:14px;
    background:#fff;
    border:1px solid rgba(226,232,240,.95);
    border-radius:20px;
    box-shadow: 0 16px 40px rgba(2,6,23,.06);
    overflow:hidden;
  }
  .tsCardInner{ padding:14px; }
  @media(min-width:720px){
    .tsCardInner{ padding:16px; }
  }

  .tsRow{
    display:flex;
    gap:12px;
    align-items:center;
  }

  .tsAvatar{
    width:42px;height:42px;
    border-radius:14px;
    border:1px solid rgba(226,232,240,.95);
    background: linear-gradient(180deg, rgba(99,102,241,.10), rgba(255,255,255,0));
    display:grid;
    place-items:center;
    flex:0 0 auto;
  }
  .tsAvatar svg{ width:22px;height:22px; opacity:.8; }

  .tsRec{
    min-width:0;
    display:flex;
    flex-direction:column;
    gap:3px;
  }
  .tsRecSmall{
    font-size:12px;
    color:rgba(15,23,42,.55);
    font-weight:500;
  }
  .tsRecName{
    font-size:14px;
    font-weight:600;
    color:#0f172a;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }
  .tsRecSub{
    font-size:12.5px;
    color:rgba(15,23,42,.62);
    font-weight:500;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .tsDivider{
    margin:12px 0;
    border-top:1px dashed rgba(148,163,184,.55);
  }

  .tsLabel{
    font-size:12px;
    font-weight:500;
    color:rgba(15,23,42,.55);
    margin:0;
  }

  .tsAmount{
    margin:6px 0 0;
    font-size:22px;
    font-weight:700;
    color:#0f172a;
    letter-spacing:.1px;
  }
  @media(min-width:720px){ .tsAmount{ font-size:26px; } }

  .tsKV{
    margin-top:12px;
    display:grid;
    gap:10px;
  }
  @media(min-width:720px){
    .tsKV{ grid-template-columns: 1fr 1fr; }
  }

  .tsKVItem{
    display:flex;
    flex-direction:column;
    gap:4px;
    min-width:0;
  }
  .tsKVVal{
    font-size:13.5px;
    font-weight:600;
    color:#0f172a;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }
  .tsKVValMuted{
    font-size:13.5px;
    font-weight:500;
    color:rgba(15,23,42,.70);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  /* “Lebih Detail” */
  .tsMoreBtn{
    margin-top:12px;
    width:100%;
    border:none;
    background:transparent;
    color:#2563eb;
    font-weight:600;
    font-size:13.5px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:8px 0 2px;
  }
  .tsMoreBtn:hover{ text-decoration:underline; }
  .tsChevron{ transition: transform .18s ease; }
  .tsMoreOpen .tsChevron{ transform: rotate(180deg); }

  .tsMorePanel{
    display:none;
    margin-top:10px;
    padding-top:10px;
    border-top:1px solid rgba(226,232,240,.95);
  }
  .tsMoreOpen + .tsMorePanel{ display:block; }

  /* bantuan card */
  .tsHelp{
    margin-top:14px;
    background:#fff;
    border:1px solid rgba(226,232,240,.95);
    border-radius:20px;
    box-shadow: 0 16px 40px rgba(2,6,23,.06);
    padding:14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
  }
  .tsHelpLeft{ display:flex; align-items:center; gap:10px; min-width:0; }
  .tsHelpIcon{
    width:40px;height:40px;
    border-radius:14px;
    border:1px solid rgba(226,232,240,.95);
    background: rgba(99,102,241,.08);
    display:grid;place-items:center;
    flex:0 0 auto;
  }
  .tsHelpIcon svg{ width:22px;height:22px; opacity:.9; }
  .tsHelpText{ min-width:0; }
  .tsHelpTitle{ margin:0; font-size:14px; font-weight:600; color:#0f172a; }
  .tsHelpSub{ margin:2px 0 0; font-size:12.5px; font-weight:500; color:rgba(15,23,42,.62); }

  .tsHelpArrow{
    width:34px;height:34px;
    border-radius:14px;
    border:1px solid rgba(226,232,240,.95);
    background:#fff;
    display:grid;place-items:center;
    flex:0 0 auto;
  }
  .tsHelpArrow svg{ width:18px;height:18px; opacity:.65; }

  /* tombol bawah */
  .tsBottom{
    padding: 16px 18px 20px;
    display:flex;
    gap:12px;
    justify-content:center;
    flex-wrap:wrap;
    background: rgba(255,255,255,.70);
  }
  .tsBtn{
    height:54px;
    border-radius:18px;
    font-weight:600;
    font-size:15px;
    cursor:pointer;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:0 18px;
    min-width: 160px;
  }
  .tsBtnGhost{
    background:#fff;
    border:1px solid rgba(34,197,94,.35);
    color:#0f172a;
  }
  .tsBtnGhost:hover{ background:#f8fafc; }

  .tsBtnPrimary{
    background: #84cc16; /* hijau fresh mirip referensi */
    border:1px solid #65a30d;
    color:#0b1220;
    box-shadow: 0 18px 34px rgba(132,204,22,.25);
  }
  .tsBtnPrimary:hover{ filter:brightness(.98); }

  /* rapihin mobile: tombol full */
  @media(max-width:520px){
    .tsBtn{ width:100%; min-width:0; }
    .tsBottom{ padding: 14px 16px 20px; }
  }
</style>

<script>
(function(){
  try{ document.body.classList.add('page-transfer-success'); }catch(e){}
})();
</script>

<div class="tsContainer">
  <div class="tsShell">

    <div class="tsMain">
      <div class="tsTop">
        <!-- Animasi centang (LottieFiles) -->
        <div class="tsAnimWrap" aria-hidden="true">
          <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>
          <dotlottie-wc
            src="https://lottie.host/89837077-6f9f-459d-9e26-8281abada9d5/4UeUtIKIhw.lottie"
            style="width: 100%; height: 100%;"
            autoplay
            loop
          ></dotlottie-wc>
        </div>

        <h1 class="tsTitle">Transfer berhasil</h1>
        <p class="tsSub"><?= e($dtNice) ?></p>
      </div>

      <div class="tsCard">
        <div class="tsCardInner">

          <div class="tsRow">
            <div class="tsAvatar" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4"></circle>
                <path d="M20 21a8 8 0 0 0-16 0"></path>
              </svg>
            </div>

            <div class="tsRec">
              <div class="tsRecSmall">Penerima</div>
              <div class="tsRecName"><?= e($receiverTitle) ?></div>
              <?php if ($receiverSub !== ''): ?>
                <div class="tsRecSub"><?= e($receiverSub) ?></div>
              <?php endif; ?>
              <?php if ($bankName || $bankAcc): ?>
                <div class="tsRecSub"><?= e(trim($bankName . ' · ' . $bankAcc)) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="tsDivider"></div>

          <p class="tsLabel">Nominal Transfer</p>
          <div class="tsAmount"><?= e(money_idr($amount)) ?></div>

          <div class="tsKV">
            <div class="tsKVItem">
              <div class="tsLabel">Sumber Dana</div>
              <div class="tsKVVal"><?= e($fundSource) ?></div>
            </div>

            <div class="tsKVItem">
              <div class="tsLabel">Metode & Biaya Transfer</div>
              <div class="tsKVValMuted">
                Transfer Online · <?= $feeIdr > 0 ? e(money_idr($feeIdr)) : e(money_idr(0)) ?>
                <?php if ($feeIdr <= 0): ?>
                  <span style="display:inline-block;margin-left:8px;padding:2px 8px;border-radius:999px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.28);color:#16a34a;font-weight:600;font-size:12px;">Gratis</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <?php
            // panel detail tambahan (hidden by default)
            $showMore = (trim($fromEmail) !== '') || (trim($fromName) !== '') || (trim($note) !== '');
          ?>
          <?php if ($showMore): ?>
            <button class="tsMoreBtn" type="button" id="moreBtn" aria-expanded="false">
              Lebih Detail
              <svg class="tsChevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                <path d="M6 9l6 6 6-6"></path>
              </svg>
            </button>

            <div class="tsMorePanel" id="morePanel">
              <?php if ($fromName || $fromEmail): ?>
                <div style="margin-top:4px;">
                  <div class="tsLabel">Pengirim</div>
                  <div class="tsKVVal"><?= e(trim(($fromName ?: 'Pengirim') . ' — ' . $fromEmail)) ?></div>
                </div>
              <?php endif; ?>

              <?php if ($note !== ''): ?>
                <div style="margin-top:10px;">
                  <div class="tsLabel">Catatan</div>
                  <div class="tsKVValMuted"><?= e($note) ?></div>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <a class="tsHelp" href="/help" style="text-decoration:none;color:inherit;">
        <div class="tsHelpLeft">
          <div class="tsHelpIcon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
              <path d="M9 10a2 2 0 1 1 2 2v1"></path>
              <path d="M12 17h.01"></path>
            </svg>
          </div>
          <div class="tsHelpText">
            <p class="tsHelpTitle">Butuh bantuan?</p>
            <p class="tsHelpSub">Kunjungi Pusat Bantuan</p>
          </div>
        </div>

        <div class="tsHelpArrow" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18l6-6-6-6"></path>
          </svg>
        </div>
      </a>
    </div>

    <div class="tsBottom">
      <a class="tsBtn tsBtnGhost" href="/dashboard">Tutup</a>
      <button class="tsBtn tsBtnPrimary" type="button" id="shareBtn">Share</button>
    </div>

  </div>
</div>

<script>
(function(){
  // Toggle "Lebih Detail"
  const moreBtn = document.getElementById('moreBtn');
  const morePanel = document.getElementById('morePanel');

  if (moreBtn && morePanel) {
    moreBtn.addEventListener('click', () => {
      const open = moreBtn.classList.toggle('tsMoreOpen');
      moreBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Share
  const shareBtn = document.getElementById('shareBtn');
  const shareText = <?= json_encode($shareText, JSON_UNESCAPED_UNICODE) ?>;
  const shareUrl = window.location.href;

  async function doShare(){
    try{
      if (navigator.share) {
        await navigator.share({
          title: 'Transfer berhasil',
          text: shareText,
          url: shareUrl
        });
        return;
      }
    }catch(e){
      // user cancel / fail => fallback
    }

    // fallback: copy link
    try{
      await navigator.clipboard.writeText(shareUrl);
      alert('Link berhasil disalin.');
    }catch(e){
      // fallback terakhir
      prompt('Salin link ini:', shareUrl);
    }
  }

  if (shareBtn) shareBtn.addEventListener('click', doShare);
})();
</script>
