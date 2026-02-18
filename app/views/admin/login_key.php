<div style="max-width:520px;margin:30px auto;">
  <div class="panel">
    <div class="panelHead">Access Key</div>
    <div class="panelBody">
      <div style="font-weight:800;margin-bottom:10px;">Hi, <?= ae($name ?? 'Admin') ?></div>
      <form method="POST" action="/admin/login/key">
        <input type="hidden" name="_csrf" value="<?= ae($csrf) ?>">
        <div class="field">
          <label>Key Akses (16 Digit)</label>
          <input name="access_key" maxlength="16" placeholder="ABCDEFGHIJKLMNOP" style="text-transform:uppercase;">
        </div>
        <button class="btn btnBlue" type="submit" style="width:100%;height:44px;">Masuk</button>
      </form>
    </div>
  </div>
</div>
