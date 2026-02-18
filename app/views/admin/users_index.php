<?php use function App\Lib\e; use function App\Lib\money_idr; ?>

<div class="aPageHead">
  <div class="aTitle">Manage Users</div>
  <div class="aActions">
    <form class="aSearch" method="GET" action="/admin/users">
      <input class="aInput" name="q" value="<?= e($q ?? '') ?>" placeholder="Search name/email..." />
    </form>
    <a class="aBtn aBtnPrimary" href="/admin/users/create">Add User</a>
  </div>
</div>

<div class="aCard">
  <div class="aTableWrap">
    <table class="aTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Wallet</th>
          <th>Status</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($users)): ?>
        <tr><td colspan="5" class="aEmpty">No users.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="aMono"><?= e((string)$u['id']) ?></td>
            <td>
              <div class="aStrong"><?= e((string)$u['name']) ?></div>
              <div class="aMuted"><?= e((string)$u['email']) ?></div>
            </td>
            <td class="aMono"><?= e(money_idr((int)$u['balance_idr'])) ?></td>
            <td>
              <span class="aPill <?= ((int)$u['is_active']===1?'isOn':'isOff') ?>">
                <?= ((int)$u['is_active']===1?'Active':'Inactive') ?>
              </span>
            </td>
            <td style="text-align:right">
              <a class="aBtn aBtnGhost" href="/admin/users/edit?id=<?= e((string)$u['id']) ?>">Detail</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
