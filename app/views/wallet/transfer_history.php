<?php
use function App\Lib\e;
use function App\Lib\money_idr;

$bal = (int)($balance ?? 0); // (tidak ditampilkan lagi sesuai revisi)
$rows = $rows ?? [];

/** ✅ samakan logo dengan dashboard */
$brandLogo = '/asset/brand/fastpaytrack-logo2.png';

$uid = (int)\App\Lib\Auth::id();

/**
 * Grouping by day: "Y-m-d"
 * Also compute daily net total (out = -, in = +)
 */
$groups = [];
foreach ($rows as $r) {
  $created = (string)($r['created_at'] ?? '');
  try {
    $dt = new DateTime($created);
  } catch (\Throwable $eDt) {
    $dt = new DateTime('now');
  }
  $dayKey = $dt->format('Y-m-d');

  $isOut = (int)($r['from_user_id'] ?? 0) === $uid;
  $amt = (int)($r['amount_idr'] ?? 0);
  $signed = $isOut ? -$amt : +$amt;

  if (!isset($groups[$dayKey])) {
    $groups[$dayKey] = [
      'label' => $dt->format('d M Y'),
      'total_signed' => 0,
      'items' => []
    ];
  }

  $groups[$dayKey]['total_signed'] += $signed;
  $groups[$dayKey]['items'][] = [
    'raw' => $r,
    'isOut' => $isOut,
    'dt_iso' => $dt->format('Y-m-d H:i:s'),
  ];
}

/** Sort groups desc by dayKey */
krsort($groups);
?>

<style>
/* =========================================================
   TRANSFER HISTORY THEME (scoped) - match show/settings vibe
   ========================================================= */
body.page-wallet-history{
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
body.page-wallet-history .wrap{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
  padding:0 !important;
}

/* shell full screen */
body.page-wallet-history .dashContainer{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
}
body.page-wallet-history .shell{
  width:100% !important;
  min-height:100vh;
  border-radius:0 !important;
  overflow:hidden;
  background: rgba(255,255,255,.70);
  backdrop-filter: blur(10px);
  border:0 !important;
  box-shadow:none !important;
}

/* ✅ semua font weight mengikuti show/settings */
body.page-wallet-history,
body.page-wallet-history p,
body.page-wallet-history span,
body.page-wallet-history a,
body.page-wallet-history label,
body.page-wallet-history input,
body.page-wallet-history select,
body.page-wallet-history button,
body.page-wallet-history small{
  font-weight: 500 !important;
}

/* hero */
body.page-wallet-history .hero{
  position:relative;
  color:#fff;
  padding:20px 20px 18px;
  background:
    radial-gradient(900px 420px at 12% 18%, rgba(37,99,235,.20), transparent 60%),
    radial-gradient(760px 420px at 92% 18%, rgba(34,211,238,.18), transparent 60%),
    linear-gradient(135deg, var(--bg1), var(--bg2));
}
body.page-wallet-history .hero:before{
  content:"";
  position:absolute;
  inset:-60px -60px auto auto;
  width:260px;height:260px;
  background:rgba(255,255,255,.16);
  border-radius:50%;
}

/* top row */
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

/* right icons */
.topRight{
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
.topIconBtn svg{ width:20px; height:20px; display:block; }

/* card body */
body.page-wallet-history .card{
  background: rgba(255,255,255,.70);
  padding:16px 16px 18px;
  border-radius:22px 22px 0 0;
}
@media (min-width: 980px){
  body.page-wallet-history .card{ padding:18px 18px 22px; }
  body.page-wallet-history .hero{ padding:22px 22px 18px; }
}

/* =========================================================
   Activity list (match recent activity card)
   ========================================================= */
.sectionTitle{
  margin:0 0 10px;
  font-size:16px;
  font-weight: 600 !important;
  color:#0f172a;
}

.dayGroup{
  margin-top:12px;
}

.dayHead{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:10px 4px 8px;
}
.dayLabel{
  font-size:14px;
  font-weight: 600 !important;
  color:#0f172a;
}
.dayTotal{
  font-size:14px;
  font-weight: 600 !important;
}
.dayTotal.neg{ color:#ef4444; }
.dayTotal.pos{ color:#16a34a; }

.activityCard{
  border:1px solid var(--border);
  border-radius:18px;
  background:#fff;
  padding:10px;
  display:grid;
  gap:10px;
}

/* item */
.actItem{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:8px 8px;
  border-radius:14px;
  text-decoration:none;
  color:inherit;
  cursor:pointer;
}
.actItem:hover{
  background:#f8fafc;
  box-shadow:0 10px 20px rgba(2,6,23,.06);
}
.actLeft{
  display:flex;
  align-items:center;
  gap:10px;
  min-width:0;
}
.actIcon{
  width:34px;height:34px;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#f8fafc;
  display:grid;
  place-items:center;
  flex:0 0 auto;
}
.actIcon svg{ width:18px;height:18px; color:#2563eb; }

.actText{
  min-width:0;
  display:flex;
  flex-direction:column;
  gap:3px;
}
.actTitle{
  font-size:13.5px;
  font-weight: 600 !important;
  color:#0f172a;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  max-width: 220px;
}
.actSub{
  font-size:12.5px;
  color:#64748b;
  line-height:1.2;
}

.actAmt{
  font-size:13.5px;
  font-weight: 600 !important;
  white-space:nowrap;
}
.actAmt.out{ color:#ef4444; }
.actAmt.in{ color:#16a34a; }

@media (max-width:520px){
  .actTitle{ max-width: 190px; }
}

/* empty note */
.noteEmpty{
  margin-top:10px;
  font-size:13px;
  color:#64748b;
}

/* =========================================================
   Modal detail (popup transaction detail)
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
  width:min(560px,100%);
  background:#fff;
  border:1px solid var(--border);
  border-radius:18px;
  box-shadow: 0 22px 55px rgba(2,6,23,.28);
  overflow:hidden;
}
.modalHead{
  padding:14px 14px 10px;
  border-bottom:1px solid var(--border);
  text-align:center;
}
.modalTopLine{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  color:#16a34a;
  font-weight: 600 !important;
  font-size:13px;
}
.modalTitle{
  margin:8px 0 0;
  font-weight: 600 !important;
  font-size:16px;
  color:#0f172a;
}
.modalBody{
  padding:14px;
}
.detailCard{
  border:1px solid var(--border);
  border-radius:16px;
  background:#fff;
  overflow:hidden;
}
.detailRow{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:12px 12px;
}
.detailRow + .detailRow{ border-top:1px solid #eef2f7; }
.detailKey{
  color:#64748b;
  font-size:12.5px;
}
.detailVal{
  color:#0f172a;
  font-weight: 600 !important;
  font-size:13.5px;
  text-align:right;
  white-space:nowrap;
}
.detailVal.wrap{
  white-space:normal;
  text-align:right;
  max-width: 320px;
}

.modalActions{
  padding:14px;
  display:grid;
  gap:10px;
}
.btnPrimary{
  width:100%;
  height:52px;
  border-radius:16px;
  border:1px solid #1d4ed8;
  background:#2563eb;
  color:#fff;
  font-weight: 600 !important;
  font-size:15px;
  cursor:pointer;
}
.btnPrimary:hover{ filter:brightness(.97); }
.btnGhost{
  width:100%;
  height:50px;
  border-radius:16px;
  border:1px solid var(--border);
  background:#fff;
  color:#0f172a;
  font-weight: 600 !important;
  font-size:15px;
  cursor:pointer;
}
.btnGhost:hover{ background:#f8fafc; }
</style>

<script>
(function(){
  try{ document.body.classList.add('page-wallet-history'); }catch(e){}
})();
</script>

<div class="dashContainer">
  <div class="shell">

    <div class="hero">
      <div class="topbar">
        <div class="brandRow">
          <a class="dashBrand" href="/dashboard" aria-label="FastPayTrack Dashboard">
            <img src="<?= e($brandLogo) ?>" alt="FASTPAYTRACK">
          </a>

          <!-- ✅ icon kanan atas: HOME + BACK -->
          <div class="topRight" aria-label="Top actions">
            <a class="topIconBtn" href="/wallet/transfer" title="Back" aria-label="Back">
              <!-- back -->
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"></path>
              </svg>
            </a>

<a class="topIconBtn" href="/dashboard" title="Home" aria-label="Home">
              <!-- home -->
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11l9-8 9 8"></path>
                <path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"></path>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- ✅ sesuai revisi: tidak ada judul & saldo -->
    </div>

    <div class="card">

      <div class="sectionTitle">Recent activity</div>

      <?php if (empty($rows)): ?>
        <div class="activityCard">
          <div class="noteEmpty">Belum ada aktivitas transfer.</div>
        </div>
      <?php else: ?>

        <?php foreach ($groups as $dayKey => $g): ?>
          <?php
            $tot = (int)$g['total_signed'];
            $totTxt = ($tot < 0 ? '-' : '+') . money_idr(abs($tot));
            $totClass = $tot < 0 ? 'neg' : 'pos';
          ?>
          <div class="dayGroup">
            <div class="dayHead">
              <div class="dayLabel"><?= e($g['label']) ?></div>
              <div class="dayTotal <?= e($totClass) ?>"><?= e($totTxt) ?></div>
            </div>

            <div class="activityCard">
              <?php foreach ($g['items'] as $it):
                $r = $it['raw'];
                $isOut = (bool)$it['isOut'];

                $fromEmail = (string)($r['from_email'] ?? '');
                $toEmail   = (string)($r['to_email'] ?? '');
                $amount    = (int)($r['amount_idr'] ?? 0);
                $note      = (string)($r['note'] ?? '');
                $createdAt = (string)($r['created_at'] ?? $it['dt_iso']);

                $title = $isOut
                  ? ('Transfer to ' . $toEmail)
                  : ('Transfer from ' . $fromEmail);

                $sub = $isOut ? 'Transfer Completed' : 'Posted';
                $amtText = ($isOut ? '-' : '+') . money_idr($amount);
                $amtClass = $isOut ? 'out' : 'in';

                // modal detail fields
                $paymentMethod = 'FastPayTrack Balance';
                $counterpartyLabel = $isOut ? 'Recipient' : 'Sender';
                $counterpartyEmail = $isOut ? $toEmail : $fromEmail;
              ?>
                <div class="actItem js-open-detail"
                     role="button"
                     tabindex="0"
                     data-title="<?= e($title) ?>"
                     data-sub="<?= e($sub) ?>"
                     data-amount="<?= e(money_idr($amount)) ?>"
                     data-amount-sign="<?= e($isOut ? '-' : '+') ?>"
                     data-is-out="<?= e($isOut ? '1' : '0') ?>"
                     data-payment-method="<?= e($paymentMethod) ?>"
                     data-counterparty-label="<?= e($counterpartyLabel) ?>"
                     data-counterparty-email="<?= e($counterpartyEmail) ?>"
                     data-note="<?= e($note) ?>"
                     data-created="<?= e($createdAt) ?>"
                >
                  <div class="actLeft">
                    <div class="actIcon" aria-hidden="true">
                      <!-- paper plane -->
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 2L11 13"></path>
                        <path d="M22 2l-7 20-4-9-9-4 20-7z"></path>
                      </svg>
                    </div>

                    <div class="actText">
                      <div class="actTitle"><?= e($title) ?></div>
                      <div class="actSub"><?= e($sub) ?></div>
                    </div>
                  </div>

                  <div class="actAmt <?= e($amtClass) ?>"><?= e($amtText) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>

    </div>
  </div>
</div>

<!-- ===== MODAL DETAIL ===== -->
<div class="modalBackdrop" id="detailModal">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="detailTitle">
    <div class="modalHead">
      <div class="modalTopLine" aria-hidden="true">
        <span style="display:inline-grid;place-items:center;width:18px;height:18px;border-radius:999px;background:rgba(22,163,74,.12);">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
               stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
               viewBox="0 0 24 24" style="color:#16a34a;">
            <path d="M20 6 9 17l-5-5"></path>
          </svg>
        </span>
        <span id="detailTopText">Transaction success!</span>
      </div>
      <div class="modalTitle" id="detailTitle">Transaction Detail</div>
    </div>

    <div class="modalBody">
      <div class="detailCard">
        <div class="detailRow">
          <div>
            <div class="detailKey">Total Payment</div>
          </div>
          <div class="detailVal" id="detailTotal">Rp 0</div>
        </div>

        <div class="detailRow">
          <div class="detailKey">Payment Method</div>
          <div class="detailVal" id="detailMethod">FastPayTrack Balance</div>
        </div>

        <div class="detailRow">
          <div class="detailKey" id="detailCpLabel">Recipient</div>
          <div class="detailVal wrap" id="detailCpEmail">-</div>
        </div>

        <div class="detailRow" id="detailNoteRow" style="display:none;">
          <div class="detailKey">Note</div>
          <div class="detailVal wrap" id="detailNote">-</div>
        </div>

        <div class="detailRow">
          <div class="detailKey">Date</div>
          <div class="detailVal" id="detailDate">-</div>
        </div>
      </div>
    </div>

    <div class="modalActions">
      <button class="btnPrimary" type="button" id="detailCloseTop">Close</button>
      <button class="btnGhost" type="button" id="detailClose">Back</button>
    </div>
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('detailModal');
  const closeBtn = document.getElementById('detailClose');
  const closeBtn2 = document.getElementById('detailCloseTop');

  const elTotal = document.getElementById('detailTotal');
  const elMethod = document.getElementById('detailMethod');
  const elCpLabel = document.getElementById('detailCpLabel');
  const elCpEmail = document.getElementById('detailCpEmail');
  const elNoteRow = document.getElementById('detailNoteRow');
  const elNote = document.getElementById('detailNote');
  const elDate = document.getElementById('detailDate');

  function openModalFrom(el){
    const amount = (el.getAttribute('data-amount') || '0');
    const sign = (el.getAttribute('data-amount-sign') || '+');
    const isOut = el.getAttribute('data-is-out') === '1';

    const method = el.getAttribute('data-payment-method') || 'FastPayTrack Balance';
    const cpLabel = el.getAttribute('data-counterparty-label') || (isOut ? 'Recipient' : 'Sender');
    const cpEmail = el.getAttribute('data-counterparty-email') || '-';
    const note = (el.getAttribute('data-note') || '').trim();
    const created = el.getAttribute('data-created') || '-';

    // total payment: show positive value only (like sample)
    elTotal.textContent = 'Rp ' + amount.replace(/^Rp\s*/i,'').trim();

    elMethod.textContent = method;
    elCpLabel.textContent = cpLabel;
    elCpEmail.textContent = cpEmail;

    if (note) {
      elNoteRow.style.display = '';
      elNote.textContent = note;
    } else {
      elNoteRow.style.display = 'none';
      elNote.textContent = '';
    }

    elDate.textContent = created;

    modal.style.display = 'flex';
  }

  function closeModal(){
    modal.style.display = 'none';
  }

  document.querySelectorAll('.js-open-detail').forEach(item => {
    item.addEventListener('click', () => openModalFrom(item));
    item.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModalFrom(item); }
      if (e.key === 'Escape') { e.preventDefault(); closeModal(); }
    });
  });

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (closeBtn2) closeBtn2.addEventListener('click', closeModal);

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });
  }
})();
</script>
