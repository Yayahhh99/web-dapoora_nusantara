<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/security.php';
requireLogin();

$db   = getDB();
$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

$pageTitle = 'Profil Saya';
include __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container" style="max-width:720px">

    <h1 style="font-size:1.75rem;margin-bottom:2rem">👤 Profil Saya</h1>

    <div style="display:grid;grid-template-columns:200px 1fr;gap:2rem;align-items:start">

      <!-- Foto -->
      <div style="text-align:center">
        <img src="<?= $user['foto_profil'] ? UPLOAD_URL.'/profiles/'.sanitize($user['foto_profil']) : BASE_URL.'/assets/images/profiles/default.png' ?>"
             alt="Foto Profil" style="width:140px;height:140px;border-radius:50%;object-fit:cover;border:3px solid var(--primary)">
        <p style="margin-top:.5rem;font-weight:700;font-size:1.05rem"><?= sanitize($user['nama']) ?></p>
        <p style="color:var(--text-muted);font-size:.85rem"><?= sanitize($user['email']) ?></p>
        <p style="color:var(--text-muted);font-size:.78rem;margin-top:.25rem">Bergabung <?= formatTanggal($user['created_at']) ?></p>
      </div>

      <div>
        <!-- Form Edit Profil -->
        <div class="card" style="padding:1.5rem;margin-bottom:1.5rem">
          <h3 style="margin-bottom:1.25rem;font-size:1rem">✏️ Edit Profil</h3>
          <form method="POST" action="<?= BASE_URL ?>/process/user_process.php"
                enctype="multipart/form-data">
            <?= csrfInput() ?>
            <input type="hidden" name="action" value="edit_profil">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" required maxlength="100"
                     value="<?= sanitize($user['nama']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Foto Profil</label>
              <input type="file" name="foto" class="form-control" accept="image/*">
              <div class="form-hint">JPG/PNG/WebP, maks. 2MB</div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
          </form>
        </div>

        <!-- Form Ganti Password -->
        <div class="card" style="padding:1.5rem">
          <h3 style="margin-bottom:1.25rem;font-size:1rem">🔒 Ganti Password</h3>
          <form method="POST" action="<?= BASE_URL ?>/process/user_process.php">
            <?= csrfInput() ?>
            <input type="hidden" name="action" value="ganti_password">
            <div class="form-group">
              <label class="form-label">Password Lama</label>
              <input type="password" name="password_lama" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <input type="password" name="password_baru" class="form-control" required minlength="8">
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="konfirmasi" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Ganti Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
