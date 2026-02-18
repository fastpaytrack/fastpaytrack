<?php use function App\Lib\e; ?>

<div class="aPageHead">
  <div class="aTitle">Add User</div>
  <div class="aActions">
    <a class="aBtn aBtnGhost" href="/admin/users">Back</a>
  </div>
</div>

<div class="aCard">
  <div class="aCardTitle">User Info</div>

  <form method="POST" action="/admin/users/create" class="aForm" style="max-width:720px">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div class="aGrid2">
      <div class="aField">
        <label>Name</label>
        <input class="aInput" name="name" required>
      </div>

      <div class="aField">
        <label>Email</label>
        <input class="aInput" name="email" type="email" required>
      </div>
    </div>

    <div class="aGrid2">
      <div class="aField">
        <label>Password</label>
        <input class="aInput" name="password" type="password" required>
      </div>

      <div class="aField">
        <label>Initial Wallet (IDR)</label>
        <input class="aInput aMono" name="balance_idr" placeholder="0">
      </div>
    </div>

    <button class="aBtn aBtnPrimary" type="submit">Create User</button>
  </form>
</div>
