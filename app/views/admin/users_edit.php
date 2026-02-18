<?php use function App\Lib\e; use function App\Lib\money_idr; ?>

<div class="aPageHead">
  <div class="aTitle">User Detail</div>
  <div class="aActions">
    <a class="aBtn aBtnGhost" href="/admin/users">Back</a>
  </div>
</div>

<div class="aGrid2">
  <div class="aCard">
    <div class="aCardTitle">Profile</div>

    <form method="POST" action="/admin/users/edit" class="aForm">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">

      <div class="aField">
        <label>Name</label>
        <input class="aInput" name="name" value="<?= e((string)$u['name']) ?>" required>
      </div>

      <div class="aField">
        <label>Email</label>
        <input class="aInput" name="email" type="email" value="<?= e((string)$u['email']) ?>" required>
      </div>

      <div class="aGrid2">
        <div class="aField">
          <label>Status</label>
          <select class="aInput" name="is_active">
            <option value="1" <?= ((int)$u['is_active']===1?'selected':'') ?>>Active</option>
            <option value="0" <?= ((int)$u['is_active']===0?'selected':'') ?>>Inactive</option>
          </select>
        </div>
        <div class="aField">
          <label>New Password (optional)</label>
          <input class="aInput" name="new_password" type="password" placeholder="Leave empty to keep">
        </div>
      </div>

      <button class="aBtn aBtnPrimary" type="submit">Save</button>
    </form>

    <hr class="aHr">

    <div class="aCardTitle">Wallet</div>
    <div class="aRowFlex" style="justify-content:space-between">
      <div class="aMuted">Current Balance</div>
      <div class="aStrong aMono"><?= e(money_idr((int)$u['balance_idr'])) ?></div>
    </div>

    <form method="POST" action="/admin/users/balance" class="aForm" style="margin-top:12px">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
      <div class="aGrid2">
        <div class="aField">
          <label>Adjust (IDR) — can be negative</label>
          <input class="aInput aMono" name="delta_idr" placeholder="ex: 50000 or -25000">
        </div>
        <div class="aField" style="display:flex;align-items:flex-end">
          <button class="aBtn aBtnPrimary" type="submit">Apply</button>
        </div>
      </div>
    </form>

    <hr class="aHr">

    <form method="POST" action="/admin/users/delete" onsubmit="return confirm('Delete user ini?')">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= e((string)$u['id']) ?>">
      <button class="aBtn aBtnDanger" type="submit">Delete User</button>
    </form>
  </div>

  <div class="aCard">
    <div class="aCardTitle">Recent Activity</div>

    <div class="aSectionTitle">Orders</div>
    <?php if (empty($orders)): ?>
      <div class="aMuted">No orders.</div>
    <?php else: ?>
      <div class="aMiniList">
        <?php foreach ($orders as $r): ?>
          <div class="aMiniRow">
            <div>
              <div class="aStrong">#<?= e((string)$r['id']) ?> • <?= e((string)$r['status']) ?></div>
              <div class="aMuted"><?= e((string)$r['created_at']) ?></div>
            </div>
            <div class="aMono"><?= e(money_idr((int)$r['total_idr'])) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="aSectionTitle" style="margin-top:16px">Topups</div>
    <?php if (empty($topups)): ?>
      <div class="aMuted">No topups.</div>
    <?php else: ?>
      <div class="aMiniList">
        <?php foreach ($topups as $r): ?>
          <div class="aMiniRow">
            <div>
              <div class="aStrong">#<?= e((string)$r['id']) ?> • <?= e((string)$r['status']) ?></div>
              <div class="aMuted"><?= e((string)$r['created_at']) ?></div>
            </div>
            <div class="aMono"><?= e(money_idr((int)$r['amount_idr'])) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="aSectionTitle" style="margin-top:16px">Transfers</div>
    <?php if (empty($transfers)): ?>
      <div class="aMuted">No transfers.</div>
    <?php else: ?>
      <div class="aMiniList">
        <?php foreach ($transfers as $r): ?>
          <div class="aMiniRow">
            <div>
              <div class="aStrong">#<?= e((string)$r['id']) ?></div>
              <div class="aMuted"><?= e((string)$r['created_at']) ?></div>
            </div>
            <div class="aMono"><?= e(money_idr((int)$r['amount_idr'])) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
