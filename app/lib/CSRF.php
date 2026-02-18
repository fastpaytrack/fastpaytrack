<?php
declare(strict_types=1);

namespace App\Lib;

final class CSRF
{
  private static function ensureSession(): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      // safe start session
      @session_start();
    }
  }

  public static function token(): string
  {
    self::ensureSession();

    if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
      $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }

    // Optional: set cookie untuk AJAX / double-submit fallback (tidak merusak yang existing)
    if (!headers_sent()) {
      @setcookie('XSRF-TOKEN', (string)$_SESSION['_csrf'], [
        'expires'  => time() + 3600,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => false,
        'samesite' => 'Lax',
      ]);
    }

    return (string)$_SESSION['_csrf'];
  }

  public static function check(): void
  {
    self::ensureSession();

    $t =
      (string)($_POST['_csrf'] ?? '') ?:
      (string)($_POST['csrf'] ?? '') ?:
      (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '') ?:
      (string)($_SERVER['HTTP_X_XSRF_TOKEN'] ?? '');

    // fallback dari cookie (kalau kamu pakai fetch tanpa kirim body csrf)
    if (!$t && !empty($_COOKIE['XSRF-TOKEN'])) {
      $t = (string)$_COOKIE['XSRF-TOKEN'];
    }

    $sess = (string)($_SESSION['_csrf'] ?? '');

    if (!$t || !$sess || !hash_equals($sess, $t)) {
      $isAjax =
        (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') ||
        (strpos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false);

      http_response_code(419);

      if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'CSRF token mismatch']);
        exit;
      }

      echo "CSRF token mismatch";
      exit;
    }
  }
}
