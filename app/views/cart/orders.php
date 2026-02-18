<?php
use function App\Lib\e;
use function App\Lib\money_idr;

function is_pending($status): bool
{
  $s = strtoupper(trim((string) $status));
  return strpos($s, 'PENDING') !== false;
}
function is_paid($status): bool
{
  $s = strtoupper(trim((string) $status));
  return strpos($s, 'PAID') !== false;
}
?>

<style>
  /* ===== Orders responsive layout (scoped) ===== */
  .ordersControls {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
  }

  .filterChips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
  }

  .chipBtn {
    height: 36px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: #fff;
    color: #0f172a;
    font-weight: 700;
    font-size: 12.5px;
    padding: 0 12px;
    cursor: pointer;
    user-select: none;
    transition: background .15s, box-shadow .15s, border-color .15s;
  }

  .chipBtn:hover {
    background: #f8fafc;
    box-shadow: 0 10px 20px rgba(2, 6, 23, .06);
  }

  .chipBtn.active {
    border-color: rgba(79, 70, 229, .35);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, .10);
  }

  .ordersCount {
    color: var(--muted);
    font-weight: 600;
    font-size: 12.5px;
  }

  .searchRow {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
    margin: 10px 0 14px;
  }

  .searchWrap {
    flex: 1;
    min-width: 240px;
    position: relative;
  }

  .searchInput {
    width: 100%;
    height: 46px;
    border-radius: 14px;
    border: 1px solid var(--border);
    padding: 0 44px 0 14px;
    font-size: 14px;
    outline: none;
    background: #fff;
  }

  .searchBtn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 32px;
    height: 32px;
    border-radius: 10px;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    font-weight: 700;
    color: #475569;
  }

  .searchBtn:hover {
    background: #f1f5f9;
    border-color: #e2e8f0;
  }

  .ordersList {
    display: grid;
    gap: 12px;
  }

  .orderCard {
    border: 1px solid var(--border);
    border-radius: 16px;
    background: #fff;
    padding: 12px;
  }

  .orderRow {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    align-items: start;
  }

  .orderCode {
    font-weight: 700;
    font-size: 14px;
    margin: 0;
    line-height: 1.2;
  }

  .orderMeta {
    margin-top: 6px;
    color: var(--muted);
    font-weight: 600;
    font-size: 12.5px;
    line-height: 1.35;
  }

  .orderStatus {
    margin-top: 8px;
    display: inline-flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    font-weight: 700;
    font-size: 12.5px;
  }

  .statusBadge {
    display: inline-flex;
    align-items: center;
    height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: #f8fafc;
  }

  .statusPaid {
    border-color: rgba(16, 185, 129, .35);
    background: rgba(16, 185, 129, .10);
    color: #065f46;
  }

  .statusPending {
    border-color: rgba(245, 158, 11, .35);
    background: rgba(245, 158, 11, .10);
    color: #92400e;
  }

  .orderRight {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }

  .orderTotal {
    font-weight: 700;
    font-size: 16px;
    margin: 0;
  }

  .orderActions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
  }

  .payBtn {
    height: 44px;
    padding: 0 16px;
    border-radius: 14px;
    background: var(--primary);
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(79, 70, 229, .25);
    box-shadow: 0 10px 18px rgba(79, 70, 229, .20);
    width: 100%;
  }

  .payBtn:hover {
    background: var(--primaryHover);
  }

  .detailBtn {
    height: 44px;
    padding: 0 16px;
    border-radius: 14px;
    background: #fff;
    color: #0f172a;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    width: 100%;
  }

  .detailBtn:hover {
    background: #f8fafc;
    box-shadow: 0 10px 20px rgba(2, 6, 23, .06);
  }

  .paidText {
    font-weight: 700;
    color: var(--success);
    display: inline-flex;
    gap: 8px;
    align-items: center;
  }

  .noResult {
    display: none;
    margin-top: 12px;
    font-weight: 700;
    color: var(--muted);
  }

  /* Desktop layout */
  @media (min-width: 720px) {
    .orderRow {
      grid-template-columns: 1.3fr .7fr;
      align-items: center;
    }

    .orderRight {
      align-items: flex-end;
      text-align: right;
    }

    .orderActions {
      justify-content: flex-end;
      width: auto;
    }

    .payBtn,
    .detailBtn {
      width: auto;
      min-width: 140px;
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
        <a class="pill" href="/dashboard">Dashboard</a>
        <a class="pill" href="/checkout">Checkout</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>

    <div class="heroTitle">Orders</div>
    <p class="heroSub">Filter status & cari order code / email.</p>
  </div>

  <div class="card">
    <div class="panel">
      <div class="panelHead">
        <p class="panelTitle">Riwayat Order</p>
        <p class="panelSub">Responsif + Filter + Search</p>
      </div>

      <div class="panelBody">
        <?php if (empty($orders)): ?>
          <div class="note">Belum ada order.</div>
        <?php else: ?>

          <!-- Filter + count -->
          <div class="ordersControls">
            <div class="filterChips">
              <button type="button" class="chipBtn active" data-filter="all">All</button>
              <button type="button" class="chipBtn" data-filter="pending">Pending</button>
              <button type="button" class="chipBtn" data-filter="paid">Paid</button>
            </div>
            <div class="ordersCount" id="ordersCount">Menampilkan semua</div>
          </div>

          <!-- Search -->
          <div class="searchRow">
            <div class="searchWrap">
              <input id="orderSearch" class="searchInput" type="text"
                placeholder="Cari order: kode FP..., email pembeli/penerima, provider..." />
              <button id="clearOrderSearch" class="searchBtn" type="button" aria-label="Clear">✕</button>
            </div>
          </div>

          <!-- List -->
          <div class="ordersList" id="ordersList">
            <?php foreach ($orders as $o): ?>
              <?php
              $code = (string) ($o['order_code'] ?? '');
              $status = (string) ($o['status'] ?? '');
              $provider = (string) ($o['payment_provider'] ?? '');
              $pending = is_pending($status);
              $paid = is_paid($status);

              $filterKey = $paid ? 'paid' : 'pending';

              $hay = strtolower(
                $code . ' ' .
                ((string) $o['buyer_email']) . ' ' .
                ((string) $o['receiver_email']) . ' ' .
                $provider . ' ' . $status
              );
              ?>

              <div class="orderCard orderItem" data-status="<?= e($filterKey) ?>" data-search="<?= e($hay) ?>">
                <div class="orderRow">
                  <!-- Left -->
                  <div>
                    <p class="orderCode">
                      <a class="link" href="/order?code=<?= e($code) ?>" style="font-weight:700;">
                        Order <span class="mono"><?= e($code) ?></span>
                      </a>
                    </p>

                    <div class="orderMeta">
                      Pembeli: <?= e((string) $o['buyer_email']) ?><br>
                      Penerima: <?= e((string) $o['receiver_email']) ?>
                    </div>

                    <div class="orderStatus">
                      <?php if ($paid): ?>
                        <span class="statusBadge statusPaid">PAID</span>
                      <?php else: ?>
                        <span class="statusBadge statusPending">PENDING</span>
                      <?php endif; ?>

                      <?php if ($provider): ?>
                        <span class="statusBadge">Provider: <?= e($provider) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Right -->
                  <div class="orderRight">
                    <p class="orderTotal"><?= e(money_idr((int) $o['total'])) ?></p>

                    <div class="orderActions">
                      <a class="detailBtn" href="/order?code=<?= e($code) ?>">Detail</a>

                      <?php if ($pending): ?>
                        <a class="payBtn" href="/pay?order=<?= e($code) ?>">Bayar</a>
                      <?php else: ?>
                        <div class="paidText">Terbayar ✅</div>
                      <?php endif; ?>
                    </div>

                  </div>
                </div>
              </div>

            <?php endforeach; ?>
          </div>

          <div id="noResult" class="noResult">Tidak ada order yang cocok.</div>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const btns = Array.from(document.querySelectorAll('.chipBtn'));
    const items = Array.from(document.querySelectorAll('.orderItem'));
    const countEl = document.getElementById('ordersCount');

    const searchInput = document.getElementById('orderSearch');
    const clearBtn = document.getElementById('clearOrderSearch');
    const noResult = document.getElementById('noResult');

    let currentFilter = 'all';

    function apply() {
      const q = (searchInput.value || '').trim().toLowerCase();
      let shown = 0;

      items.forEach(el => {
        const st = el.getAttribute('data-status');  // paid/pending
        const hay = el.getAttribute('data-search') || '';
        const okFilter = (currentFilter === 'all') || (st === currentFilter);
        const okSearch = !q || hay.includes(q);
        const ok = okFilter && okSearch;

        el.style.display = ok ? '' : 'none';
        if (ok) shown++;
      });

      // counter text
      const label = (currentFilter === 'all') ? 'All' : (currentFilter === 'pending') ? 'Pending' : 'Paid';
      countEl.textContent = label + ' • Hasil: ' + shown;

      noResult.style.display = (shown === 0) ? '' : 'none';
    }

    btns.forEach(b => {
      b.addEventListener('click', () => {
        currentFilter = b.getAttribute('data-filter');
        btns.forEach(x => x.classList.toggle('active', x === b));
        apply();
      });
    });

    searchInput.addEventListener('input', apply);
    clearBtn.addEventListener('click', () => {
      searchInput.value = '';
      searchInput.focus();
      apply();
    });

    apply();
  })();
</script>