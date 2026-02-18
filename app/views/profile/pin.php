<?php use function App\Lib\e; ?>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brand"><div class="logo">A</div>FastPayTrack</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="pill" href="/profile">Profil</a>
        <a class="pill" href="/dashboard">Dashboard</a>
        <a class="pill" href="/logout">Logout</a>
      </div>
    </div>

    <div class="heroTitle"><?= $hasPin ? 'Ubah PIN' : 'Buat PIN' ?></div>
    <p class="heroSub">PIN 6 digit diperlukan untuk transfer saldo.</p>
  </div>

  <div class="card">
    <div class="panel">
      <div class="panelHead">
        <p class="panelTitle">PIN Transfer</p>
        <p class="panelSub">Gunakan PIN yang mudah diingat tapi aman</p>
      </div>

      <div class="panelBody">
        <form method="POST" action="/pin" style="max-width:420px;">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

          <div class="field">
            <label>PIN Baru (6 digit)</label>
            <input name="pin" inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="contoh: 123456" required>
          </div>

          <div class="field">
            <label>Konfirmasi PIN</label>
            <input name="pin2" inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="ulang: 123456" required>
          </div>

          <button class="btn btnPrimary" type="submit">Simpan PIN</button>

          <div class="note" style="margin-top:10px;">
            PIN disimpan aman (hash) dan tidak ditampilkan kembali.
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
