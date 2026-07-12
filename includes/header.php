<?php
/**
 * includes/header.php
 * Navbar publik Dapoora Nusantara
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

// Halaman aktif untuk nav-link
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
  <meta name="description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'Temukan resep masakan khas Indonesia terbaik di ' . SITE_NAME ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <?= isset($extraCss) ? $extraCss : '' ?>
</head>
<body>

<nav class="navbar">
  <div class="navbar-inner">

   <!-- Brand -->
<a href="<?= BASE_URL ?>" class="navbar-brand">
  <img src="<?= BASE_URL ?>/assets/images/ikon.svg"
       alt="Logo Dapoora Nusantara"
       width="56" height="56"
       style="border-radius:50%;object-fit:cover;flex-shrink:0;">
  <div>
    <span class="brand-name"><?= 'Dapoora Nusantara' ?></span>
    <span class="brand-tagline"><?= 'Cita Rasa, Cerita Kita' ?></span>
  </div>
</a>

    <!-- Nav Links -->
    <nav class="navbar-nav" id="navMenu">
      <a href="<?= BASE_URL ?>"                    class="nav-link <?= $currentPage==='index.php'   ?'active':'' ?>">Home</a>
      <a href="<?= BASE_URL ?>/pages/resep.php"    class="nav-link <?= $currentPage==='resep.php'   ?'active':'' ?>">Resep</a>
      <a href="<?= BASE_URL ?>/pages/kategori.php" class="nav-link <?= $currentPage==='kategori.php'?'active':'' ?>">Kategori</a>
      <a href="<?= BASE_URL ?>/pages/bookmark.php" class="nav-link <?= $currentPage==='bookmark.php'?'active':'' ?>">Bookmark</a>
    </nav>

    <!-- Search -->
    <div class="navbar-search" id="navSearch">
      <span class="search-icon">🔍</span>
      <input type="text" id="searchInput" placeholder="Cari resep..." autocomplete="off" aria-label="Cari resep">
      <div class="search-dropdown" id="searchDropdown" role="listbox"></div>
    </div>

    <!-- Actions -->
    <div class="navbar-actions">
      <?php if (isLoggedIn()): ?>
        <div class="user-menu">
          <button class="user-toggle" id="userToggle" aria-expanded="false">
            <img src="<?= !empty($_SESSION['user_foto']) ? UPLOAD_URL.'/profiles/'.sanitize($_SESSION['user_foto']) : BASE_URL.'/assets/images/profiles/default.png' ?>"
                 alt="Foto profil" loading="lazy">
            <span>Hai, <?= sanitize($_SESSION['user_nama'] ?? 'User') ?></span>
            <span>▾</span>
          </button>
          <div class="user-dropdown" id="userDropdown">
            <a href="<?= BASE_URL ?>/pages/profil.php"   class="dropdown-item">👤 Profil Saya</a>
            <a href="<?= BASE_URL ?>/pages/bookmark.php" class="dropdown-item">🔖 Bookmark</a>
            <div class="dropdown-divider"></div>
            <a href="<?= BASE_URL ?>/pages/logout.php"   class="dropdown-item danger">🚪 Keluar</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/pages/login.php"    class="btn btn-outline btn-sm">Masuk</a>
        <a href="<?= BASE_URL ?>/pages/register.php" class="btn btn-primary btn-sm">Daftar</a>
      <?php endif; ?>
    </div>

    <!-- Hamburger -->
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<?php showFlash(); ?>
