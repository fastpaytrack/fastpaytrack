<div style="max-width:520px;margin:30px auto;">
  <div class="panel">
    <div class="panelHead">Verification</div>
    <div class="panelBody">
      <form method="POST" action="/admin/login/password">
        <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
        <div class="field">
          <label>Username</label>
          <input value="<?= ae($username ?? '') ?>" disabled>
        </div>
        <div class="field">
          <label>Password</label>
          <input name="password" type="password" placeholder="Masukkan password">
        </div>
        <button class="btn btnBlue" type="submit" style="width:100%;height:44px;">Continue</button>
      </form>
    </div>
  </div>
</div>
