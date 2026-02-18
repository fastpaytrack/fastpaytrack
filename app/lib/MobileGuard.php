<?php
declare(strict_types=1);

namespace App\Lib;

final class MobileGuard
{
  /**
   * Jalankan guard untuk semua halaman APP,
   * kecuali path yang ada di allowlist (mis: homepage "/").
   */
  public static function enforce(string $path, string $method = 'GET', array $allowlist = ['/']): void
  {
    // Allowlist (homepage dkk)
    if (in_array($path, $allowlist, true)) return;

    // Jangan ganggu webhook / endpoint non-HTML (amanin integrasi)
    if (self::isWebhookPath($path)) return;

    // Kalau request bukan HTML (mis: download file), jangan diblokir dari sini
    if (!self::expectsHtml()) return;

    // Block DESKTOP dari server-side (biar gak bisa akses dashboard pakai desktop mode)
    if (!self::isMobileDevice()) {
      self::renderBlockedPage();
      exit;
    }

    // Mobile device lolos.
    // Portrait enforcement dilakukan di client-side via overlay di head.php
  }

  private static function isWebhookPath(string $path): bool
  {
    return str_starts_with($path, '/webhook/');
  }

  private static function expectsHtml(): bool
  {
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    // kalau Accept kosong, anggap HTML juga (umumnya browser)
    if ($accept === '') return true;
    return str_contains($accept, 'text/html') || str_contains($accept, 'application/xhtml+xml');
  }

  /**
   * Deteksi mobile pakai kombinasi:
   * - Client Hints: Sec-CH-UA-Mobile
   * - User-Agent pattern (Android/iPhone/iPad/iPod/Mobile)
   */
  private static function isMobileDevice(): bool
  {
    $ch = (string)($_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '');
    if ($ch === '?1') return true;

    $ua = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));

    // iPadOS sering ngaku Macintosh tapi masih ada "mobile" di UA
    $mobileKeywords = [
      'mobi', 'android', 'iphone', 'ipad', 'ipod',
      'opera mini', 'iemobile', 'blackberry', 'webos'
    ];

    foreach ($mobileKeywords as $k) {
      if (str_contains($ua, $k)) return true;
    }
    return false;
  }

  private static function renderBlockedPage(): void
  {
    http_response_code(403);

    // Halaman sederhana (tanpa layout app) supaya jelas dan ringan
    echo '<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FastPayTrack — Mobile Only</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --bg1:#6d5efc; --bg2:#35c6ff; --text:#0f172a; --muted:#64748b;
    --card:#fff; --border:#e2e8f0; --primary:#4f46e5; --primaryHover:#4338ca;
    --shadow:0 20px 45px rgba(2,6,23,.18);
  }
  *{box-sizing:border-box}
  body{
    margin:0;
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
    min-height:100vh;
    background: radial-gradient(1200px 600px at 20% 10%, rgba(255,255,255,.28), transparent 60%),
                linear-gradient(135deg,var(--bg1),var(--bg2));
    padding:24px 14px;
    display:flex; align-items:center; justify-content:center;
    color:var(--text);
  }
  .box{
    width:min(520px,100%);
    background:rgba(255,255,255,.92);
    border:1px solid rgba(226,232,240,.95);
    border-radius:22px;
    box-shadow:var(--shadow);
    overflow:hidden;
  }
  .head{
    padding:16px 16px 12px;
    border-bottom:1px solid rgba(226,232,240,.9);
    background:linear-gradient(180deg,#fff,#f8fafc);
  }
  .title{ margin:0; font-weight:800; font-size:18px; }
  .sub{ margin:8px 0 0; color:var(--muted); font-weight:600; font-size:13px; line-height:1.45; }
  .body{ padding:14px 16px 16px; }
  .btn{
    height:46px; width:100%;
    border-radius:14px; border:none;
    cursor:pointer; font-weight:800; font-size:14.5px;
    background:var(--primary); color:#fff;
    box-shadow:0 12px 24px rgba(79,70,229,.25);
  }
  .btn:hover{ background:var(--primaryHover); }
  .link{
    display:block; margin-top:10px;
    text-align:center; font-weight:800; text-decoration:none;
    color:var(--primary);
  }
</style>
</head>
<body>
  <div class="box">
    <div class="head">
      <p class="title">Akses dibatasi</p>
      <p class="sub">
        Dashboard FastPayTrack hanya bisa diakses melalui <b>device mobile</b> dan mode <b>portrait</b>.
        Silakan buka menggunakan HP (bukan desktop/desktop mode).
      </p>
    </div>
    <div class="body">
      <button class="btn" onclick="location.href=\'/\';">Kembali ke Home</button>
      <a class="link" href="/" aria-label="Home">fastpaytrack.com</a>
    </div>
  </div>
</body>
</html>';
  }
}
