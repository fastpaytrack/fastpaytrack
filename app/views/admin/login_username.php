<?php use App\Lib\AdminUtil; ?>
<div style="max-width:520px;margin:30px auto;">
  <div class="panel">
    <div class="panelHead">Admin Login</div>
    <div class="panelBody">
      <form method="POST" action="/admin/login/username">
        <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
        <div class="field">
          <label>Username</label>
          <input name="username" autocomplete="off" placeholder="Masukkan username admin">
        </div>
        <button class="btn btnBlue" type="submit" style="width:100%;height:44px;">Continue</button>
      </form>
    </div>
  </div>
</div>
