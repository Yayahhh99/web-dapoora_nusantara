<?php
/**
 * admin/login.php
 * Halaman login admin
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

if (isAdminLoggedIn()) redirect('admin/index.php');

$pageTitle = 'Login Admin';
generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <style>
    .auth-header { background: #2C1810; }
    .auth-header h2, .auth-header p { color: rgba(255,255,255,.9); }
  </style>
</head>
<body>
<div class="auth-page" style="background:linear-gradient(135deg,#2C1810 0%,#4A2C1A 100%)">
  <div class="auth-card">

    <div class="auth-header">
      <div class="auth-logo"></div>
      <h2>Admin Panel</h2>
      <p>Masuk sebagai administrator <?= SITE_NAME ?></p>
    </div>

    <div class="auth-body">
      <?php showFlash(); ?>

      <form method="POST" action="<?= BASE_URL ?>/admin/process/admin_auth_process.php" novalidate>
        <?= csrfInput() ?>

        <div class="form-group">
          <label class="form-label" for="email">Email Admin</label>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="admin@dapoora.com" required
                 value="<?= sanitize($_SESSION['old_admin_email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Password admin" required>
        </div>

        <button type="submit" class="btn btn-primary w-100" style="margin-top:.5rem">
          Masuk ke Dashboard →
        </button>
      </form>
    </div>

    <div class="auth-footer">
      <a href="<?= BASE_URL ?>">← Kembali ke Beranda</a>
    </div>
  </div>
</div>
<?php unset($_SESSION['old_admin_email']); ?>
</body>
</html>
