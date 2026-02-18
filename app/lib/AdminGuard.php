<?php
declare(strict_types=1);

namespace App\Lib;

use function App\Lib\redirect;

final class AdminGuard
{
  public static function requireAdmin(): void {
    AdminAuth::start();
    if (!AdminAuth::isLoggedIn()) {
      redirect('/admin/login');
    }
  }
}
