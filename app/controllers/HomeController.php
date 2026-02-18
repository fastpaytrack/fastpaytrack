<?php
declare(strict_types=1);

namespace App\Controllers;

final class HomeController
{
  private function view(string $file, array $data = []): void
  {
    extract($data);
    require __DIR__ . '/../views/partials/head.php';
    require __DIR__ . '/../views/partials/flash.php';
    require __DIR__ . '/../views/' . $file;
    require __DIR__ . '/../views/partials/foot.php';
  }

  public function index(): void
  {
    // Public homepage
    $this->view('home.php', [
      'bodyClass' => 'page-home',
      'metaTitle' => 'FastPayTrack — Fast, simple, modern payments',
      'metaDesc'  => 'FastPayTrack membantu transaksi, topup, dan pembayaran lebih cepat, aman, dan rapi untuk bisnis & personal.',
    ]);
  }
}
