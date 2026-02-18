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
      <a class="authPill" href="/logout">Log out</a>
    </div>

    <div class="authKicker">SECURE CHECK</div>
    <div class="authTitle">Verify it's you</div>
    <p class="authSub">Enter the 6-digit code sent to your email.</p>
  </div>

  <div class="authBody">
    <div class="authForm">
      <form method="POST" action="/otp">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

        <div class="field">
          <input name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="123456" required>
        </div>

        <div class="rememberRow">
          <label class="rememberBox">
            <input type="checkbox" name="remember_device" value="1" <?= !empty($cookiesAllow) ? '' : 'disabled' ?>>
            <span class="rememberText">
              Remember this device
              <small class="rememberHint">
                <?= !empty($cookiesAllow) ? 'Skip OTP on this browser next time.' : 'Enable "Allow cookies" to use this feature.' ?>
              </small>
            </span>
          </label>
        </div>

        <button class="btn btnPrimary" type="submit">Verify</button>
      </form>

      <form method="POST" action="/otp/resend" style="margin-top:10px;">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="btn btnGhost" type="submit">Get a new code</button>
      </form>
    </div>
  </div>
</div>
