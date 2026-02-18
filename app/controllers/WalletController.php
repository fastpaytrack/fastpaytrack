<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\DB;
use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\MailerSMTP;
use function App\Lib\redirect;
use function App\Lib\flash;

final class WalletController
{
  private function view(string $file, array $data = []): void {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  // GET /wallet/transfer
  public function transferForm(): void
  {
    Auth::requireAuth();
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("SELECT balance_idr, email, name, pin_hash IS NOT NULL AS has_pin FROM users WHERE id=? LIMIT 1");
    $st->execute([$uid]);
    $me = $st->fetch();

    // ✅ ambil 10 transaksi terakhir (in/out) untuk ditampilkan di bawah form
    $hst = $pdo->prepare("
      SELECT t.*,
             u1.email AS from_email,
             u2.email AS to_email
      FROM wallet_transfers t
      JOIN users u1 ON u1.id = t.from_user_id
      JOIN users u2 ON u2.id = t.to_user_id
      WHERE t.from_user_id=? OR t.to_user_id=?
      ORDER BY t.id DESC
      LIMIT 10
    ");
    $hst->execute([$uid, $uid]);
    $recent = $hst->fetchAll();

    $this->view('wallet/transfer.php', [
      'csrf'   => CSRF::token(),
      'me'     => $me,
      'uid'    => $uid,
      'recent' => $recent,
    ]);
  }

  // POST /wallet/transfer
  public function transferDo(): void
  {
    CSRF::check();
    Auth::requireAuth();

    $toEmail = trim((string)($_POST['to_email'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));

    $rawAmt = (string)($_POST['amount_idr'] ?? '');
    $clean = preg_replace('/[^0-9]/', '', $rawAmt);
    $amount = (int)$clean;

    $pin = trim((string)($_POST['pin'] ?? ''));

    $min = 1000; // minimal transfer
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
      flash('error', 'Email tujuan tidak valid.');
      redirect('/wallet/transfer');
    }
    if ($amount < $min) {
      flash('error', 'Minimal transfer Rp ' . number_format($min,0,',','.') . '.');
      redirect('/wallet/transfer');
    }
    if (!preg_match('/^\d{6}$/', $pin)) {
      flash('error', 'PIN wajib 6 digit angka.');
      redirect('/wallet/transfer');
    }

    $pdo = DB::pdo();
    $fromId = (int)Auth::id();

    // ambil sender + cek pin
    $meSt = $pdo->prepare("SELECT id, name, email, balance_idr, pin_hash FROM users WHERE id=? LIMIT 1");
    $meSt->execute([$fromId]);
    $me = $meSt->fetch();

    if (!$me) {
      flash('error', 'Akun tidak ditemukan.');
      redirect('/logout');
    }

    if (empty($me['pin_hash'])) {
      flash('error', 'Kamu belum membuat PIN. Buat PIN dulu untuk transfer.');
      redirect('/pin');
    }

    if (!password_verify($pin, (string)$me['pin_hash'])) {
      flash('error', 'PIN salah.');
      redirect('/wallet/transfer');
    }

    if (strtolower((string)$me['email']) === strtolower($toEmail)) {
      flash('error', 'Tidak bisa transfer ke email sendiri.');
      redirect('/wallet/transfer');
    }

    // cari penerima
    $toSt = $pdo->prepare("SELECT id, name, email FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
    $toSt->execute([$toEmail]);
    $to = $toSt->fetch();
    if (!$to) {
      flash('error', 'Email tujuan tidak terdaftar.');
      redirect('/wallet/transfer');
    }
    $toId = (int)$to['id'];

    $transferId = 0;

    // transaksi + lock rows
    $pdo->beginTransaction();
    try {
      // lock sender
      $lockFrom = $pdo->prepare("SELECT balance_idr FROM users WHERE id=? FOR UPDATE");
      $lockFrom->execute([$fromId]);
      $fromBal = (int)($lockFrom->fetch()['balance_idr'] ?? 0);

      if ($fromBal < $amount) {
        $pdo->rollBack();
        redirect('/wallet/transfer/failed?reason=insufficient');
      }

      // lock receiver
      $lockTo = $pdo->prepare("SELECT balance_idr FROM users WHERE id=? FOR UPDATE");
      $lockTo->execute([$toId]);
      $lockTo->fetch();

      // debit sender
      $deb = $pdo->prepare("UPDATE users SET balance_idr = balance_idr - ? WHERE id=?");
      $deb->execute([$amount, $fromId]);

      // credit receiver
      $cred = $pdo->prepare("UPDATE users SET balance_idr = balance_idr + ? WHERE id=?");
      $cred->execute([$amount, $toId]);

      // log transfer
      $ins = $pdo->prepare("INSERT INTO wallet_transfers (from_user_id, to_user_id, amount_idr, note) VALUES (?,?,?,?)");
      $ins->execute([$fromId, $toId, $amount, $note ?: null]);

      $transferId = (int)$pdo->lastInsertId();

      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      error_log("WALLET TRANSFER ERROR: " . $e->getMessage());
      redirect('/wallet/transfer/failed?reason=error');
    }

    // ✅ Kirim email notifikasi (setelah commit)
    $this->sendTransferEmails(
      (string)$me['name'], (string)$me['email'],
      (string)$to['name'], (string)$to['email'],
      $amount,
      $note
    );

    // redirect ke halaman sukses
    redirect('/wallet/transfer/success?id=' . $transferId);
  }

  private function sendTransferEmails(
    string $fromName,
    string $fromEmail,
    string $toName,
    string $toEmail,
    int $amountIdr,
    string $note
  ): void
  {
    $amt = 'Rp ' . number_format($amountIdr, 0, ',', '.');
    $dt = new \DateTime('now'); // mengikuti timezone server (sudah Asia/Jakarta via bootstrap)
    $when = $dt->format('d M Y H:i');

    // Email untuk pengirim
    $subjectSender = "FastPayTrack - Transfer Berhasil ({$amt})";
    $htmlSender = "
      <div style='font-family:Arial,sans-serif;color:#111827;'>
        <h2>Transfer Berhasil ✅</h2>
        <p>Kamu berhasil mengirim saldo sebesar <b>{$amt}</b>.</p>
        <div style='margin:12px 0;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;'>
          <div style='color:#6b7280;font-size:12px;font-weight:700;'>Waktu</div>
          <div style='font-weight:900;'>{$when}</div>
          <div style='height:10px;'></div>
          <div style='color:#6b7280;font-size:12px;font-weight:700;'>Dari</div>
          <div style='font-weight:900;'>{$this->esc($fromName)} — {$this->esc($fromEmail)}</div>
          <div style='height:10px;'></div>
          <div style='color:#6b7280;font-size:12px;font-weight:700;'>Ke</div>
          <div style='font-weight:900;'>{$this->esc($toName)} — {$this->esc($toEmail)}</div>
          ".($note ? "<div style='height:10px;'></div><div style='color:#6b7280;font-size:12px;font-weight:700;'>Catatan</div><div style='font-weight:900;'>{$this->esc($note)}</div>" : "")."
        </div>
        <p style='color:#6b7280;font-size:12px;'>Terima kasih telah menggunakan FastPayTrack.</p>
      </div>
    ";

    // Email untuk penerima
    $subjectReceiver = "FastPayTrack - Saldo Masuk ({$amt})";
    $htmlReceiver = "
      <div style='font-family:Arial,sans-serif;color:#111827;'>
        <h2>Saldo Masuk ✅</h2>
        <p>Kamu menerima saldo sebesar <b>{$amt}</b> ke akun FastPayTrack kamu.</p>
        <div style='margin:12px 0;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;'>
          <div style='color:#6b7280;font-size:12px;font-weight:700;'>Waktu</div>
          <div style='font-weight:900;'>{$when}</div>
          <div style='height:10px;'></div>
          <div style='color:#6b7280;font-size:12px;font-weight:700;'>Dari</div>
          <div style='font-weight:900;'>{$this->esc($fromName)} — {$this->esc($fromEmail)}</div>
          ".($note ? "<div style='height:10px;'></div><div style='color:#6b7280;font-size:12px;font-weight:700;'>Catatan</div><div style='font-weight:900;'>{$this->esc($note)}</div>" : "")."
        </div>
        <p style='color:#6b7280;font-size:12px;'>Jika ini bukan kamu, segera hubungi admin.</p>
      </div>
    ";

    try {
      if ($fromEmail) MailerSMTP::send($fromEmail, $subjectSender, $htmlSender);
    } catch (\Throwable $e) {
      error_log("TRANSFER MAIL SENDER ERROR: " . $e->getMessage());
    }

    try {
      if ($toEmail && strtolower($toEmail) !== strtolower($fromEmail)) {
        MailerSMTP::send($toEmail, $subjectReceiver, $htmlReceiver);
      }
    } catch (\Throwable $e) {
      error_log("TRANSFER MAIL RECEIVER ERROR: " . $e->getMessage());
    }
  }

  private function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }

  // GET /wallet/transfer/success?id=123
  public function transferSuccess(): void
  {
    Auth::requireAuth();
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) redirect('/wallet/transfer');

    $pdo = DB::pdo();
    $uid = (int)Auth::id();

    $st = $pdo->prepare("
      SELECT t.*,
             u1.name AS from_name, u1.email AS from_email,
             u2.name AS to_name,   u2.email AS to_email
      FROM wallet_transfers t
      JOIN users u1 ON u1.id = t.from_user_id
      JOIN users u2 ON u2.id = t.to_user_id
      WHERE t.id=?
      LIMIT 1
    ");
    $st->execute([$id]);
    $tr = $st->fetch();

    if (!$tr) {
      flash('error', 'Data transfer tidak ditemukan.');
      redirect('/wallet/transfer');
    }

    if ((int)$tr['from_user_id'] !== $uid && (int)$tr['to_user_id'] !== $uid) {
      flash('error', 'Tidak memiliki akses untuk melihat transfer ini.');
      redirect('/wallet/transfer');
    }

    $this->view('wallet/transfer_success.php', [
      'tr' => $tr
    ]);
  }

  // GET /wallet/transfer/failed?reason=insufficient|error
  public function transferFailed(): void
  {
    Auth::requireAuth();
    $reason = (string)($_GET['reason'] ?? 'error');

    $this->view('wallet/transfer_failed.php', [
      'reason' => $reason
    ]);
  }

  // GET /wallet/transfer/history
  public function transferHistory(): void
  {
    Auth::requireAuth();
    $pdo = DB::pdo();
    $uid = Auth::id();

    $st = $pdo->prepare("
      SELECT t.*,
             u1.email AS from_email,
             u2.email AS to_email
      FROM wallet_transfers t
      JOIN users u1 ON u1.id = t.from_user_id
      JOIN users u2 ON u2.id = t.to_user_id
      WHERE t.from_user_id=? OR t.to_user_id=?
      ORDER BY t.id DESC
      LIMIT 100
    ");
    $st->execute([$uid, $uid]);
    $rows = $st->fetchAll();

    $meSt = $pdo->prepare("SELECT balance_idr FROM users WHERE id=? LIMIT 1");
    $meSt->execute([$uid]);
    $me = $meSt->fetch();

    $this->view('wallet/transfer_history.php', [
      'rows' => $rows,
      'balance' => (int)($me['balance_idr'] ?? 0),
    ]);
  }
}
