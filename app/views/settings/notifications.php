<?php
/** @var string $csrf */
/** @var bool $enabled */
?>
<div class="page-wrap">
  <div class="card hero">
    <h1 style="margin:0;font-size:28px;line-height:1.2;">Notification Settings</h1>
    <div style="opacity:.9;margin-top:6px;">Kelola notifikasi keamanan akun.</div>
  </div>

  <div class="card" style="margin-top:14px;">
    <div style="font-weight:800;font-size:18px;">Notifikasi Login via Email</div>
    <div style="opacity:.85;margin-top:6px;font-size:14px;line-height:1.4;">
      Jika ada login dari device baru atau IP berbeda, sistem akan mengirim email pemberitahuan.
    </div>

    <div style="margin-top:14px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
      <div id="toggleLabel" style="font-weight:800;">
        <?= $enabled ? 'ON' : 'OFF' ?>
      </div>

      <label class="switch">
        <input id="toggleLoginEmail" type="checkbox" <?= $enabled ? 'checked' : '' ?>>
        <span class="slider"></span>
      </label>
    </div>

    <div id="msg" style="margin-top:10px;font-size:13px;opacity:.9;"></div>

    <div style="margin-top:14px;display:flex;gap:10px;">
      <a class="btn" href="/settings" style="flex:1;text-align:center;">Kembali</a>
    </div>
  </div>
</div>

<style>
  .page-wrap{max-width:520px;margin:0 auto;padding:18px;}
  .card{background:#fff;border-radius:22px;padding:18px;box-shadow:0 12px 24px rgba(0,0,0,.08);}
  .hero{background:rgba(255,255,255,.18);backdrop-filter:blur(10px);color:#fff;}
  .btn{display:inline-block;padding:12px 14px;border-radius:14px;border:1px solid rgba(0,0,0,.08);background:#fff;color:#111;text-decoration:none;font-weight:800}

  /* Toggle Switch */
  .switch{position:relative;display:inline-block;width:62px;height:34px;flex:0 0 auto;}
  .switch input{opacity:0;width:0;height:0;}
  .slider{
    position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;
    background:#d1d5db;transition:.2s;border-radius:999px;
  }
  .slider:before{
    position:absolute;content:"";height:26px;width:26px;left:4px;bottom:4px;
    background:white;transition:.2s;border-radius:999px;
    box-shadow:0 6px 14px rgba(0,0,0,.18);
  }
  input:checked + .slider{background:#4f46e5;}
  input:checked + .slider:before{transform:translateX(28px);}
</style>

<script>
(function(){
  const csrf = <?= json_encode($csrf) ?>;
  const toggle = document.getElementById('toggleLoginEmail');
  const label  = document.getElementById('toggleLabel');
  const msg    = document.getElementById('msg');

  function setMsg(text, ok=true){
    msg.textContent = text || '';
    msg.style.color = ok ? '#0f766e' : '#b91c1c';
  }

  toggle.addEventListener('change', async function(){
    const enabled = toggle.checked ? 1 : 0;

    // Optimistic UI
    label.textContent = enabled ? 'ON' : 'OFF';
    setMsg('Menyimpan...', true);

    const fd = new FormData();
    fd.append('csrf', csrf);
    fd.append('enabled', String(enabled));

    try{
      const res = await fetch('/settings/notifications/toggle-login-email', {
        method: 'POST',
        body: fd,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf
        }
      });

      const text = await res.text();
      let data = null;
      try { data = JSON.parse(text); } catch(e){}

      if(!res.ok){
        throw new Error(data?.message || text || ('HTTP '+res.status));
      }

      if(data && data.ok){
        setMsg('Berhasil disimpan ✅', true);
      }else{
        setMsg('Gagal menyimpan', false);
      }
    }catch(err){
      // rollback UI
      toggle.checked = !toggle.checked;
      const back = toggle.checked ? 1 : 0;
      label.textContent = back ? 'ON' : 'OFF';
      setMsg('Gagal: ' + (err.message || 'Unknown error'), false);
    }
  });
})();
</script>
