<?php use function App\Lib\e; use function App\Lib\money_idr; ?>

<div class="aPageHead">
  <div class="aTitle">Transactions</div>
</div>

<div class="aTabs">
  <a class="aTab <?= ($tab==='orders'?'isActive':'') ?>" href="/admin/transactions?tab=orders">Orders</a>
  <a class="aTab <?= ($tab==='topups'?'isActive':'') ?>" href="/admin/transactions?tab=topups">Topups</a>
  <a class="aTab <?= ($tab==='transfers'?'isActive':'') ?>" href="/admin/transactions?tab=transfers">Transfers</a>
</div>

<div class="aCard">
  <div class="aTableWrap">
    <table class="aTable">
      <thead>
      <?php if ($tab==='orders'): ?>
        <tr>
          <th>ID</th><th>Order Code</th><th>User</th><th>Payment</th><th>Status</th><th style="text-align:right">Total</th><th>Date</th>
        </tr>
      <?php elseif ($tab==='topups'): ?>
        <tr>
          <th>ID</th><th>User</th><th>Provider</th><th>Status</th><th style="text-align:right">Amount</th><th>Date</th>
        </tr>
      <?php else: ?>
        <tr>
          <th>ID</th><th>From</th><th>To</th><th>Method</th><th style="text-align:right">Amount</th><th>Date</th>
        </tr>
      <?php endif; ?>
      </thead>
      <tbody>
      <?php if ($tab==='orders'): ?>
        <?php if (empty($orders)): ?><tr><td colspan="7" class="aEmpty">No orders.</td></tr><?php endif; ?>
        <?php foreach ($orders as $r): ?>
          <tr>
            <td class="aMono"><?= e((string)$r['id']) ?></td>
            <td class="aMono"><?= e((string)$r['order_code']) ?></td>
            <td>
              <div class="aStrong"><?= e((string)$r['user_name']) ?></div>
              <div class="aMuted"><?= e((string)$r['user_email']) ?></div>
            </td>
            <td class="aMono"><?= e((string)$r['payment_method']) ?></td>
            <td><span class="aPill <?= (strtoupper((string)$r['status'])==='PAID'?'isOn':'isWarn') ?>"><?= e((string)$r['status']) ?></span></td>
            <td class="aMono" style="text-align:right"><?= e(money_idr((int)$r['total_idr'])) ?></td>
            <td class="aMuted"><?= e((string)$r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>

      <?php elseif ($tab==='topups'): ?>
        <?php if (empty($topups)): ?><tr><td colspan="6" class="aEmpty">No topups.</td></tr><?php endif; ?>
        <?php foreach ($topups as $r): ?>
          <tr>
            <td class="aMono"><?= e((string)$r['id']) ?></td>
            <td>
              <div class="aStrong"><?= e((string)$r['user_name']) ?></div>
              <div class="aMuted"><?= e((string)$r['user_email']) ?></div>
            </td>
            <td class="aMono"><?= e((string)$r['provider']) ?></td>
            <td><span class="aPill <?= (strtoupper((string)$r['status'])==='APPROVED'?'isOn':'isWarn') ?>"><?= e((string)$r['status']) ?></span></td>
            <td class="aMono" style="text-align:right"><?= e(money_idr((int)$r['amount_idr'])) ?></td>
            <td class="aMuted"><?= e((string)$r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>

      <?php else: ?>
        <?php if (empty($transfers)): ?><tr><td colspan="6" class="aEmpty">No transfers.</td></tr><?php endif; ?>
        <?php foreach ($transfers as $r): ?>
          <tr>
            <td class="aMono"><?= e((string)$r['id']) ?></td>
            <td>
              <div class="aStrong"><?= e((string)$r['from_name']) ?></div>
              <div class="aMuted"><?= e((string)$r['from_email']) ?></div>
            </td>
            <td>
              <div class="aStrong"><?= e((string)$r['to_name']) ?></div>
              <div class="aMuted"><?= e((string)$r['to_email']) ?></div>
            </td>
            <td class="aMono">FastPayTrack Balance</td>
            <td class="aMono" style="text-align:right"><?= e(money_idr((int)$r['amount_idr'])) ?></td>
            <td class="aMuted"><?= e((string)$r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
