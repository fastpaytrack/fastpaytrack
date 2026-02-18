<?php use function App\Lib\e; ?>

<div class="aPageHead">
  <div class="aTitle">Admin Settings</div>
</div>

<div class="aGrid2">
  <div class="aCard">
    <div class="aCardTitle">Profile</div>

    <form method="POST" action="/admin/settings/profile" class="aForm">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <div class="aField">
        <label>Name</label>
        <input class="aInput" name="name" value="<?= e((string)($me['name'] ?? '')) ?>" required>
      </div>

      <div class="aField">
        <label>Email</label>
        <input class="aInput" name="email" type="email" value="<?= e((string)($me['email'] ?? '')) ?>" required>
      </div>

      <div class="aField">
        <label>Username</label>
        <input class="aInput" name="username" value="<?= e((string)($me['username'] ?? '')) ?>" required>
      </div>

      <button class="aBtn aBtnPrimary" type="submit">Save Profile</button>
    </form>

    <hr class="aHr">

    <div class="aCardTitle">Change Password</div>
    <form method="POST" action="/admin/settings/password" class="aForm">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <div class="aField">
        <label>Old Password</label>
        <input class="aInput" name="old_password" type="password" required>
      </div>

      <div class="aField">
        <label>New Password</label>
        <input class="aInput" name="new_password" type="password" required>
      </div>

      <button class="aBtn aBtnPrimary" type="submit">Update Password</button>
    </form>
  </div>

  <div class="aCard">
    <div class="aCardTitle">Access Key</div>
    <div class="aMuted">Key disimpan dalam bentuk hash. Kamu bisa generate ulang kapan saja.</div>

    <form method="POST" action="/admin/settings/key" style="margin-top:14px">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <button class="aBtn aBtnPrimary" type="submit" onclick="return confirm('Generate key baru? Simpan key baru setelah muncul notifikasi.')">
        Generate New Key
      </button>
    </form>

    <hr class="aHr">

    <div class="aCardTitle">Last Login</div>
    <div class="aMiniList">
      <div class="aMiniRow">
        <div class="aMuted">IP</div>
        <div class="aMono"><?= e((string)($me['last_login_ip'] ?? '-')) ?></div>
      </div>
      <div class="aMiniRow">
        <div class="aMuted">Device</div>
        <div class="aMono"><?= e((string)($me['last_login_ua'] ?? '-')) ?></div>
      </div>
      <div class="aMiniRow">
        <div class="aMuted">Last Login At</div>
        <div class="aMono"><?= e((string)($me['last_login_at'] ?? '-')) ?></div>
      </div>
    </div>
  </div>
</div>
