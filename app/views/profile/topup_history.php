<?php
use function App\Lib\e;
use function App\Lib\money_idr;

function status_badge(string $status): array
{
  $s = strtoupper(trim($status));
  if ($s === 'PAID')
    return ['PAID', 'rgba(16,185,129,.10)', 'rgba(16,185,129,.35)', '#065f46'];
  if ($s === 'FAILED')
    return ['FAILED', 'rgba(239,68,68,.10)', 'rgba(239,68,68,.35)', '#991b1b'];
  return ['PENDING', 'rgba(245,158,11,.10)', 'rgba(245,158,11,.35)', '#92400e'];
}
?>

<style>
  .topupList {
    display: grid;
    gap: 12px;
  }

  .topupCard {
    border: 1px solid var(--border);
    border-radius: 16px;
    background: #fff;
    padding: 12px;
  }

  .topupRow {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    align-items: start;
  }

  @media (min-width: 720px) {
    .topupRow {
      grid-template-columns: 1.2fr .8fr;
      align-items: center;
    }

    .rightBox {
      text-align: right;
    }
  }

  .code {
    font-weight: 700;
  }

  .meta {
    color: var(--muted);
    font-weight: 600;
    font-size: 12.5px;
    line-height: 1.35;
    margin-top: 6px;
  }

  .amount {
    font-weight: 700;
    font-size: 16px;
  }

  .badge {
    display: inline-flex;
    align-items: center;
    height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    border: 1px solid var(--border);
    font-weight: 700;
    font-size: 12px;
    margin-top: 8px;
  }
</style>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brand">
        <div class="logo">A</div>FastPayTrack
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="pill" href="/dashboard">Dashboard</a>
        <a class="pill" href="/topup">Topup</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>
    <div class="heroTitle">Riwayat Topup</div>
    <p class="heroSub">Daftar topup saldo wallet (PENDING/PAID/FAILED).</p>
  </div>

  <div class="card">
    <div class="panel">
      <div class="panelHead">
        <p class="panelTitle">Topup</p>
        <p class="panelSub">Topup PENDING akan menjadi PAID setelah webhook Stripe sukses</p>
      </div>

      <div class="panelBody">
        <?php if (empty($topups)): ?>
          <div class="note">Belum ada topup.</div>
        <?php else: ?>
          <div class="topupList">
            <?php foreach ($topups as $t): ?>
              <?php
              [$label, $bg, $br, $tx] = status_badge((string) ($t['status'] ?? 'PENDING'));
              $provider = (string) ($t['payment_provider'] ?? '-');
              ?>
              <div class="topupCard">
                <div class="topupRow">
                  <div>
                    <div class="code">Topup <span class="mono"><?= e((string) $t['topup_code']) ?></span></div>
                    <div class="meta">
                      Provider: <span class="mono"><?= e($provider ?: '-') ?></span><br>
                      Dibuat: <span class="mono"><?= e((string) $t['created_at']) ?></span>
                    </div>
                    <span class="badge" style="background:<?= e($bg) ?>;border-color:<?= e($br) ?>;color:<?= e($tx) ?>;">
                      <?= e($label) ?>
                    </span>
                  </div>

                  <div class="rightBox">
                    <div class="amount"><?= e(money_idr((int) $t['amount_idr'])) ?></div>
                    <div class="meta">
                      Update: <span class="mono"><?= e((string) ($t['updated_at'] ?? '-')) ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>