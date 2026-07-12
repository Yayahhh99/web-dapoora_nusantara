<?php
/**
 * admin/includes/header_admin.php
 * Header HTML + topbar admin
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<head>
  <meta charset="UTF-8">
  <meta name="base-url" content="<?= BASE_URL ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? sanitize($pageTitle).' — Admin | ' : 'Admin | ' ?><?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <?= isset($extraCss) ? $extraCss : '' ?>
</head>
<body class="admin-body">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-main" id="adminMain">

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <span></span><span></span><span></span>
      </button>
      <?php if (isset($pageTitle)): ?>
        <span style="font-weight:700;font-size:.95rem;"><?= sanitize($pageTitle) ?></span>
      <?php endif; ?>
    </div>

    <div class="topbar-right">
      <!-- Notifikasi -->
      <button class="notif-btn" aria-label="Notifikasi">
        🔔
        <?php
        $db = getDB();
        $n = $db->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
        if ($n > 0): ?>
          <span class="notif-count"><?= $n ?></span>
        <?php endif; ?>
      </button>

      <!-- Profil Admin -->
      <div class="admin-profile">
        <img src="<?= BASE_URL ?>/assets/images/profiles/admin.svg" alt="Admin" loading="lazy">
        <div>
          <div class="ap-name"><?= sanitize($_SESSION['admin_nama'] ?? 'Admin') ?></div>
          <div class="ap-role">Administrator</div>
        </div>
        <span>▾</span>
      </div>
    </div>
  </header>

  <!-- Flash message -->
  <?php showFlash(); ?>

  <!-- Page content wrapper starts (closed in footer_admin.php) -->
  <main class="admin-content">
