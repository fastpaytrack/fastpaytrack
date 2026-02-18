<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\Auth;
use App\Lib\MailerSMTP;
use function App\Lib\redirect;
use function App\Lib\flash;
use function App\Lib\random_digits;
use function App\Lib\app_url;

final class AuthController
{
  private const COOKIE_CONSENT_NAME = 'fpt_cookie_consent'; // allow|deny
  private const TRUST_COOKIE_NAME   = 'fpt_trusted_device'; // selector.token (base64url)
  private const TRUST_DAYS          = 30;

  private function view(string $file, array $data = []): void
  {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  private static function sendMail(string $to, string $subject, string $html): bool
  {
    try {
      if (method_exists(MailerSMTP::class, 'send')) {
        try {
          /** @phpstan-ignore-next-line */
          MailerSMTP::send($to, $subject, $html);
          return true;
        } catch (\Throwable $e) {
          // fallback to instance
        }
      }

      $m = new MailerSMTP();
      if (method_exists($m, 'send')) {
        /** @phpstan-ignore-next-line */
        $m->send($to, $subject, $html);
        return true;
      }
    } catch (\Throwable $e) {
      error_log('MAIL ERROR: ' . $e->getMessage());
    }
    return false;
  }

  // =========================
  // LOGIN / REGISTER
  // =========================
  public function showLogin(): void
  {
    $this->view('auth/login.php', ['csrf' => CSRF::token()]);
  }

  public function login(): void
  {
    CSRF::check();

    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if (!$email || !$pass) {
      flash('error', 'Email dan password wajib diisi.');
      redirect('/login');
    }

    $pdo = DB::pdo();
    $st  = $pdo->prepare("SELECT id, password_hash, is_active FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();

    if (!$u || !(int)$u['is_active'] || !password_verify($pass, (string)$u['password_hash'])) {
      flash('error', 'Email atau password salah.');
      redirect('/login');
    }

    $uid = (int)$u['id'];

    // ==========================================================
    // TRUSTED DEVICE CHECK (skip OTP kalau valid + cookies allow)
    // ==========================================================
    if ($this->cookieConsentAllow() && $this->hasTrustedCookie()) {
      try {
        if ($this->isTrustedDeviceValid($uid)) {
          // login tanpa OTP
          Auth::login($uid, false);
          Auth::markOtpOk();
          flash('success', 'Login berhasil (trusted device).');
          redirect('/dashboard');
        }
      } catch (\Throwable $e) {
        error_log('TRUST DEVICE CHECK ERROR: ' . $e->getMessage());
        // fallback: lanjut OTP normal
      }
    }

    // login (otp required)
    Auth::login($uid, true);

    // create OTP & send email
    $ok = $this->issueOtp($uid, $email);
    if (!$ok) {
      redirect('/otp');
    }

    redirect('/otp');
  }

  public function showRegister(): void
  {
    $this->view('auth/register.php', ['csrf' => CSRF::token()]);
  }

  public function register(): void
  {
    CSRF::check();

    $name  = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
      flash('error', 'Isi data dengan benar (password minimal 8 karakter).');
      redirect('/register');
    }

    $pdo = DB::pdo();
    $st  = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    if ($st->fetch()) {
      flash('error', 'Email sudah terdaftar.');
      redirect('/register');
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $ins  = $pdo->prepare("INSERT INTO users (name, email, password_hash, is_active, created_at) VALUES (?, ?, ?, 1, UTC_TIMESTAMP())");
    $ins->execute([$name, $email, $hash]);

    flash('success', 'Registrasi berhasil. Silakan login.');
    redirect('/login');
  }

  // =========================
  // OTP
  // =========================
  public function showOtp(): void
  {
    if (!Auth::check()) redirect('/login');
    $this->view('auth/otp.php', [
      'csrf' => CSRF::token(),
      'cookiesAllow' => $this->cookieConsentAllow(),
    ]);
  }

  public function verifyOtp(): void
  {
    CSRF::check();
    if (!Auth::check()) redirect('/login');

    $code = trim((string)($_POST['otp'] ?? ''));
    if (!$code || strlen($code) < 4) {
      flash('error', 'OTP wajib diisi.');
      redirect('/otp');
    }

    $pdo = DB::pdo();
    $uid = (int)Auth::id();

    $st = $pdo->prepare(
      "SELECT id, code_hash, used_at, expires_at
       FROM otp_codes
       WHERE user_id=? AND used_at IS NULL
       ORDER BY id DESC
       LIMIT 1"
    );
    $st->execute([$uid]);
    $row = $st->fetch();

    if (!$row) {
      flash('error', 'OTP tidak ditemukan. Silakan kirim ulang.');
      redirect('/otp');
    }

    $nowUtc = gmdate('Y-m-d H:i:s');
    if (!empty($row['expires_at']) && (string)$row['expires_at'] < $nowUtc) {
      flash('error', 'OTP sudah kedaluwarsa. Silakan kirim ulang.');
      redirect('/otp');
    }

    if (!password_verify($code, (string)$row['code_hash'])) {
      flash('error', 'OTP salah.');
      redirect('/otp');
    }

    $upd = $pdo->prepare("UPDATE otp_codes SET used_at=UTC_TIMESTAMP() WHERE id=?");
    $upd->execute([(int)$row['id']]);

    Auth::markOtpOk();

    // ==========================================================
    // REMEMBER THIS DEVICE (hanya jika dicentang + cookies allow)
    // ==========================================================
    $remember = (string)($_POST['remember_device'] ?? '') === '1';

    if ($remember) {
      if (!$this->cookieConsentAllow()) {
        flash('error', 'Agar fitur "Remember this device" berfungsi, kamu harus memilih "Allow cookies".');
        // tetap lanjut masuk dashboard (OTP sudah valid)
      } else {
        try {
          $this->issueTrustedDevice($uid);
        } catch (\Throwable $e) {
          error_log('TRUST DEVICE ISSUE ERROR: ' . $e->getMessage());
          // jangan gagalkan login user, hanya fitur remember-nya saja yang gagal
        }
      }
    }

    // ==========================================================
    // TRACK LOGIN DEVICE/IP + EMAIL NOTIF (hanya jika ON)
    // ==========================================================
    $sessionHash = hash('sha256', session_id());
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ip = self::clientIp();
    $label = self::deviceLabel($ua);

    // Ambil user + preferensi notifikasi
    $uSt = $pdo->prepare("SELECT name, email, notify_login_email FROM users WHERE id=? LIMIT 1");
    $uSt->execute([$uid]);
    $userRow = $uSt->fetch() ?: ['name' => 'User', 'email' => '', 'notify_login_email' => 1];

    $userName  = (string)($userRow['name'] ?? 'User');
    $userEmail = (string)($userRow['email'] ?? '');
    $notifyEnabled = (int)($userRow['notify_login_email'] ?? 1) === 1;

    // Cek apakah kombinasi (user_id + ip + user_agent) sudah pernah ada & masih aktif
    $chk = $pdo->prepare(
      "SELECT id
       FROM user_login_devices
       WHERE user_id=? AND ip_address=? AND user_agent=? AND revoked_at IS NULL
       ORDER BY id DESC
       LIMIT 1"
    );
    $chk->execute([$uid, $ip, $ua]);
    $existing = $chk->fetch();

    $isNewLoginContext = false;

    if ($existing) {
      $updDev = $pdo->prepare(
        "UPDATE user_login_devices
         SET last_seen_at=UTC_TIMESTAMP(), session_id_hash=?
         WHERE id=?"
      );
      $updDev->execute([$sessionHash, (int)$existing['id']]);
    } else {
      $isNewLoginContext = true;

      $ins = $pdo->prepare(
        "INSERT INTO user_login_devices (user_id, ip_address, user_agent, device_label, session_id_hash, created_at, last_seen_at)
         VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())"
      );
      $ins->execute([$uid, $ip, $ua, $label, $sessionHash]);
    }

    // Jika device/ip baru DAN notifikasi ON → kirim email
    if ($isNewLoginContext && $userEmail && $notifyEnabled) {
      $subject = '🔐 Login baru terdeteksi di akun FastPayTrack';

      $when = gmdate('Y-m-d H:i:s') . ' UTC';
      $manageUrl = app_url('/settings/security');
      $revokeUrl = app_url('/settings/security/revoke-others');

      $body = "
        Halo <b>" . htmlspecialchars($userName) . "</b>,<br><br>

        Kami mendeteksi <b>login baru</b> ke akun FastPayTrack kamu.<br><br>

        <div style='padding:12px;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa'>
          <div><b>Waktu:</b> " . htmlspecialchars($when) . "</div>
          <div><b>IP Address:</b> " . htmlspecialchars($ip) . "</div>
          <div><b>Device:</b> " . htmlspecialchars($label) . "</div>
          <div><b>User-Agent:</b> " . htmlspecialchars($ua) . "</div>
        </div>
        <br>

        Jika ini kamu, abaikan email ini.<br>
        Jika bukan kamu, segera amankan akun kamu:<br><br>

        <a href='" . htmlspecialchars($manageUrl) . "' style='display:inline-block;padding:10px 14px;border-radius:10px;background:#4f46e5;color:#fff;text-decoration:none'>
          Manage Devices
        </a>
        &nbsp;
        <a href='" . htmlspecialchars($revokeUrl) . "' style='display:inline-block;padding:10px 14px;border-radius:10px;background:#111827;color:#fff;text-decoration:none'>
          Logout Device Lain
        </a>
        <br><br>

        <span style='color:#6b7280;font-size:12px'>Email ini dikirim otomatis untuk keamanan akun.</span>
      ";

      self::sendMail($userEmail, $subject, $body);
    }

    flash('success', 'OTP valid. Selamat datang!');
    redirect('/dashboard');
  }

  public function resendOtp(): void
  {
    CSRF::check();
    if (!Auth::check()) redirect('/login');

    $pdo = DB::pdo();
    $uid = (int)Auth::id();

    $st = $pdo->prepare("SELECT email FROM users WHERE id=? LIMIT 1");
    $st->execute([$uid]);
    $u = $st->fetch();

    if (!$u) {
      flash('error', 'User tidak ditemukan.');
      redirect('/login');
    }

    $ok = $this->issueOtp($uid, (string)$u['email']);
    if (!$ok) {
      flash('error', 'Gagal mengirim OTP. Coba lagi.');
      redirect('/otp');
    }

    flash('success', 'OTP baru sudah dikirim.');
    redirect('/otp');
  }

  public function logout(): void
  {
    Auth::logout();
    flash('success', 'Anda telah logout.');
    redirect('/login');
  }

  private function issueOtp(int $userId, string $email): bool
  {
    $pdo = DB::pdo();

    $otp  = random_digits(6);
    $hash = password_hash($otp, PASSWORD_BCRYPT);

    // invalidate old
    $pdo->prepare("UPDATE otp_codes SET used_at=UTC_TIMESTAMP() WHERE user_id=? AND used_at IS NULL")
        ->execute([$userId]);

    $exp = gmdate('Y-m-d H:i:s', time() + 300); // 5 menit (UTC)
    $ins = $pdo->prepare("INSERT INTO otp_codes (user_id, code_hash, expires_at, created_at) VALUES (?, ?, ?, UTC_TIMESTAMP())");
    $ins->execute([$userId, $hash, $exp]);

    $subject = 'Kode OTP Login';
    $body = "Kode OTP Anda: <b>{$otp}</b><br>Jangan bagikan kode ini ke siapa pun.<br><br>" . app_url('/');

    return self::sendMail($email, $subject, $body);
  }

  // =========================
  // FORGOT / RESET PASSWORD
  // =========================
  public function showForgot(): void
  {
    $this->view('auth/forgot.php', ['csrf' => CSRF::token()]);
  }

  public function sendReset(): void
  {
    CSRF::check();

    $email = trim((string)($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      flash('error', 'Email tidak valid.');
      redirect('/forgot');
    }

    $pdo = DB::pdo();

    $st = $pdo->prepare("SELECT id, name, email FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();

    if (!$u) {
      flash('success', 'Jika email terdaftar, link reset akan dikirim.');
      redirect('/forgot');
    }

    $uid = (int)$u['id'];

    $token = bin2hex(random_bytes(24));
    $tokenHash = password_hash($token, PASSWORD_BCRYPT);
    $exp = gmdate('Y-m-d H:i:s', time() + 3600);

    try {
      $pdo->prepare("DELETE FROM password_resets WHERE user_id=?")->execute([$uid]);
    } catch (\Throwable $e) {
      error_log('RESET DELETE ERROR: ' . $e->getMessage());
    }

    $ins = $pdo->prepare(
      "INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
       VALUES (?, ?, ?, UTC_TIMESTAMP())"
    );
    $ins->execute([$uid, $tokenHash, $exp]);

    $resetUrl = app_url('/reset?token=' . urlencode($token) . '&email=' . urlencode($email));

    $subject = 'Reset Password FastPayTrack';
    $body = "
      Halo <b>" . htmlspecialchars((string)$u['name']) . "</b>,<br><br>
      Kamu meminta reset password.<br>
      Klik link berikut untuk membuat password baru (berlaku 1 jam):<br><br>
      <a href='" . htmlspecialchars($resetUrl) . "'>" . htmlspecialchars($resetUrl) . "</a><br><br>
      Jika kamu tidak merasa meminta reset, abaikan email ini.
    ";

    self::sendMail((string)$u['email'], $subject, $body);

    flash('success', 'Jika email terdaftar, link reset akan dikirim.');
    redirect('/forgot');
  }

  public function showReset(): void
  {
    $token = (string)($_GET['token'] ?? '');
    $email = (string)($_GET['email'] ?? '');

    if (!$token || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      flash('error', 'Link reset tidak valid.');
      redirect('/forgot');
    }

    $this->view('auth/reset.php', [
      'csrf' => CSRF::token(),
      'token' => $token,
      'email' => $email,
    ]);
  }

  public function resetPassword(): void
  {
    CSRF::check();

    $token = trim((string)($_POST['token'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if (!$token || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
      flash('error', 'Data tidak valid (password minimal 8 karakter).');
      redirect('/forgot');
    }

    $pdo = DB::pdo();

    $st = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();

    if (!$u) {
      flash('error', 'Link reset tidak valid.');
      redirect('/forgot');
    }

    $uid = (int)$u['id'];

    $rt = $pdo->prepare(
      "SELECT id, token_hash, expires_at
       FROM password_resets
       WHERE user_id=?
       ORDER BY id DESC
       LIMIT 1"
    );
    $rt->execute([$uid]);
    $row = $rt->fetch();

    if (!$row) {
      flash('error', 'Token reset tidak ditemukan / sudah dipakai.');
      redirect('/forgot');
    }

    $nowUtc = gmdate('Y-m-d H:i:s');
    if (!empty($row['expires_at']) && (string)$row['expires_at'] < $nowUtc) {
      try { $pdo->prepare("DELETE FROM password_resets WHERE user_id=?")->execute([$uid]); } catch (\Throwable $e) {}
      flash('error', 'Token reset sudah kedaluwarsa.');
      redirect('/forgot');
    }

    if (!password_verify($token, (string)$row['token_hash'])) {
      flash('error', 'Token reset tidak valid.');
      redirect('/forgot');
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $uid]);

    try {
      $pdo->prepare("DELETE FROM password_resets WHERE user_id=?")->execute([$uid]);
    } catch (\Throwable $e) {
      error_log('RESET DELETE AFTER USE ERROR: ' . $e->getMessage());
    }

    flash('success', 'Password berhasil direset. Silakan login.');
    redirect('/login');
  }

  // =========================
  // TRUSTED DEVICE HELPERS
  // =========================
  private function cookieConsentAllow(): bool
  {
    $v = (string)($_COOKIE[self::COOKIE_CONSENT_NAME] ?? '');
    return $v === 'allow';
  }

  private function hasTrustedCookie(): bool
  {
    $v = (string)($_COOKIE[self::TRUST_COOKIE_NAME] ?? '');
    return $v !== '';
  }

  private function issueTrustedDevice(int $userId): void
  {
    $pdo = DB::pdo();

    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ip = self::clientIp();

    $selector = bin2hex(random_bytes(6)); // 12 chars hex (sesuai kolom selector CHAR(12))
    $tokenRaw = random_bytes(32);
    $token    = rtrim(strtr(base64_encode($tokenRaw), '+/', '-_'), '='); // base64url
    $tokenHash = hash('sha256', $token);

    $expiresAt = gmdate('Y-m-d H:i:s', time() + (self::TRUST_DAYS * 86400));

    // kolom tabel kamu: user_id, selector, token_hash, user_agent, ip, created_at, last_used_at, expires_at, revoked_at
    $ins = $pdo->prepare(
      "INSERT INTO user_trusted_devices
        (user_id, selector, token_hash, user_agent, ip, created_at, last_used_at, expires_at, revoked_at)
       VALUES
        (?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP(), ?, NULL)"
    );
    $ins->execute([$userId, $selector, $tokenHash, $ua, $ip, $expiresAt]);

    $cookieVal = $this->b64urlEncode($selector . '.' . $token);

    $this->setCookieSafe(
      self::TRUST_COOKIE_NAME,
      $cookieVal,
      time() + (self::TRUST_DAYS * 86400)
    );
  }

  private function isTrustedDeviceValid(int $userId): bool
  {
    $raw = (string)($_COOKIE[self::TRUST_COOKIE_NAME] ?? '');
    if ($raw === '') return false;

    $decoded = $this->b64urlDecode($raw);
    if ($decoded === '') return false;

    $parts = explode('.', $decoded, 2);
    if (count($parts) !== 2) return false;

    [$selector, $token] = $parts;

    if ($selector === '' || $token === '') return false;
    if (strlen($selector) !== 12) return false;

    $pdo = DB::pdo();

    $st = $pdo->prepare(
      "SELECT id, token_hash, expires_at, revoked_at
       FROM user_trusted_devices
       WHERE user_id=? AND selector=? AND revoked_at IS NULL
       ORDER BY id DESC
       LIMIT 1"
    );
    $st->execute([$userId, $selector]);
    $row = $st->fetch();

    if (!$row) return false;

    $nowUtc = gmdate('Y-m-d H:i:s');
    if (!empty($row['expires_at']) && (string)$row['expires_at'] < $nowUtc) {
      return false;
    }

    $expected = (string)$row['token_hash'];
    $got = hash('sha256', $token);
    if (!hash_equals($expected, $got)) {
      return false;
    }

    // update last_used_at
    $upd = $pdo->prepare("UPDATE user_trusted_devices SET last_used_at=UTC_TIMESTAMP() WHERE id=?");
    $upd->execute([(int)$row['id']]);

    return true;
  }

  private function setCookieSafe(string $name, string $value, int $expiresTs): void
  {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    // PHP 7.3+ supports options array
    setcookie($name, $value, [
      'expires'  => $expiresTs,
      'path'     => '/',
      'secure'   => $secure,
      'httponly' => true,
      'samesite' => 'Lax',
    ]);

    // agar langsung kebaca di request yang sama (tanpa refresh)
    $_COOKIE[$name] = $value;
  }

  private function b64urlEncode(string $s): string
  {
    return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
  }

  private function b64urlDecode(string $s): string
  {
    $s = strtr($s, '-_', '+/');
    $pad = strlen($s) % 4;
    if ($pad) $s .= str_repeat('=', 4 - $pad);
    $out = base64_decode($s, true);
    return $out === false ? '' : $out;
  }

  // =========================
  // Helpers
  // =========================
  private static function clientIp(): string
  {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
      $v = $_SERVER[$k] ?? '';
      if (!$v) continue;

      foreach (explode(',', (string)$v) as $ip) {
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
          return $ip;
        }
      }
    }
    return '0.0.0.0';
  }

  private static function deviceLabel(string $ua): string
  {
    $u = strtolower($ua);

    $os = 'Unknown OS';
    if (strpos($u, 'windows') !== false) $os = 'Windows';
    elseif (strpos($u, 'android') !== false) $os = 'Android';
    elseif (strpos($u, 'iphone') !== false || strpos($u, 'ipad') !== false) $os = 'iOS';
    elseif (strpos($u, 'macintosh') !== false || strpos($u, 'mac os') !== false) $os = 'macOS';
    elseif (strpos($u, 'linux') !== false) $os = 'Linux';

    $browser = 'Unknown Browser';
    if (strpos($u, 'edg/') !== false) $browser = 'Edge';
    elseif (strpos($u, 'chrome/') !== false && strpos($u, 'edg/') === false) $browser = 'Chrome';
    elseif (strpos($u, 'firefox/') !== false) $browser = 'Firefox';
    elseif (strpos($u, 'safari/') !== false && strpos($u, 'chrome/') === false) $browser = 'Safari';

    return $browser . ' • ' . $os;
  }
}
