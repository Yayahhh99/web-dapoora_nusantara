<?php
$pageTitle = 'Profil Admin';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db    = getDB();
$admin = $db->prepare("SELECT * FROM admins WHERE id=?");
$admin->execute([$_SESSION['admin_id']]);
$admin = $admin->fetch();

include __DIR__ . '/includes/header_admin.php';
?>
<div class="page-header"><h1>Profil Admin</h1></div>

<div style="max-width:600px">
  <div class="admin-form-card" style="margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;gap:1.25rem;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--gray-200)">
      <img src="<?= BASE_URL ?>/assets/images/profiles/admin.svg" alt="Admin"
           style="width:72px;height:72px;border-radius:50%;border:3px solid var(--primary)">
      <div>
        <div style="font-weight:800;font-size:1.15rem"><?= sanitize($admin['nama']) ?></div>
        <div style="color:var(--text-muted);font-size:.875rem"><?= sanitize($admin['email']) ?></div>
        <div style="margin-top:.35rem">
          <span class="badge badge-primary">Administrator</span>
        </div>
      </div>
    </div>

    <h3 style="font-size:1rem;margin-bottom:1.25rem">✏️ Edit Profil</h3>
    <form method="POST" action="<?= BASE_URL ?>/admin/process/admin_auth_process.php">
      <?= csrfInput() ?>
      <input type="hidden" name="action" value="edit_profil">
      <div class="form-group">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" required value="<?= sanitize($admin['nama']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= sanitize($admin['email']) ?>">
      </div>
      <button type="submit" class="btn btn-primary">💾 Simpan</button>
    </form>
  </div>

  <div class="admin-form-card">
    <h3 style="font-size:1rem;margin-bottom:1.25rem">🔒 Ganti Password</h3>
    <form method="POST" action="<?= BASE_URL ?>/admin/process/admin_auth_process.php">
      <?= csrfInput() ?>
      <input type="hidden" name="action" value="ganti_password_admin">
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
      <button type="submit" class="btn btn-primary">🔑 Ganti Password</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer_admin.php'; ?>
