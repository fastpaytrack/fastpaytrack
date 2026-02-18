<?php
use function App\Lib\e;

$reason = (string) ($reason ?? 'error');

$title = 'Transfer Gagal ❌';
$msg = 'Transfer gagal diproses. Silakan coba lagi.';
if ($reason === 'insufficient') {
  $msg = 'Saldo kamu tidak mencukupi untuk melakukan transfer ini.';
}
?>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brand">
        <div class="logo">A</div>FastPayTrack
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="pill" href="/dashboard">Dashboard</a>
        <a class="pill" href="/wallet/transfer">Transfer</a>
        <a class="pill" href="/topup">Topup</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>
    <div class="heroTitle"><?= e($title) ?></div>
    <p class="heroSub"><?= e($msg) ?></p>
  </div>

  <div class="card">
    <div class="panel">
      <div class="panelHead">
        <p class="panelTitle">Konfirmasi</p>
        <p class="panelSub">Silakan pilih tindakan berikut</p>
      </div>

      <div class="panelBody">
        <div style="border:1px solid var(--border);border-radius:18px;padding:14px;background:#fff;">
          <div style="font-weight:700;"><?= e($msg) ?></div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
            <a class="btn btnPrimary" href="/wallet/transfer"
              style="width:auto;text-decoration:none;display:inline-grid;place-items:center;">
              Coba Lagi
            </a>
            <?php if ($reason === 'insufficient'): ?>
              <a class="btn btnGhost" href="/topup"
                style="width:auto;text-decoration:none;display:inline-grid;place-items:center;">
                Topup Saldo
              </a>
            <?php endif; ?>
            <a class="btn btnGhost" href="/dashboard"
              style="width:auto;text-decoration:none;display:inline-grid;place-items:center;">
              Kembali ke Dashboard
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>