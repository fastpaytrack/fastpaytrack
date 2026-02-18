<?php
declare(strict_types=1);

namespace App\Lib;

final class Env {
  private static array $data = [];

  public static function load(string $file): void {
    if (!is_file($file)) return;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return;

    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '' || strpos($line, '#') === 0) continue;
      if (strpos($line, '=') === false) continue;

      [$k, $v] = explode('=', $line, 2);
      $k = trim($k);
      $v = trim($v);

      // strip quotes
      if ((strlen($v) >= 2) && (($v[0] === '"' && substr($v, -1) === '"') || ($v[0] === "'" && substr($v, -1) === "'"))) {
        $v = substr($v, 1, -1);
      }
      self::$data[$k] = $v;
    }
  }

  public static function get(string $key, ?string $default = null): ?string {
    return self::$data[$key] ?? $default;
  }

  public static function bool(string $key, bool $default = false): bool {
    $v = strtolower((string)(self::$data[$key] ?? ''));
    if ($v === '') return $default;
    return in_array($v, ['1','true','yes','on'], true);
  }
}
