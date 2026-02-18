<?php
use App\Lib\CSRF;

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
$metaTitle = $metaTitle ?? 'Admin Panel - FastPayTrack';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= e($metaTitle) ?></title>

  <!-- Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#efefef;
      --panel:#ffffff;
      --text:#111827;
      --muted:#6b7280;
      --border:#e5e7eb;
      --blue:#2f6bdc;
      --blue2:#2b5fca;
      --danger:#b91c1c;
      --success:#16a34a;
      --shadow: 0 12px 28px rgba(0,0,0,.08);
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: Poppins, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    a{ color:inherit; text-decoration:none; }
    .adminWrap{
      display:grid;
      grid-template-columns: 280px 1fr;
      min-height:100vh;
    }
    .side{
      background:#fff;
      border-right:1px solid var(--border);
      padding:18px 14px;
      position:sticky;
      top:0;
      height:100vh;
    }
    .profile{
      display:flex; gap:12px; align-items:center;
      padding:10px 10px 14px;
      border-bottom:1px solid var(--border);
      margin-bottom:12px;
    }
    .avatar{
      width:44px;height:44px;border-radius:50%;
      background:#111827;
      opacity:.10;
    }
    .pname{ font-weight:700; font-size:13px; line-height:1.1; }
    .pmail{ font-size:11px; color:var(--muted); margin-top:3px; }
    .nav{
      display:grid; gap:8px; padding:10px 4px;
    }
    .nav a{
      display:flex; gap:10px; align-items:center;
      padding:12px 12px;
      border-radius:10px;
      color:#111827;
      font-weight:600;
      font-size:13px;
    }
    .nav a.active{
      background:var(--blue);
      color:#fff;
      box-shadow: 0 10px 22px rgba(47,107,220,.22);
    }
    .nav a:hover{ background: rgba(17,24,39,.04); }
    .logout{
      position:absolute;
      left:14px; right:14px;
      bottom:18px;
      background:#a52a2a;
      color:#fff;
      border-radius:10px;
      padding:12px 14px;
      font-weight:700;
      text-align:center;
    }

    .main{
      padding:18px 18px 28px;
      overflow:auto;
    }

    .topbar{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      padding:10px 12px;
      background:#fff;
      border:1px solid var(--border);
      border-radius:12px;
      box-shadow: var(--shadow);
    }
    .brand{
      display:flex; align-items:center; gap:12px;
      font-weight:800;
      letter-spacing:.2px;
    }
    .brand small{
      display:block;
      font-weight:600;
      color:var(--muted);
      font-size:11px;
      margin-top:2px;
    }
    .topIcons{ display:flex; gap:10px; align-items:center; }
    .iconBtn{
      width:38px;height:38px;border-radius:10px;
      border:1px solid var(--border);
      background:#fff;
      display:grid; place-items:center;
      cursor:pointer;
    }

    .gridCards{
      margin-top:14px;
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap:12px;
    }
    .card{
      background:#fff;
      border:1px solid var(--border);
      border-radius:12px;
      padding:12px 12px;
      box-shadow: var(--shadow);
      min-height:90px;
    }
    .card.blue{ background: var(--blue); color:#fff; border-color: transparent; }
    .card.black{ background: #0b0f1a; color:#fff; border-color: transparent; }
    .ctitle{ font-weight:700; font-size:13px; }
    .cvalue{ margin-top:8px; font-weight:800; font-size:18px; }
    .csub{ margin-top:3px; color: rgba(255,255,255,.85); font-size:11px; font-weight:600; }
    .card:not(.blue):not(.black) .csub{ color: var(--muted); }

    .gridMid{
      margin-top:12px;
      display:grid;
      grid-template-columns: 380px 1fr;
      gap:12px;
    }

    .panel{
      background:#fff;
      border:1px solid var(--border);
      border-radius:12px;
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .panelHead{
      padding:12px 12px;
      border-bottom:1px solid var(--border);
      font-weight:800;
      font-size:13px;
      display:flex;
      align-items:center;
      justify-content:space-between;
    }
    .panelBody{ padding:12px; }

    .notifItem{
      padding:10px 0;
      border-bottom:1px solid rgba(229,231,235,.8);
    }
    .notifItem:last-child{ border-bottom:none; }
    .notifTitle{ font-weight:700; font-size:12.5px; }
    .notifSub{ color:var(--muted); font-size:11px; margin-top:2px; font-weight:500; }

    table{
      width:100%;
      border-collapse:separate;
      border-spacing:0;
      font-size:11px;
    }
    th,td{
      padding:10px 8px;
      border-bottom:1px solid rgba(229,231,235,.8);
      text-align:left;
      vertical-align:middle;
      white-space:nowrap;
    }
    th{ color:#111827; font-weight:800; font-size:11px; }
    .badge{
      display:inline-flex; align-items:center; justify-content:center;
      height:20px;
      padding:0 10px;
      border-radius:999px;
      font-weight:800;
      font-size:10px;
      border:1px solid var(--border);
      background:#f9fafb;
    }
    .bPaid{ color:#15803d; background: rgba(22,163,74,.10); border-color: rgba(22,163,74,.22); }
    .bPending{ color:#b45309; background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.22); }
    .bDelivered{ color:#1d4ed8; background: rgba(59,130,246,.12); border-color: rgba(59,130,246,.22); }
    .bExpired{ color:#991b1b; background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.20); }

    .btnSmall{
      height:24px;
      padding:0 10px;
      border-radius:999px;
      border:none;
      font-weight:800;
      font-size:10px;
      cursor:pointer;
      background: var(--blue);
      color:#fff;
    }
    .btnSmall:hover{ background: var(--blue2); }
    .btnSmall.gray{ background:#e5e7eb; color:#111827; }

    .panelBottom{
      margin-top:12px;
    }

    @media (max-width: 1100px){
      .gridCards{ grid-template-columns:1fr; }
      .gridMid{ grid-template-columns:1fr; }
      .adminWrap{ grid-template-columns: 260px 1fr; }
    }
    @media (max-width: 860px){
      .adminWrap{ grid-template-columns:1fr; }
      .side{ position:relative; height:auto; }
      .logout{ position:relative; left:auto; right:auto; bottom:auto; margin-top:14px; display:block; }
    }
  </style>
</head>

<body>
