</div>

<!-- Cookie Consent Modal (Global) -->
<div class="cookieOverlay" id="cookieOverlay" aria-hidden="true">
  <div class="cookieModal" role="dialog" aria-modal="true" aria-labelledby="cookieTitle">
    <div class="cookieHead">
      <h3 class="cookieTitle" id="cookieTitle">
        <span class="cookieBadge">🍪</span>
        Consent
      </h3>
    </div>
    <div class="cookieBody">
      <p>
        If you accept cookies, we’ll use them to improve and customize your experience and enable our partners to show you personalized ads when you visit other sites. <b>Allow all cookies</b> or <b>Deny</b>.
      </p>
    </div>
    <div class="cookieActions">
      <button class="cookieBtn cookieAllow" id="cookieAllowBtn" type="button">Accept</button>
      <button class="cookieBtn cookieDeny" id="cookieDenyBtn" type="button">Decline</button>
    </div>
  </div>
</div>

<script>
(function(){
  var CONSENT_NAME = 'fpt_cookie_consent';

  function getCookie(name){
    var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  }

  function setCookie(name, value, days){
    var d = new Date();
    d.setTime(d.getTime() + (days*24*60*60*1000));
    var expires = 'expires=' + d.toUTCString();
    var secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = name + '=' + encodeURIComponent(value) + '; ' + expires + '; path=/; SameSite=Lax' + secure;
  }

  var overlay = document.getElementById('cookieOverlay');
  var allowBtn = document.getElementById('cookieAllowBtn');
  var denyBtn  = document.getElementById('cookieDenyBtn');

  function open(){
    if(!overlay) return;
    overlay.classList.add('isOpen');
    overlay.setAttribute('aria-hidden', 'false');
  }

  function close(){
    if(!overlay) return;
    overlay.classList.remove('isOpen');
    overlay.setAttribute('aria-hidden', 'true');
  }

  var v = getCookie(CONSENT_NAME);
  if(!v){
    open();
  }

  if(allowBtn){
    allowBtn.addEventListener('click', function(){
      setCookie(CONSENT_NAME, 'allow', 365);
      close();
    });
  }

  if(denyBtn){
    denyBtn.addEventListener('click', function(){
      setCookie(CONSENT_NAME, 'deny', 365);
      close();
    });
  }

  if(overlay){
    overlay.addEventListener('click', function(e){
      if(e.target === overlay){
        // user wajib pilih
      }
    });
  }
})();
</script>

<script>
(function(){
  function initPasswordToggles(){
    var btns = document.querySelectorAll('[data-pw-toggle]');
    if(!btns || !btns.length) return;

    btns.forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = btn.getAttribute('data-pw-toggle');
        var input = id ? document.getElementById(id) : null;
        if(!input) return;

        var isShown = input.getAttribute('type') === 'text';
        input.setAttribute('type', isShown ? 'password' : 'text');

        btn.classList.toggle('isShown', !isShown);
        btn.setAttribute('aria-label', isShown ? 'Show password' : 'Hide password');

        try { input.focus(); } catch(e) {}
      });
    });
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initPasswordToggles);
  } else {
    initPasswordToggles();
  }
})();
</script>

</body>
</html>
