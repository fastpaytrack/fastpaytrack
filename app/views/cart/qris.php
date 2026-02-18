<?php
use function App\Lib\e;
use function App\Lib\money_idr;

$orderCode = (string) ($order['order_code'] ?? '');
$total = (int) ($order['total'] ?? 0);

/**
 * ROBUST QR URL DETECTOR:
 * Controller bisa mengirim QR URL dengan nama variabel berbeda.
 * Kita coba semua kemungkinan:
 * - $qr_url (yang kita pakai di PaymentController)
 * - $qrUrl / $qrisUrl
 * - $qris['qr_url']
 * - $q['qr_url']
 * - $qris['actions'][...]['url'] / $q['actions'][...]['url']
 * - $data['actions'][...]['url']
 * + ✅ fallback dari DB: orders.payment_reference = "txid|qrurl"
 */
$qrisUrl = '';

if (!empty($qr_url))
  $qrisUrl = (string) $qr_url;
if (!$qrisUrl && !empty($qrUrl))
  $qrisUrl = (string) $qrUrl;
// kalau ada variabel bernama sama, biarkan saja
if (!$qrisUrl && isset($qrisUrl) && is_string($qrisUrl) && $qrisUrl !== '')
  $qrisUrl = (string) $qrisUrl;

if (!$qrisUrl && !empty($qris) && is_array($qris)) {
  if (!empty($qris['qr_url']))
    $qrisUrl = (string) $qris['qr_url'];
  if (!$qrisUrl && !empty($qris['actions']) && is_array($qris['actions'])) {
    foreach ($qris['actions'] as $a) {
      if (($a['name'] ?? '') === 'generate-qr-code' && !empty($a['url']))
        $qrisUrl = (string) $a['url'];
      if (!$qrisUrl && !empty($a['url']))
        $qrisUrl = (string) $a['url'];
    }
  }
}

if (!$qrisUrl && !empty($q) && is_array($q)) {
  if (!empty($q['qr_url']))
    $qrisUrl = (string) $q['qr_url'];
  if (!$qrisUrl && !empty($q['actions']) && is_array($q['actions'])) {
    foreach ($q['actions'] as $a) {
      if (($a['name'] ?? '') === 'generate-qr-code' && !empty($a['url']))
        $qrisUrl = (string) $a['url'];
      if (!$qrisUrl && !empty($a['url']))
        $qrisUrl = (string) $a['url'];
    }
  }
}

if (!$qrisUrl && !empty($data) && is_array($data) && !empty($data['actions']) && is_array($data['actions'])) {
  foreach ($data['actions'] as $a) {
    if (($a['name'] ?? '') === 'generate-qr-code' && !empty($a['url']))
      $qrisUrl = (string) $a['url'];
    if (!$qrisUrl && !empty($a['url']))
      $qrisUrl = (string) $a['url'];
  }
}

/** ✅ fallback dari DB: payment_reference = "transaction_id|qr_url" */
if (!$qrisUrl && !empty($order['payment_reference'])) {
  $ref = (string) $order['payment_reference'];
  if (strpos($ref, '|') !== false) {
    $parts = explode('|', $ref, 2);
    if (!empty($parts[1]))
      $qrisUrl = (string) $parts[1];
  }
}
?>

<style>
  .qrisWrap {
    display: grid;
    gap: 12px;
  }

  .qrisCard {
    border: 1px solid var(--border);
    border-radius: 18px;
    background: #fff;
    padding: 14px;
  }

  .qrisTitle {
    font-weight: 700;
    font-size: 15px;
  }

  .qrisSub {
    margin-top: 6px;
    color: var(--muted);
    font-weight: 600;
    font-size: 12.5px;
    line-height: 1.4;
  }

  .qrisBox {
    margin-top: 14px;
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
    display: grid;
    place-items: center;
    background: #fff;
  }

  .qrisImg {
    width: 260px;
    max-width: 100%;
    height: auto;
    border-radius: 12px;
  }

  .qrisActions {
    margin-top: 14px;
    display: grid;
    gap: 10px;
  }

  .qrisBtn {
    height: 46px;
    border-radius: 14px;
    background: var(--primary);
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid rgba(79, 70, 229, .25);
    box-shadow: 0 10px 18px rgba(79, 70, 229, .20);
    cursor: pointer;
  }

  .qrisBtn:hover {
    background: var(--primaryHover);
  }

  .qrisBtnGhost {
    background: #fff;
    color: #0f172a;
    border: 1px solid var(--border);
    box-shadow: none;
  }

  .qrisBtnGhost:hover {
    background: #f8fafc;
    box-shadow: 0 10px 20px rgba(2, 6, 23, .06);
  }

  .disabledBtn {
    opacity: .65;
    cursor: not-allowed;
  }

  @media (min-width: 520px) {
    .qrisActions {
      grid-template-columns: 1fr 1fr;
    }
  }
</style>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brand">
        <div class="logo">A</div>FastPayTrack
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="pill" href="/orders">Orders</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>

    <div class="heroTitle">QRIS Midtrans</div>
    <p class="heroSub">
      Scan QR untuk membayar • Order <span class="mono"><?= e($orderCode) ?></span>
    </p>
  </div>

  <div class="card">
    <div class="qrisWrap">
      <div class="qrisCard">
        <div class="qrisTitle">Total: <?= e(money_idr($total)) ?></div>
        <div class="qrisSub">
          Jika sudah bayar, status akan otomatis diperbarui melalui webhook Midtrans.
        </div>

        <div class="qrisBox">
          <?php if ($qrisUrl): ?>
            <img id="qrisImage" class="qrisImg" src="<?= e($qrisUrl) ?>" alt="QRIS <?= e($orderCode) ?>">
          <?php else: ?>
            <div class="note">QRIS kadaluwarsa.</div>
            <div class="note" style="margin-top:8px;">
              Coba buat order QRIS baru untuk memproses pembelian anda.
            </div>
          <?php endif; ?>
        </div>

        <div class="qrisActions">
          <button id="downloadQris" class="qrisBtn <?= $qrisUrl ? '' : 'disabledBtn' ?>" type="button" <?= $qrisUrl ? '' : 'disabled' ?>>
            Unduh QRIS
          </button>

          <a class="qrisBtn qrisBtnGhost" href="/orders">Lihat Status Order</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const btn = document.getElementById('downloadQris');
    const img = document.getElementById('qrisImage');

    if (!btn) return;
    if (!img) return;

    btn.addEventListener('click', async () => {
      try {
        const res = await fetch(img.src);
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = 'QRIS-<?= e($orderCode) ?>.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        URL.revokeObjectURL(url);
      } catch (err) {
        alert('Gagal mengunduh QRIS. Di iPhone: tekan & tahan gambar lalu pilih Save Image.');
      }
    });
  })();
</script>