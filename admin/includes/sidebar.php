<?php
/**
 * admin/includes/sidebar.php
 * Sidebar navigasi admin Dapoora
 */
$adminPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">

  <!-- Brand -->
  <a href="<?= BASE_URL ?>/admin/index.php" class="sidebar-brand">
    <img src="<?= BASE_URL ?>/assets/images/ikon.svg" 
     alt="Logo Dapoora"
     style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <div>
      <div class="s-name"><?= SITE_NAME ?></div>
      <div class="s-tag"><?= SITE_TAGLINE ?></div>
    </div>
  </a>

  <!-- RESEP -->
  <div class="sidebar-section">
    <div class="sidebar-label">Resep</div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/admin/index.php"
         class="<?= $adminPage==='index.php'?'active':'' ?>">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <a href="<?= BASE_URL ?>/admin/kelola_resep.php"
         class="<?= $adminPage==='kelola_resep.php'?'active':'' ?>">
        <span class="nav-icon">📋</span> Kelola Resep
      </a>
      <a href="<?= BASE_URL ?>/admin/tambah_resep.php"
         class="<?= $adminPage==='tambah_resep.php'?'active':'' ?>">
        <span class="nav-icon">➕</span> Tambah Resep
      </a>
      <a href="<?= BASE_URL ?>/admin/kategori.php"
         class="<?= $adminPage==='kategori.php'?'active':'' ?>">
        <span class="nav-icon">🏷️</span> Kategori
      </a>
      <a href="<?= BASE_URL ?>/admin/komentar.php"
         class="<?= $adminPage==='komentar.php'?'active':'' ?>">
        <span class="nav-icon">💬</span> Komentar
        <?php
        // Badge komentar pending
        $db = getDB();
        $c = $db->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
        if ($c > 0): ?>
          <span class="nav-badge"><?= $c ?></span>
        <?php endif; ?>
      </a>
    </nav>
  </div>

  <!-- PENGGUNA -->
  <div class="sidebar-section">
    <div class="sidebar-label">Pengguna</div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/admin/pengguna.php"
         class="<?= $adminPage==='pengguna.php'?'active':'' ?>">
        <span class="nav-icon">👥</span> Daftar Pengguna
      </a>
    </nav>
  </div>

  <!-- ANALITIK -->
  <div class="sidebar-section">
    <div class="sidebar-label">Analitik</div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/admin/statistik.php"
         class="<?= $adminPage==='statistik.php'?'active':'' ?>">
        <span class="nav-icon">📈</span> Statistik
      </a>
    </nav>
  </div>

  <!-- PENGATURAN -->
  <div class="sidebar-section">
    <div class="sidebar-label">Pengaturan</div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/admin/profil_admin.php"
         class="<?= $adminPage==='profil_admin.php'?'active':'' ?>">
        <span class="nav-icon">👤</span> Profil Admin
      </a>
    </nav>
  </div>

  <!-- Logout -->
  <div class="sidebar-footer">
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>/admin/logout.php">
        <span class="nav-icon">🚪</span> Keluar
      </a>
    </nav>
  </div>

</aside>
