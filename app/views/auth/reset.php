<?php use function App\Lib\e; ?>

<script>
(function(){ try{ document.body.classList.add('page-auth'); }catch(e){} })();
</script>

<div class="authShell">
  <div class="authTop">
    <div class="authTopbar">
      <a class="authBrand" href="/">
        <img class="authBrandImg" src="/asset/brand/fastpaytrack-logo.png" alt="FastPayTrack">
      </a>
      <a class="authPill" href="/login">Log in</a>
    </div>

    <div class="authKicker">NEW PASSWORD</div>
    <div class="authTitle">Buat Password Baru</div>
    <p class="authSub">Masukkan password baru dan konfirmasi untuk mengamankan akun.</p>
  </div>

  <div class="authBody">
    <div class="authForm">
      <form method="POST" action="/reset">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

        <div class="field">
          <div class="pwWrap">
            <input id="resetPassword1" name="password" type="password" placeholder="Minimal 8 karakter" required>
            <button class="pwToggle" type="button" aria-label="Show password" data-pw-toggle="resetPassword1">
              <svg class="pwIcon pwIconEye" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="2"/>
              </svg>
              <svg class="pwIcon pwIconEyeOff" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10.6 10.6A2.5 2.5 0 0 0 13.4 13.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M6.2 6.6C4.1 8.2 2.5 12 2.5 12S6 19 12 19c1.7 0 3.2-.4 4.5-1" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <path d="M9.2 5.2C10.1 5 11 5 12 5c6 0 9.5 7 9.5 7s-1.2 2.4-3.3 4.3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="field">
          <div class="pwWrap">
            <input id="resetPassword2" name="password2" type="password" placeholder="Ulangi password" required>
            <button class="pwToggle" type="button" aria-label="Show password" data-pw-toggle="resetPassword2">
              <svg class="pwIcon pwIconEye" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="2"/>
              </svg>
              <svg class="pwIcon pwIconEyeOff" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10.6 10.6A2.5 2.5 0 0 0 13.4 13.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M6.2 6.6C4.1 8.2 2.5 12 2.5 12S6 19 12 19c1.7 0 3.2-.4 4.5-1" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <path d="M9.2 5.2C10.1 5 11 5 12 5c6 0 9.5 7 9.5 7s-1.2 2.4-3.3 4.3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </div>

        <button class="btn btnPrimary" type="submit">Simpan Password</button>
      </form>

      <div class="authFooterNote">
        Back to <a href="/login">Log in</a>
      </div>
    </div>
  </div>
</div>
