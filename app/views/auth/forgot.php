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
    <p class="authSub">Masukkan email atau nama pengguna Anda untuk memulai.</p>
  </div>

  <div class="authBody">
    <div class="authForm">
      <form method="POST" action="/forgot" autocomplete="on">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

        <div class="field">
          <input name="email" type="email" placeholder="Enter your email address" required autocomplete="email">
        </div>

        <button class="btn btnPrimary" type="submit">Submit</button>
      </form>

      <div class="authNote">
        Remember your password? <a href="/login">Back to login</a>
      </div>
    </div>
  </div>
</div>
