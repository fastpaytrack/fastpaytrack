<?php use App\Lib\AdminUtil; $money = $money ?? fn(int $n)=>AdminUtil::moneyIdr($n); ?>
<div class="topbar">
  <div class="brand">
    <div class="brandTitle">Orders</div>
    <div class="brandSub">Kelola pengiriman voucher/gift card</div>
  </div>
</div>

<div class="panel">
  <div class="panelHead">Recent Orders</div>
  <div class="panelBody" style="padding:0;">
    <table>
      <thead>
      <tr>
        <th>Date</th>
        <th>Customer</th>
        <th>Order ID</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Delivery</th>
        <th>Action</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach (($rows ?? []) as $o): ?>
        <?php
          $paid = ((string)$o['status'] ?? '') === 'paid';
          $delivered = ((int)($o['delivered_count'] ?? 0)) > 0;
          $st = $paid ? '<span class="pillStatus sPaid">● Paid</span>' : '<span class="pillStatus sPending">● Pending</span>';
          $dl = $delivered ? '<span class="pillStatus sDelivered">✓ Delivered</span>' : '<span class="pillStatus sPending">Not Yet</span>';
        ?>
        <tr>
          <td><?= ae((string)($o['created_at'] ?? '')) ?></td>
          <td><?= ae((string)($o['user_email'] ?? '')) ?></td>
          <td><?= ae((string)($o['order_code'] ?? $o['id'] ?? '')) ?></td>
          <td><?= ae($money((int)($o['total_amount_idr'] ?? 0))) ?></td>
          <td><?= $st ?></td>
          <td><?= $dl ?></td>
          <td>
            <a class="btn btnBlue" href="/admin/orders/send?id=<?= (int)$o['id'] ?>">Open</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
