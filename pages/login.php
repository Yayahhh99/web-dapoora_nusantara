<?php
/**
 * pages/login.php
 * Halaman login user
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

// Sudah login → redirect ke beranda
if (isLoggedIn()) redirect('index.php');

$pageTitle = 'Masuk';
$token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?> — <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">

    <div class="auth-header">
      <div class="auth-logo">🍳</div>
      <h2>Selamat Datang Kembali!</h2>
      <p>Masuk ke akun <?= SITE_NAME ?> kamu</p>
    </div>

    <div class="auth-body">
      <?php showFlash(); ?>

      <form method="POST" action="<?= BASE_URL ?>/process/auth_process.php" novalidate>
        <?= csrfInput() ?>
        <input type="hidden" name="action" value="login">

        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="contoh@email.com" required
                 value="<?= sanitize($_SESSION['old_email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Masukkan password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100" style="margin-top:.5rem">
          Masuk →
        </button>
      </form>
    </div>

    <div class="auth-footer">
      Belum punya akun? <a href="<?= BASE_URL ?>/pages/register.php">Daftar sekarang</a>
    </div>
  </div>
</div>
<?php unset($_SESSION['old_email']); ?>
</body>
</html>
