<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\AdminAuth;
use App\Lib\AdminUtil;

final class AuthController
{
  // =========================================================
  // GET /admin/login  (username step)
  // =========================================================
  public function usernameForm(): void
  {
    // untuk kompatibel dengan routing yang memanggil usernameForm()
    $this->login();
  }

  // =========================================================
  // GET /admin/login/password (password step)
  // =========================================================
  public function passwordForm(): void
  {
    // untuk kompatibel dengan routing yang memanggil passwordForm()
    // tetap pakai halaman login() yang akan render sesuai step session
    $this->login();
  }

  // =========================================================
  // GET /admin/login
  // Render sesuai step: username -> password -> key
  // =========================================================
  public function login(): void
  {
    AdminAuth::start();

    // kalau sudah login, langsung ke dashboard
    if (AdminAuth::isLoggedIn()) {
      header('Location: /admin/dashboard');
      exit;
    }

    $step = AdminAuth::step();
    $tmp  = AdminAuth::tmp();

    $error = $_SESSION['admin_flash_error'] ?? '';
    unset($_SESSION['admin_flash_error']);

    $success = $_SESSION['admin_flash_success'] ?? '';
    unset($_SESSION['admin_flash_success']);

    // Render view sesuai step
    if ($step === 'password') {
      $username = (string)($tmp['username'] ?? '');
      require __DIR__ . '/../../views/admin/login_password.php';
      return;
    }

    if ($step === 'key') {
      $name     = (string)($tmp['name'] ?? '');
      $username = (string)($tmp['username'] ?? '');
      require __DIR__ . '/../../views/admin/login_key.php';
      return;
    }

    // default: username step
    require __DIR__ . '/../../views/admin/login_username.php';
  }

  // =========================================================
  // POST /admin/login  (submit username)
  // =========================================================
  public function usernameDo(): void
  {
    AdminAuth::start();

    $username = trim((string)($_POST['username'] ?? ''));
    if ($username === '') {
      $_SESSION['admin_flash_error'] = 'Username wajib diisi.';
      header('Location: /admin/login');
      exit;
    }

    // cek lock
    if (AdminUtil::isLocked($username)) {
      $_SESSION['admin_flash_error'] = 'Login dikunci sementara. Coba lagi beberapa menit.';
      header('Location: /admin/login');
      exit;
    }

    $db = DB::pdo();
    $st = $db->prepare("SELECT id, name, username, password_hash, access_key_hash FROM admin_users WHERE username = ? LIMIT 1");
    $st->execute([$username]);
    $admin = $st->fetch();

    if (!$admin) {
      AdminUtil::incFail($username);
      $_SESSION['admin_flash_error'] = 'Username Salah!';
      header('Location: /admin/login');
      exit;
    }

    // simpan tmp & lanjut step password
    AdminAuth::setTmp([
      'id' => (int)$admin['id'],
      'name' => (string)$admin['name'],
      'username' => (string)$admin['username'],
      'password_hash' => (string)$admin['password_hash'],
      'access_key_hash' => (string)$admin['access_key_hash'],
    ]);
    AdminAuth::setStep('password');

    header('Location: /admin/login/password');
    exit;
  }

  // =========================================================
  // POST /admin/login/password (submit password)
  // =========================================================
  public function passwordDo(): void
  {
    AdminAuth::start();

    $tmp = AdminAuth::tmp();
    $username = (string)($tmp['username'] ?? '');

    if ($username === '') {
      AdminAuth::setStep('username');
      $_SESSION['admin_flash_error'] = 'Session login habis. Silakan ulangi.';
      header('Location: /admin/login');
      exit;
    }

    // cek lock
    if (AdminUtil::isLocked($username)) {
      $_SESSION['admin_flash_error'] = 'Login dikunci sementara. Coba lagi beberapa menit.';
      header('Location: /admin/login');
      exit;
    }

    $password = (string)($_POST['password'] ?? '');
    $hash = (string)($tmp['password_hash'] ?? '');

    if ($password === '' || $hash === '' || !password_verify($password, $hash)) {
      AdminUtil::incFail($username);
      $_SESSION['admin_flash_error'] = 'Password salah!';
      header('Location: /admin/login/password');
      exit;
    }

    // password benar -> lanjut step key
    AdminAuth::setStep('key');
    header('Location: /admin/login');
    exit;
  }

  // =========================================================
  // POST /admin/login/key (submit access key)
  // =========================================================
  public function keyDo(): void
  {
    AdminAuth::start();

    $tmp = AdminAuth::tmp();
    $username = (string)($tmp['username'] ?? '');

    if ($username === '') {
      AdminAuth::setStep('username');
      $_SESSION['admin_flash_error'] = 'Session login habis. Silakan ulangi.';
      header('Location: /admin/login');
      exit;
    }

    // cek lock (khusus step key)
    if (AdminUtil::isLocked($username)) {
      $_SESSION['admin_flash_error'] = 'Login dikunci sementara 5 menit karena key salah 4x.';
      header('Location: /admin/login');
      exit;
    }

    $key = strtoupper(trim((string)($_POST['access_key'] ?? '')));
    $keyHash = (string)($tmp['access_key_hash'] ?? '');

    if ($key === '' || $keyHash === '' || !password_verify($key, $keyHash)) {
      AdminUtil::incFail($username); // 4x -> lock 5 menit (sesuai logic di AdminUtil)
      $_SESSION['admin_flash_error'] = 'Key yang anda masukan tidak sesuai';
      header('Location: /admin/login');
      exit;
    }

    // sukses login admin
    $adminId = (int)($tmp['id'] ?? 0);
    $name = (string)($tmp['name'] ?? '');
    AdminAuth::login($adminId, $name, $username);

    // update last login meta
    try {
      $db = DB::pdo();
      $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
      $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
      $st = $db->prepare("UPDATE admin_users SET last_login_at = NOW(), last_login_ip = ?, last_login_ua = ? WHERE id = ? LIMIT 1");
      $st->execute([$ip, $ua, $adminId]);
    } catch (\Throwable $e) {
      // jangan gagalkan login
    }

    AdminUtil::resetFail($username);

    header('Location: /admin/dashboard');
    exit;
  }

  // =========================================================
  // GET /admin/logout
  // =========================================================
  public function logout(): void
  {
    AdminAuth::logout();
    header('Location: /admin/login');
    exit;
  }
}