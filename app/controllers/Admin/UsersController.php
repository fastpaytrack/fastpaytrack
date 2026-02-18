<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Lib\DB;
use App\Lib\CSRF;
use App\Lib\AdminGuard;
use function App\Lib\redirect;
use function App\Lib\flash;

final class UsersController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../../views/admin/partials/head.php';
    require __DIR__ . '/../../views/partials/flash.php';
    require __DIR__ . '/../../views/admin/' . $file;
    require __DIR__ . '/../../views/admin/partials/foot.php';
  }

  public function index(): void
  {
    AdminGuard::requireAdmin();
    $pdo = DB::pdo();
    $q = trim((string)($_GET['q'] ?? ''));

    if ($q !== '') {
      $st = $pdo->prepare("SELECT id,name,email,balance_idr,is_active,created_at FROM users WHERE email LIKE ? OR name LIKE ? ORDER BY id DESC");
      $st->execute(['%' . $q . '%', '%' . $q . '%']);
    } else {
      $st = $pdo->query("SELECT id,name,email,balance_idr,is_active,created_at FROM users ORDER BY id DESC LIMIT 200");
    }
    $users = $st->fetchAll();

    $this->view('users_index.php', [
      'csrf' => CSRF::token(),
      'users' => $users,
      'q' => $q,
      'metaTitle' => 'Admin • Users',
      'page' => 'users',
    ]);
  }

  public function createForm(): void
  {
    AdminGuard::requireAdmin();
    $this->view('users_create.php', [
      'csrf' => CSRF::token(),
      'metaTitle' => 'Admin • Add User',
      'page' => 'users',
    ]);
  }

  public function createDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');
    $balance = (int)preg_replace('/[^0-9]/', '', (string)($_POST['balance_idr'] ?? '0'));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
      flash('error', 'Input tidak valid. Password minimal 6 karakter.');
      redirect('/admin/users/create');
    }

    $pdo = DB::pdo();
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    try {
      $st = $pdo->prepare("INSERT INTO users (name,email,password_hash,balance_idr,is_active) VALUES (?,?,?,?,1)");
      $st->execute([$name, $email, $hash, $balance]);
    } catch (\Throwable $e) {
      flash('error', 'Gagal menambah user (email mungkin sudah terdaftar).');
      redirect('/admin/users/create');
    }

    flash('success', 'User berhasil dibuat.');
    redirect('/admin/users');
  }

  public function editForm(): void
  {
    AdminGuard::requireAdmin();
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) redirect('/admin/users');

    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT id,name,email,balance_idr,is_active,created_at FROM users WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $u = $st->fetch();
    if (!$u) redirect('/admin/users');

    // simple activity: last orders/topups/transfers
    $orders = $pdo->prepare("SELECT id, status, total_idr, created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 10");
    $orders->execute([$id]);

    $topups = $pdo->prepare("SELECT id, status, amount_idr, created_at FROM wallet_topups WHERE user_id=? ORDER BY id DESC LIMIT 10");
    $topups->execute([$id]);

    $tr = $pdo->prepare("SELECT id, from_user_id, to_user_id, amount_idr, created_at FROM wallet_transfers WHERE from_user_id=? OR to_user_id=? ORDER BY id DESC LIMIT 10");
    $tr->execute([$id,$id]);

    $this->view('users_edit.php', [
      'csrf' => CSRF::token(),
      'u' => $u,
      'orders' => $orders->fetchAll(),
      'topups' => $topups->fetchAll(),
      'transfers' => $tr->fetchAll(),
      'metaTitle' => 'Admin • Edit User',
      'page' => 'users',
    ]);
  }

  public function editDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) redirect('/admin/users');

    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $active = (int)($_POST['is_active'] ?? 1) === 1 ? 1 : 0;
    $newPass = trim((string)($_POST['new_password'] ?? ''));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      flash('error', 'Nama/email tidak valid.');
      redirect('/admin/users/edit?id=' . $id);
    }

    $pdo = DB::pdo();
    try {
      $pdo->prepare("UPDATE users SET name=?, email=?, is_active=? WHERE id=?")->execute([$name,$email,$active,$id]);

      if ($newPass !== '') {
        if (strlen($newPass) < 6) {
          flash('error', 'Password baru minimal 6 karakter.');
          redirect('/admin/users/edit?id=' . $id);
        }
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash,$id]);
      }
    } catch (\Throwable $e) {
      flash('error', 'Gagal menyimpan user (email mungkin sudah dipakai).');
      redirect('/admin/users/edit?id=' . $id);
    }

    flash('success', 'User berhasil diperbarui.');
    redirect('/admin/users/edit?id=' . $id);
  }

  public function deleteDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) redirect('/admin/users');

    $pdo = DB::pdo();
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);

    flash('success', 'User berhasil dihapus.');
    redirect('/admin/users');
  }

  public function adjustBalanceDo(): void
  {
    CSRF::check();
    AdminGuard::requireAdmin();

    $id = (int)($_POST['id'] ?? 0);
    $delta = (int)preg_replace('/[^0-9\-]/', '', (string)($_POST['delta_idr'] ?? '0')); // allow negative if typed
    if ($id <= 0 || $delta === 0) redirect('/admin/users/edit?id=' . $id);

    $pdo = DB::pdo();

    // safe update
    $pdo->beginTransaction();
    try {
      $pdo->prepare("SELECT balance_idr FROM users WHERE id=? FOR UPDATE")->execute([$id]);
      $pdo->prepare("UPDATE users SET balance_idr = balance_idr + ? WHERE id=?")->execute([$delta, $id]);
      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      flash('error', 'Gagal update saldo user.');
      redirect('/admin/users/edit?id=' . $id);
    }

    flash('success', 'Saldo user berhasil diubah.');
    redirect('/admin/users/edit?id=' . $id);
  }
}
