<?php
use App\Lib\AdminUtil;
$money = $money ?? fn(int $n) => AdminUtil::moneyIdr($n);
?>
<div class="topbar">
  <div class="brand">
    <div class="brandTitle">FASTPAYTRACK.</div>
    <div class="brandSub">Dashboard Super Admin</div>
  </div>
  <div class="topActions">
    <div class="chip">🔔 Notifications</div>
    <div class="chip">⚙️ Settings</div>
  </div>
</div>

<div class="grid3">
  <div class="statCard">
    <div class="statTitle">Gross Income</div>
    <div class="statValue"><?= ae($money((int)$grossIncome)) ?></div>
    <div class="statHint">Current Balance</div>
  </div>

  <div class="statCard blue">
    <div class="statTitle">Wallet Balance</div>
    <div class="statValue"><?= ae($money((int)$walletTotal)) ?></div>
    <div class="statHint">User Wallet Balance</div>
  </div>

  <div class="statCard dark">
    <div class="statTitle">Earnings</div>
    <div class="statValue"><?= ae($money((int)$earnings)) ?></div>
    <div class="statHint">Available Balance</div>
  </div>
</div>

<div class="grid3" style="margin-top:14px;">
  <div class="statCard">
    <div class="statTitle">Total User</div>
    <div class="statValue"><?= ae((string)$totalUser) ?></div>
  </div>
  <div class="statCard">
    <div class="statTitle">User Active</div>
    <div class="statValue"><?= ae((string)$userActive) ?></div>
  </div>
  <div class="statCard">
    <div class="statTitle">Online User</div>
    <div class="statValue"><?= ae((string)$onlineUser) ?></div>
  </div>
</div>

<div class="grid3" style="margin-top:14px;">
  <div class="statCard">
    <div class="statTitle">Total Products</div>
    <div class="statValue"><?= ae((string)$totalProducts) ?></div>
  </div>
  <div class="statCard">
    <div class="statTitle">Total Products Available</div>
    <div class="statValue">—</div>
  </div>
  <div class="statCard">
    <div class="statTitle">Total Products Sold</div>
    <div class="statValue"><?= ae((string)$totalProductsSold) ?></div>
  </div>
</div>

<div class="grid2">
  <div class="panel">
    <div class="panelHead">Notifications Center <a href="/admin/transactions" class="btn btnGhost">View all</a></div>
    <div class="panelBody">
      <?php if (empty($notif)): ?>
        <div style="color:var(--muted);font-weight:600;">Belum ada notifikasi.</div>
      <?php else: ?>
        <?php foreach ($notif as $n): ?>
          <?php
            $title = $n['type']==='topup' ? 'Topup e-wallet' : ($n['type']==='transfer' ? 'Transfer' : 'Order');
            $desc = $n['email'] ? ($n['email'].' • '.$money((int)$n['amount'])) : $money((int)$n['amount']);
          ?>
          <div style="padding:10px 0;border-bottom:1px solid #eef2f7;">
            <div style="font-weight:800;"><?= ae($title) ?></div>
            <div style="color:var(--muted);font-size:12px;font-weight:600;"><?= ae($desc) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panelHead">Recent Order <a href="/admin/orders" class="btn btnGhost">View all</a></div>
    <div class="panelBody" style="padding:0;">
      <table>
        <thead>
          <tr>
            <th>Date & Time</th>
            <th>Item</th>
            <th>Order ID</th>
            <th>Amount</th>
            <th>Payment</th>
            <th>Order Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($recentOrders ?? []) as $o): ?>
            <?php
              $status = (string)($o['status'] ?? '');
              $paid = $status === 'paid';
              $pending = $status === 'pending';

              $delivered = ((int)($o['delivered_count'] ?? 0)) > 0;

              $paymentPill = $paid ? '<span class="pillStatus sPaid">● Paid</span>' : ($pending ? '<span class="pillStatus sPending">● Pending</span>' : '<span class="pillStatus sPending">● Pending</span>');

              if ($paid && !$delivered) {
                $orderPill = '<a class="btn btnBlue" href="/admin/orders/send?id='.(int)$o['id'].'" style="height:28px;border-radius:999px;padding:0 12px;font-size:11px;">Sent Item</a>';
              } elseif ($paid && $delivered) {
                $orderPill = '<span class="pillStatus sDelivered">✓ Delivered</span>';
              } else {
                $orderPill = '<span class="pillStatus sPending">Pending</span>';
              }

              $items = '(items)';
              // best effort: tampilkan 1 item dari order_items
              $items = $items;
            ?>
            <tr>
              <td><?= ae((string)($o['created_at'] ?? '')) ?></td>
              <td><?= ae((string)($o['item_name'] ?? '')) ?></td>
              <td><?= ae((string)($o['order_code'] ?? $o['id'] ?? '')) ?></td>
              <td><?= ae($money((int)($o['total_amount_idr'] ?? 0))) ?></td>
              <td><?= $paymentPill ?></td>
              <td><?= $orderPill ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="panel" style="margin-top:14px;">
  <div class="panelHead">Order Status</div>
  <div class="panelBody" style="padding:0;">
    <table>
      <thead>
      <tr>
        <th>Date & Time</th>
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
      <?php foreach (($orderStatus ?? []) as $r): ?>
        <?php
          $st = (string)($r['status'] ?? '');
          $pill = $st==='paid' ? '<span class="pillStatus sPaid">Completed</span>' : '<span class="pillStatus sPending">'.ae($st ?: 'Pending').'</span>';
        ?>
        <tr>
          <td><?= ae((string)($r['created_at'] ?? '')) ?></td>
          <td><?= ae((string)($r['items'] ?? '')) ?></td>
          <td><?= ae((string)($r['order_code'] ?? '')) ?></td>
          <td>Payment</td>
          <td><?= ae((string)($r['payment_method'] ?? '')) ?></td>
          <td><?= $pill ?></td>
          <td><?= ae($money((int)($r['total_amount_idr'] ?? 0))) ?></td>
          <td><?= ae((string)($r['user_email'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
