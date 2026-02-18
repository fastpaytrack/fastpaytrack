<?php
declare(strict_types=1);

namespace App\Lib;

use PDO;
use PDOStatement;

final class DB {
  private static ?PDO $pdo = null;

  public static function pdo(): PDO {
    if (self::$pdo) return self::$pdo;

    $host = Env::get('DB_HOST','localhost');
    $db = Env::get('DB_NAME','');
    $user = Env::get('DB_USER','');
    $pass = Env::get('DB_PASS','');
    $charset = Env::get('DB_CHARSET','utf8mb4');

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    self::$pdo = $pdo;
    return $pdo;
  }

  /** Jalankan query prepared */
  public static function query(string $sql, array $params = []): PDOStatement {
    $stmt = self::pdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  /** Ambil 1 row (atau null) */
  public static function fetch(string $sql, array $params = []): ?array {
    $stmt = self::query($sql, $params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
  }

  /** Ambil semua row */
  public static function fetchAll(string $sql, array $params = []): array {
    return self::query($sql, $params)->fetchAll();
  }

  /** Execute non-select, return jumlah affected rows */
  public static function exec(string $sql, array $params = []): int {
    return self::query($sql, $params)->rowCount();
  }

  /** Last insert id */
  public static function lastInsertId(): string {
    return self::pdo()->lastInsertId();
  }
}
