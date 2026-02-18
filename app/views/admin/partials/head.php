<?php
use function App\Lib\e;

$metaTitle = $metaTitle ?? 'Admin • FastPayTrack';
$page = $page ?? 'dashboard';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= e($metaTitle) ?></title>

  <!-- Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#0b1220;
      --panel:#0f172a;
      --card:#111c33;
      --card2:#0f1a30;
      --border: rgba(255,255,255,.08);
      --text:#eaf0ff;
      --muted: rgba(234,240,255,.70);
      --accent:#6d5efc;
      --accent2:#35c6ff;
      --success:#22c55e;
      --warn:#f59e0b;
      --danger:#ef4444;
      --shadow: 0 28px 80px rgba(0,0,0,.55);
    }

    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family:'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
      background:
        radial-gradient(900px 520px at 12% 12%, rgba(109,94,252,.20), transparent 60%),
        radial-gradient(900px 520px at 88% 18%, rgba(53,198,255,.16), transparent 60%),
        radial-gradient(900px 520px at 70% 90%, rgba(147,51,234,.10), transparent 55%),
        var(--bg);
      color: var(--text);
      min-height:100vh;
    }

    a{ color:inherit; text-decoration:none; }

    .aWrap{
      display:grid;
      grid-template-columns: 280px 1fr;
      min-height:100vh;
    }

    /* Sidebar */
    .aSide{
      padding:22px 18px;
      border-right: 1px solid var(--border);
      background: rgba(15,23,42,.55);
      backdrop-filter: blur(12px);
    }
    .aBrand{
      display:flex; align-items:center; gap:12px;
      padding:10px 10px;
      border-radius:16px;
      background: rgba(255,255,255,.04);
      border: 1px solid var(--border);
    }
    .aLogo{
      width:42px;height:42px;border-radius:14px;
      display:grid;place-items:center;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      font-weight:700;
      box-shadow: 0 18px 40px rgba(0,0,0,.35);
    }
    .aBrandText .t1{ font-weight:700; font-size:14px; letter-spacing:.2px; }
    .aBrandText .t2{ font-size:12px; color:var(--muted); margin-top:2px; }

    .aNav{
      margin-top:16px;
      display:grid;
      gap:10px;
    }
    .aNav a{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:12px 12px;
      border-radius:16px;
      background: rgba(255,255,255,.03);
      border: 1px solid var(--border);
      transition: transform .06s ease, background .15s ease;
      font-weight:600;
      font-size:13px;
      color: rgba(234,240,255,.90);
    }
    .aNav a:hover{ background: rgba(255,255,255,.06); }
    .aNav a:active{ transform: translateY(1px); }
    .aNav a.isActive{
      background: linear-gradient(135deg, rgba(109,94,252,.22), rgba(53,198,255,.12));
      border-color: rgba(109,94,252,.28);
      box-shadow: 0 22px 55px rgba(0,0,0,.35);
    }

    .aMain{
      padding:22px 22px 44px;
    }

    .aTop{
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      margin-bottom:16px;
    }
    .aTopRight{ display:flex; gap:10px; align-items:center; }

    .aBtn{
      height:40px;
      padding:0 14px;
      border-radius:14px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,.04);
      color: rgba(234,240,255,.95);
      font-weight:700;
      font-size:12.8px;
      cursor:pointer;
      display:inline-flex; align-items:center; justify-content:center;
      transition: background .15s ease, transform .06s ease, box-shadow .15s ease;
      font-family: inherit;
    }
    .aBtn:hover{ background: rgba(255,255,255,.07); box-shadow: 0 18px 44px rgba(0,0,0,.35); }
    .aBtn:active{ transform: translateY(1px); }

    .aBtnPrimary{
      background: linear-gradient(135deg, rgba(109,94,252,.95), rgba(53,198,255,.55));
      border-color: rgba(255,255,255,.10);
    }
    .aBtnPrimary:hover{ filter: brightness(1.02); }
    .aBtnGhost{ background: rgba(255,255,255,.04); }
    .aBtnDanger{
      background: rgba(239,68,68,.12);
      border-color: rgba(239,68,68,.22);
      color: #ffd7d7;
    }

    .aPageHead{
      display:flex;
      align-items:flex-end;
      justify-content:space-between;
      gap:14px;
      margin: 6px 0 14px;
      flex-wrap:wrap;
    }
    .aTitle{
      font-size:22px;
      font-weight:700;
      letter-spacing:.2px;
    }
    .aActions{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .aSearch{ min-width:260px; }

    .aCard{
      background: rgba(17,28,51,.60);
      border: 1px solid var(--border);
      border-radius:18px;
      padding:14px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
    }
    .aCardTitle{
      font-weight:700;
      font-size:14px;
      margin-bottom:10px;
      color: rgba(234,240,255,.95);
    }

    .aMuted{ color: var(--muted); font-size:12.5px; font-weight:500; line-height:1.55; }
    .aStrong{ font-weight:700; }
    .aMono{ font-family: ui-monospace, Menlo, Consolas, monospace; font-weight:700; }

    .aForm{ display:grid; gap:12px; }
    .aField label{ display:block; font-size:12px; font-weight:600; color: rgba(234,240,255,.75); margin-bottom:6px; }

    .aInput{
      width:100%;
      border-radius:14px;
      border: 1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.04);
      padding:11px 12px;
      color: rgba(234,240,255,.95);
      outline:none;
      font-family:inherit;
      font-weight:600;
      font-size:13px;
      transition: box-shadow .15s ease, border-color .15s ease;
    }
    .aInput:focus{
      border-color: rgba(109,94,252,.45);
      box-shadow: 0 0 0 4px rgba(109,94,252,.16);
    }
    textarea.aInput{ resize:vertical; }

    .aGrid2{ display:grid; grid-template-columns: 1.2fr .8fr; gap:14px; }
    @media (max-width: 980px){ .aWrap{ grid-template-columns: 1fr; } .aSide{ border-right:none; border-bottom:1px solid var(--border);} .aGrid2{ grid-template-columns:1fr; } .aSearch{ min-width: 0; width:100%; } }

    .aTableWrap{ overflow:auto; border-radius:14px; }
    .aTable{
      width:100%;
      border-collapse: collapse;
      min-width: 760px;
    }
    .aTable th, .aTable td{
      padding:12px 12px;
      border-bottom: 1px solid rgba(255,255,255,.06);
      vertical-align: top;
      font-size:12.8px;
      font-weight:600;
    }
    .aTable th{
      color: rgba(234,240,255,.70);
      font-weight:700;
      background: rgba(255,255,255,.03);
      position: sticky;
      top:0;
      z-index: 1;
    }
    .aEmpty{ color: var(--muted); text-align:center; padding:16px; }

    .aRowFlex{ display:flex; gap:12px; align-items:center; }
    .aThumb{
      width:44px;height:44px;border-radius:14px;
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.10);
      background-size:cover;background-position:center;
      flex:0 0 auto;
    }
    .aThumbLg{
      width:64px;height:64px;border-radius:18px;
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.10);
      background-size:cover;background-position:center;
      flex:0 0 auto;
    }

    .aPill{
      display:inline-flex; align-items:center; justify-content:center;
      height:28px; padding:0 10px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.05);
      font-weight:800;
      font-size:11.5px;
      letter-spacing:.3px;
    }
    .aPill.isOn{ background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.22); color: #c9ffe1; }
    .aPill.isOff{ background: rgba(148,163,184,.10); border-color: rgba(148,163,184,.18); color: rgba(234,240,255,.70); }
    .aPill.isWarn{ background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.22); color: #ffe7c2; }

    .aHr{ border:none; border-top:1px solid rgba(255,255,255,.08); margin:12px 0; }

    .aMiniList{ display:grid; gap:10px; margin-top:10px; }
    .aMiniRow{
      display:flex; align-items:flex-start; justify-content:space-between; gap:12px;
      padding:10px 10px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,.08);
      background: rgba(255,255,255,.03);
    }

    .aSectionTitle{
      font-weight:700;
      font-size:12.5px;
      color: rgba(234,240,255,.85);
      margin-top:8px;
    }

    .aTabs{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
    .aTab{
      height:38px; padding:0 14px; border-radius:999px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.04);
      display:inline-flex; align-items:center; justify-content:center;
      font-weight:800; font-size:12.5px;
      color: rgba(234,240,255,.90);
    }
    .aTab.isActive{
      background: linear-gradient(135deg, rgba(109,94,252,.75), rgba(53,198,255,.32));
      border-color: rgba(255,255,255,.12);
    }

    .aDenomGrid{ display:grid; gap:10px; }
    .aDenomRow{ display:grid; grid-template-columns: 1fr 220px; gap:10px; }
    @media (max-width: 520px){ .aDenomRow{ grid-template-columns:1fr; } }

  </style>
</head>

<body>
<div class="aWrap">
  <aside class="aSide">
    <div class="aBrand">
      <div class="aLogo">F</div>
      <div class="aBrandText">
        <div class="t1">FastPayTrack</div>
        <div class="t2">Admin Panel</div>
      </div>
    </div>

    <nav class="aNav">
      <a class="<?= ($page==='dashboard'?'isActive':'') ?>" href="/admin/dashboard">Dashboard</a>
      <a class="<?= ($page==='orders'?'isActive':'') ?>" href="/admin/orders">Orders</a>
      <a class="<?= ($page==='products'?'isActive':'') ?>" href="/admin/products">Products</a>
      <a class="<?= ($page==='users'?'isActive':'') ?>" href="/admin/users">Manage Users</a>
      <a class="<?= ($page==='transactions'?'isActive':'') ?>" href="/admin/transactions">Transactions</a>
      <a class="<?= ($page==='store'?'isActive':'') ?>" href="/admin/store">Manage Store</a>
      <a class="<?= ($page==='settings'?'isActive':'') ?>" href="/admin/settings">Settings</a>
      <a href="/admin/logout">Logout</a>
    </nav>
  </aside>

  <main class="aMain">
    <div class="aTop">
      <div class="aMuted">Welcome back 👋</div>
      <div class="aTopRight">
        <a class="aBtn aBtnGhost" href="/">Open Website</a>
        <a class="aBtn aBtnGhost" href="/admin/logout">Logout</a>
      </div>
    </div>
