<?php
/**
 * pages/404.php — Halaman Not Found
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

http_response_code(404);
$pageTitle = 'Halaman Tidak Ditemukan';
include __DIR__ . '/../includes/header.php';
?>

<section style="min-height:65vh;display:flex;align-items:center;justify-content:center;background:var(--gray-50)">
  <div style="text-align:center;padding:2rem 1rem">
    <div style="font-size:6rem;margin-bottom:1rem;line-height:1">🍳</div>
    <h1 style="font-size:5rem;font-family:var(--font-display);color:var(--primary);line-height:1;margin-bottom:.5rem">404</h1>
    <h2 style="font-size:1.5rem;margin-bottom:1rem;color:var(--dark)">Halaman Tidak Ditemukan</h2>
    <p style="color:var(--text-muted);max-width:400px;margin:0 auto 2rem;line-height:1.7">
      Sepertinya resep yang kamu cari tidak ada di dapur kami. Mungkin sudah habis dimasak? 😄
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="<?= BASE_URL ?>" class="btn btn-primary">🏠 Kembali ke Beranda</a>
      <a href="<?= BASE_URL ?>/pages/resep.php" class="btn btn-outline">🍴 Jelajahi Resep</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
