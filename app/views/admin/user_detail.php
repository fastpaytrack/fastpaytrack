<?php use App\Lib\AdminUtil; $money=$money ?? fn(int $n)=>AdminUtil::moneyIdr($n); ?>
<div class="topbar">
  <div class="brand">
    <div class="brandTitle">User Detail</div>
    <div class="brandSub"><?= ae((string)($u['email'] ?? '')) ?></div>
  </div>
  <div class="topActions">
    <a class="btn btnGhost" href="/admin/users">Back</a>
  </div>
</div>

<div class="grid2">
  <div class="panel">
    <div class="panelHead">Profile</div>
    <div class="panelBody">
      <div class="field"><label>Nama</label><input value="<?= ae((string)($u['name'] ?? '')) ?>" disabled></div>
      <div class="field"><label>Email</label><input value="<?= ae((string)($u['email'] ?? '')) ?>" disabled></div>
      <div class="field"><label>Saldo Wallet</label><input value="<?= ae($money((int)($u['balance_idr'] ?? 0))) ?>" disabled></div>

      <form method="POST" action="/admin/users/add-balance" style="margin-top:12px;">
        <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int)($u['id'] ?? 0) ?>">
        <div class="field">
          <label>Tambah Saldo (IDR)</label>
          <input name="amount_idr" placeholder="50000">
        </div>
        <button class="btn btnBlue" type="submit" style="width:100%;height:44px;">Add Balance</button>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panelHead">Login Devices</div>
    <div class="panelBody">
      <?php if (empty($devices)): ?>
        <div style="color:var(--muted);font-weight:600;">Tidak ada data device.</div>
      <?php else: ?>
        <?php foreach ($devices as $d): ?>
          <div style="padding:10px 0;border-bottom:1px solid #eef2f7;">
            <div style="font-weight:800;"><?= ae((string)($d['ip_address'] ?? '')) ?></div>
            <div style="color:var(--muted);font-size:12px;font-weight:600;"><?= ae((string)($d['user_agent'] ?? '')) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="panel" style="margin-top:14px;">
  <div class="panelHead">Recent Activity</div>
  <div class="panelBody" style="padding:0;">
    <table>
      <thead>
      <tr><th>Type</th><th>Ref</th><th>Amount</th><th>Date</th></tr>
      </thead>
      <tbody>
      <?php foreach (($activity ?? []) as $a): ?>
        <tr>
          <td><?= ae((string)$a['type']) ?></td>
          <td><?= ae((string)$a['ref_id']) ?></td>
          <td><?= ae($money((int)($a['amount_idr'] ?? 0))) ?></td>
          <td><?= ae((string)$a['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
