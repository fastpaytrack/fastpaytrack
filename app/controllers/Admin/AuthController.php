<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminAuth;
use App\Lib\AdminGuard;
use function App\Lib\flash;
use function App\Lib\redirect;

final class AuthController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../../views/admin/partials/head.php';
    require __DIR__ . '/../../views/partials/flash.php';
    require __DIR__ . '/../../views/admin/' . $file;
    require __DIR__ . '/../../views/admin/partials/foot.php';
  }

  // GET /admin/login
  public function login(): void
  {
    AdminAuth::bootSeedAdminIfEmpty();

    $stage = AdminAuth::loginStageGet();
    $this->view(
      $stage['stage'] === 'password' ? 'login_password.php' :
      ($stage['stage'] === 'key' ? 'login_key.php' : 'login_username.php'),
      [
        'csrf' => CSRF::token(),
        'stage' => $stage['stage'],
        'data' => $stage['data'],
      ]
    );
  }

  // POST /admin/login/username
  public function postUsername(): void
  {
    CSRF::check();

    $username = trim((string)($_POST['username'] ?? ''));
    if ($username === ''){
      flash('error', 'Username wajib diisi.');
      redirect('/admin/login');
    }

    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT id, username FROM admin_users WHERE username=? LIMIT 1");
    $st->execute([$username]);
    $a = $st->fetch();

    if (!$a){
      flash('error', 'Username Salah!');
      redirect('/admin/login');
    }

    AdminAuth::loginStageSet('password', ['username' => $username]);
    redirect('/admin/login');
  }

  // POST /admin/login/password
  public function postPassword(): void
  {
    CSRF::check();

    $stage = AdminAuth::loginStageGet();
    $username = (string)($stage['data']['username'] ?? '');
    $pass = (string)($_POST['password'] ?? '');

    if ($username === ''){
      AdminAuth::loginStageClear();
      redirect('/admin/login');
    }

    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT id, name, email, username, password_hash, access_key_hash FROM admin_users WHERE username=? LIMIT 1");
    $st->execute([$username]);
    $a = $st->fetch();

    if (!$a || !password_verify($pass, (string)$a['password_hash'])){
      flash('error', 'Password salah!');
      AdminAuth::loginStageSet('password', ['username' => $username]);
      redirect('/admin/login');
    }

    // lanjut ke key step
    AdminAuth::loginStageSet('key', [
      'admin_id' => (int)$a['id'],
      'username' => (string)$a['username'],
      'name' => (string)$a['name'],
      'email' => (string)$a['email'],
    ]);
    redirect('/admin/login');
  }

  // POST /admin/login/key
  public function postKey(): void
  {
    CSRF::check();

    $stage = AdminAuth::loginStageGet();
    $username = (string)($stage['data']['username'] ?? '');
    $adminId = (int)($stage['data']['admin_id'] ?? 0);
    $key = strtoupper(trim((string)($_POST['access_key'] ?? '')));

    if ($username === '' || $adminId <= 0){
      AdminAuth::loginStageClear();
      redirect('/admin/login');
    }

    $lock = AdminGuard::isLocked($username);
    if ($lock['locked']){
      flash('error', 'Login dikunci sementara. Coba lagi setelah: ' . $lock['until']);
      redirect('/admin/login');
    }

    if (!preg_match('/^[A-Z]{16}$/', $key)){
      flash('error', 'Key wajib 16 huruf kapital.');
      redirect('/admin/login');
    }

    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT id, name, email, username, access_key_hash FROM admin_users WHERE id=? LIMIT 1");
    $st->execute([$adminId]);
    $a = $st->fetch();

    if (!$a || !password_verify($key, (string)$a['access_key_hash'])){
      $res = AdminGuard::failKey($username);
      if (!empty($res['locked'])){
        flash('error', 'Key yang anda masukan tidak sesuai. Login dikunci 5 menit.');
      } else {
        flash('error', 'Key yang anda masukan tidak sesuai.');
      }
      redirect('/admin/login');
    }

    AdminGuard::resetFails($username);

    // set session
    AdminAuth::setSession((int)$a['id'], (string)$a['name'], (string)$a['email'], (string)$a['username']);

    // update last login
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $up = $pdo->prepare("UPDATE admin_users SET last_login_at=NOW(), last_login_ip=?, last_login_ua=?, updated_at=NOW() WHERE id=?");
    $up->execute([$ip, $ua, (int)$a['id']]);

    AdminAuth::loginStageClear();
    redirect('/admin/dashboard');
  }

  // GET /admin/logout
  public function logout(): void
  {
    AdminAuth::logout();
    AdminAuth::loginStageClear();
    redirect('/admin/login');
  }
}
