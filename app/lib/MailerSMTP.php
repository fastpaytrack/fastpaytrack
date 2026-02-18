<?php
declare(strict_types=1);

namespace App\Lib;

final class MailerSMTP {
  public static function send(string $to, string $subject, string $html, ?string $text = null): void {
    $host = Env::get('SMTP_HOST','smtp.gmail.com');
    $port = (int)Env::get('SMTP_PORT','587');
    $user = Env::get('SMTP_USERNAME','');
    $pass = Env::get('SMTP_PASSWORD','');
    $from = Env::get('MAIL_FROM', $user);
    $fromName = Env::get('MAIL_FROM_NAME','FastPayTrack');

    if (!$user || !$pass) {
      throw new \RuntimeException("SMTP credentials missing");
    }

    $sock = fsockopen($host, $port, $errno, $errstr, 20);
    if (!$sock) throw new \RuntimeException("SMTP connect failed: $errstr ($errno)");

    self::expect($sock, 220);
    self::cmd($sock, "EHLO fastpaytrack.com", 250);

    // STARTTLS
    self::cmd($sock, "STARTTLS", 220);
    if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
      throw new \RuntimeException("STARTTLS failed");
    }
    self::cmd($sock, "EHLO fastpaytrack.com", 250);

    // AUTH LOGIN
    self::cmd($sock, "AUTH LOGIN", 334);
    self::cmd($sock, base64_encode($user), 334);
    self::cmd($sock, base64_encode($pass), 235);

    $boundary = 'b' . bin2hex(random_bytes(8));
    $headers = [];
    $headers[] = "From: " . self::encodeHeader($fromName) . " <{$from}>";
    $headers[] = "To: <{$to}>";
    $headers[] = "Subject: " . self::encodeHeader($subject);
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";

    $textPart = $text ?? strip_tags($html);

    $body = "";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $textPart . "\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $html . "\r\n";
    $body .= "--{$boundary}--\r\n";

    self::cmd($sock, "MAIL FROM:<{$from}>", 250);
    self::cmd($sock, "RCPT TO:<{$to}>", 250);
    self::cmd($sock, "DATA", 354);

    $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
    fwrite($sock, $data . "\r\n");
    self::expect($sock, 250);

    self::cmd($sock, "QUIT", 221);
    fclose($sock);
  }

  private static function encodeHeader(string $s): string {
    return '=?UTF-8?B?' . base64_encode($s) . '?=';
  }

  private static function cmd($sock, string $cmd, int $expectCode): void {
    fwrite($sock, $cmd . "\r\n");
    self::expect($sock, $expectCode);
  }

  private static function expect($sock, int $code): void {
    $line = '';
    while (($l = fgets($sock, 515)) !== false) {
      $line .= $l;
      // multi-line responses have "-" after code
      if (preg_match('/^\d{3}\s/', $l)) break;
    }
    $got = (int)substr($line, 0, 3);
    if ($got !== $code && !($code === 250 && $got === 250)) {
      throw new \RuntimeException("SMTP expected {$code}, got {$got}: {$line}");
    }
  }
}
