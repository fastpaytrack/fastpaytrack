<?php
use App\Lib\AdminUtil;

$fmt = $fmt ?? fn(int $n) => AdminUtil::money_idr($n);

function badgePayment(string $payment): string {
  $p = strtoupper($payment);
  if ($p === 'PAID') return 'badge bPaid';
  if ($p === 'PENDING') return 'badge bPending';
  return 'badge';
}

function badgeStatus(string $status): string {
  $s = strtoupper($status);
  if (in_array($s, ['DELIVERED','COMPLETED'], true)) return 'badge bDelivered';
  if ($s === 'PAID') return 'badge bPaid';
  if ($s === 'PENDING') return 'badge bPending';
  if ($s === 'EXPIRED') return 'badge bExpired';
  return 'badge';
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
?>
<div class="adminWrap">
  <aside class="side">
    <div class="profile">
      <div class="avatar"></div>
      <div style="min-width:0;">
        <div class="pname"><?= e($me['name'] ?? 'Admin') ?></div>
        <div class="pmail"><?= e($me['email'] ?? '') ?></div>
      </div>
    </div>

    <nav class="nav">
      <a class="active" href="/admin/dashboard">Dashboard</a>
      <a href="/admin/products">Product</a>
      <a href="/admin/users">Manage User</a>
      <a href="/admin/transactions">Transactions</a>
      <a href="/admin/store">Manage Store</a>
      <a href="/admin/settings">Settings</a>
    </nav>

    <a class="logout" href="/admin/logout">Logout</a>
  </aside>

  <main class="main">
    <div class="topbar">
      <div class="brand">
        <div>
          FASTPAYTRACK.
          <small>Dashboard Super Admin</small>
        </div>
      </div>
      <div class="topIcons">
        <div class="iconBtn" title="Notifications">🔔</div>
        <div class="iconBtn" title="Settings">⚙️</div>
      </div>
    </div>

    <section class="gridCards">
      <div class="card">
        <div class="ctitle">Gross Income</div>
        <div class="cvalue"><?= e($fmt((int)$cards['gross_income'])) ?></div>
        <div class="csub">Current Balance</div>
      </div>

      <div class="card blue">
        <div class="ctitle">Wallet Balance</div>
        <div class="cvalue"><?= e($fmt((int)$cards['wallet_total'])) ?></div>
        <div class="csub">User Wallet Balance</div>
      </div>

      <div class="card black">
        <div class="ctitle">Earnings</div>
        <div class="cvalue"><?= e($fmt((int)$cards['earnings'])) ?></div>
        <div class="csub">Available Balance</div>
      </div>

      <div class="card">
        <div class="ctitle">Total User</div>
        <div class="cvalue"><?= (int)$cards['total_users'] ?></div>
      </div>

      <div class="card">
        <div class="ctitle">User Active</div>
        <div class="cvalue"><?= (int)$cards['active_users'] ?></div>
      </div>

      <div class="card">
        <div class="ctitle">Online User</div>
        <div class="cvalue"><?= (int)$cards['online_users'] ?></div>
      </div>

      <div class="card">
        <div class="ctitle">Total Products</div>
        <div class="cvalue"><?= (int)$cards['total_products'] ?></div>
      </div>

      <div class="card">
        <div class="ctitle">Total Products Sold</div>
        <div class="cvalue"><?= (int)$cards['total_products_sold'] ?></div>
      </div>

      <div class="card">
        <div class="ctitle">Total Products Available</div>
        <div class="cvalue">Skip</div>
      </div>
    </section>

    <section class="gridMid">
      <div class="panel">
        <div class="panelHead">
          <span>Notifications Center</span>
          <a href="/admin/transactions" style="color:var(--blue);font-weight:800;font-size:11px;">View all</a>
        </div>
        <div class="panelBody">

          <div class="notifItem">
            <div class="notifTitle">Topup e-wallet</div>
            <?php foreach (($notif['topups'] ?? []) as $t): ?>
              <div class="notifSub">
                <?= e($t['email'] ?? '') ?> topup <?= e($fmt((int)$t['amount_idr'])) ?> • <?= e((string)$t['status']) ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="notifItem">
            <div class="notifTitle">Transfer</div>
            <?php foreach (($notif['transfers'] ?? []) as $tr): ?>
              <div class="notifSub">
                <?= e($tr['from_email'] ?? '') ?> → <?= e($tr['to_email'] ?? '') ?>
                <?= e($fmt((int)$tr['amount_idr'])) ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="notifItem">
            <div class="notifTitle">Order</div>
            <?php foreach (($notif['orders'] ?? []) as $o): ?>
              <div class="notifSub">
                <?= e($o['email'] ?? '') ?> order <?= e((string)$o['order_code']) ?>
                <?= e($fmt((int)$o['grand_total_idr'])) ?> • <?= e((string)$o['status']) ?>
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>

      <div class="panel">
        <div class="panelHead">
          <span>Recent Order</span>
          <a href="/admin/transactions?tab=orders" style="color:var(--blue);font-weight:800;font-size:11px;">View all</a>
        </div>
        <div class="panelBody" style="padding:0;">
          <table>
            <thead>
              <tr>
                <th>Date &amp; Time</th>
                <th>Item</th>
                <th>Order ID</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Order Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($recentOrders ?? []) as $r): ?>
                <?php
                  $status = strtoupper((string)$r['status']);
                  $delivered = (int)($r['delivered_count'] ?? 0) > 0 || $status === 'DELIVERED';
                  $canSend = in_array($status, ['PAID','COMPLETED'], true) && !$delivered;
                ?>
                <tr>
                  <td><?= e((string)$r['created_at']) ?></td>
                  <td><?= e((string)$r['item_title']) ?></td>
                  <td><?= e((string)$r['order_code']) ?></td>
                  <td><?= e($fmt((int)$r['grand_total_idr'])) ?></td>
                  <td><span class="<?= e($status === 'PENDING' ? 'badge bPending' : 'badge bPaid') ?>"><?= e($status === 'PENDING' ? 'Pending' : 'Paid') ?></span></td>
                  <td>
                    <?php if ($canSend): ?>
                      <a class="btnSmall" href="/admin/orders/send?order_id=<?= (int)$r['id'] ?>">Sent Item</a>
                    <?php else: ?>
                      <span class="<?= e(badgeStatus($status)) ?>"><?= e($delivered ? 'Delivered' : $status) ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:right;">▾</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="panel panelBottom">
      <div class="panelHead">
        <span>Order Status</span>
        <span>↗</span>
      </div>
      <div class="panelBody" style="padding:0;">
        <table>
          <thead>
            <tr>
              <th>Date &amp; Time</th>
              <th>Item</th>
              <th>Order ID</th>
              <th>Transaction Type</th>
              <th>Channel</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Customer Email</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($orderStatus ?? []) as $o): ?>
              <tr>
                <td><?= e((string)$o['created_at']) ?></td>
                <td><?= e((string)$o['item_title']) ?></td>
                <td><?= e((string)$o['order_code']) ?></td>
                <td><?= e((string)$o['transaction_type']) ?></td>
                <td><?= e((string)$o['channel']) ?></td>
                <td><span class="<?= e(badgeStatus((string)$o['status'])) ?>"><?= e((string)$o['status']) ?></span></td>
                <td><?= e($fmt((int)$o['grand_total_idr'])) ?></td>
                <td><?= e((string)$o['email']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>
