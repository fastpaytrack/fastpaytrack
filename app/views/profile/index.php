<?php
use function App\Lib\e;

$created = (string) ($user['created_at'] ?? '');
$name = (string) ($user['name'] ?? '');
$email = (string) ($user['email'] ?? '');
?>

<style>
  /* tombol gear posisi sama seperti tombol profil di dashboard */
  .brandRow {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
    padding-right: 2px;
  }

  .settingsTopBtn {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .28);
    background: rgba(255, 255, 255, .16);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    margin-left: auto;
    transform: translateX(6px);
  }

  .settingsTopBtn:hover {
    background: rgba(255, 255, 255, .22);
  }

  /* read-only hint */
  .readonlyHint {
    margin-top: 10px;
    background: rgba(255, 255, 255, .14);
    border: 1px solid rgba(255, 255, 255, .22);
    color: #fff;
    border-radius: 16px;
    padding: 10px 12px;
    font-weight: 600;
    font-size: 12.5px;
    line-height: 1.35;
  }

  /* make disabled inputs look nicer */
  input[disabled] {
    background: #f8fafc !important;
    color: #0f172a !important;
    opacity: 1 !important;
    cursor: not-allowed;
  }
</style>

<div class="shell">
  <div class="hero">
    <div class="topbar">
      <div class="brandRow">
        <div class="brand">
          <div class="logo">A</div>FastPayTrack
        </div>

        <!-- tombol gear settings -->
        <a class="settingsTopBtn" href="/settings" title="Pengaturan" aria-label="Pengaturan">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
            <path
              d="M19.4 15a7.7 7.7 0 0 0 .1-1l2-1.2-2-3.5-2.3.5a7.6 7.6 0 0 0-1.7-1l-.3-2.3H9l-.3 2.3a7.6 7.6 0 0 0-1.7 1l-2.3-.5-2 3.5 2 1.2a7.7 7.7 0 0 0 .1 1 7.7 7.7 0 0 0-.1 1l-2 1.2 2 3.5 2.3-.5a7.6 7.6 0 0 0 1.7 1l.3 2.3h6.2l.3-2.3a7.6 7.6 0 0 0 1.7-1l2.3.5 2-3.5-2-1.2a7.7 7.7 0 0 0-.1-1Z" />
          </svg>
        </a>
      </div>
    </div>

    <div class="heroTitle">Profil Akun</div>
    <p class="heroSub">Informasi akun kamu (hanya lihat).</p>

    <div class="readonlyHint">
      Untuk mengubah profil atau keamanan akun, buka menu <b>Pengaturan</b> (ikon ⚙️).
    </div>
  </div>

  <div class="card">
    <div class="panel">
      <div class="panelHead">
        <p class="panelTitle">Informasi Akun</p>
        <p class="panelSub">Semua data di halaman ini terkunci</p>
      </div>

      <div class="panelBody" style="max-width:420px;">
        <div class="field">
          <label>Nama</label>
          <input type="text" value="<?= e($name) ?>" disabled>
        </div>

        <div class="field" style="margin-top:12px;">
          <label>Email</label>
          <input type="email" value="<?= e($email) ?>" disabled>
        </div>

        <div class="field" style="margin-top:12px;">
          <label>Tanggal Daftar</label>
          <input type="text" value="<?= e($created) ?>" disabled>
        </div>

        <!-- tombol simpan dihapus -->
      </div>
    </div>
  </div>
</div>