<?php use App\Lib\AdminUtil; $money=$money ?? fn(int $n)=>AdminUtil::moneyIdr($n); ?>
<div class="topbar">
  <div class="brand">
    <div class="brandTitle">Manage User</div>
    <div class="brandSub">Daftar user & saldo wallet</div>
  </div>
  <div class="topActions">
    <form method="GET" action="/admin/users" class="chip" style="gap:8px;">
      <input name="q" value="<?= ae((string)($q ?? '')) ?>" placeholder="Search email/nama" style="border:none;outline:none;font-family:inherit;">
      <button class="btn btnGhost" type="submit">Search</button>
    </form>
  </div>
</div>

<div class="panel">
  <div class="panelHead">Users</div>
  <div class="panelBody" style="padding:0;">
    <table>
      <thead>
      <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Saldo</th>
        <th>Created</th>
        <th>Action</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach (($rows ?? []) as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= ae((string)$u['name']) ?></td>
          <td><?= ae((string)$u['email']) ?></td>
          <td><?= ae($money((int)($u['balance_idr'] ?? 0))) ?></td>
          <td><?= ae((string)($u['created_at'] ?? '')) ?></td>
          <td><a class="btn btnBlue" href="/admin/users/detail?id=<?= (int)$u['id'] ?>">Detail</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
