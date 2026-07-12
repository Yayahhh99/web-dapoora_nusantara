<?php
$pageTitle = 'Edit Resep';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('admin/kelola_resep.php');

$resep = $db->prepare("SELECT * FROM recipes WHERE id=?");
$resep->execute([$id]);
$resep = $resep->fetch();
if (!$resep) redirect('admin/kelola_resep.php');

$bahan  = implode("\n", json_decode($resep['bahan'],  true) ?? []);
$langkah = implode("\n", json_decode($resep['langkah'], true) ?? []);
$categories = $db->query("SELECT id, nama FROM categories ORDER BY nama")->fetchAll();
include __DIR__ . '/includes/header_admin.php';
?>
<div class="page-header">
  <h1>Edit Resep</h1>
  <p>Perbarui informasi resep "<?= sanitize($resep['judul']) ?>"</p>
</div>

<div class="admin-form-card">
  <form method="POST" action="<?= BASE_URL ?>/process/recipe_process.php" enctype="multipart/form-data">
    <?= csrfInput() ?>
    <input type="hidden" name="action"    value="edit">
    <input type="hidden" name="recipe_id" value="<?= $resep['id'] ?>">

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Judul Resep *</label>
        <input type="text" name="judul" class="form-control" required maxlength="200"
               value="<?= sanitize($resep['judul']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Kategori *</label>
        <select name="category_id" class="form-control" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id']==$resep['category_id']?'selected':'' ?>>
              <?= sanitize($c['nama']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Foto Resep (kosongkan jika tidak ingin mengganti)</label>
      <?php if ($resep['foto']): ?>
        <div style="margin-bottom:.6rem">
          <img src="<?= UPLOAD_URL.'/recipes/'.sanitize($resep['foto']) ?>"
               style="width:180px;border-radius:var(--radius)" alt="Foto saat ini">
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem">Foto saat ini</div>
        </div>
      <?php endif; ?>
      <input type="file" name="foto" class="form-control" accept="image/*" id="fotoInput">
      <div class="img-preview-wrap" id="previewWrap"><img id="imgPreview" src="" alt="Preview"></div>
    </div>

    <div class="form-group">
      <label class="form-label">Deskripsi Singkat</label>
      <textarea name="deskripsi" class="form-control" rows="3"><?= sanitize($resep['deskripsi']) ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Bahan-bahan *</label>
        <textarea name="bahan" class="form-control" rows="8" required><?= sanitize($bahan) ?></textarea>
        <div class="form-hint">Satu bahan per baris</div>
      </div>
      <div class="form-group">
        <label class="form-label">Langkah Memasak *</label>
        <textarea name="langkah" class="form-control" rows="8" required><?= sanitize($langkah) ?></textarea>
        <div class="form-hint">Satu langkah per baris</div>
      </div>
    </div>

    <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr">
      <div class="form-group">
        <label class="form-label">Waktu Memasak (menit)</label>
        <input type="number" name="waktu_memasak" class="form-control" required min="1"
               value="<?= $resep['waktu_memasak'] ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Porsi</label>
        <input type="number" name="porsi" class="form-control" required min="1"
               value="<?= $resep['porsi'] ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Kesulitan</label>
        <select name="kesulitan" class="form-control">
          <?php foreach (['mudah','sedang','sulit'] as $k): ?>
            <option value="<?= $k ?>" <?= $resep['kesulitan']===$k?'selected':'' ?>><?= ucfirst($k) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="publish" <?= $resep['status']==='publish'?'selected':'' ?>>Publish</option>
          <option value="draft"   <?= $resep['status']==='draft'  ?'selected':'' ?>>Draft</option>
        </select>
      </div>
    </div>

    <div style="display:flex;gap:1rem">
      <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
      <a href="<?= BASE_URL ?>/admin/kelola_resep.php" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>

<?php
$extraJs = '<script>
document.getElementById("fotoInput").addEventListener("change", function() {
  const wrap=document.getElementById("previewWrap"), img=document.getElementById("imgPreview");
  if (this.files && this.files[0]) {
    const r=new FileReader(); r.onload=e=>{img.src=e.target.result;wrap.classList.add("show");}; r.readAsDataURL(this.files[0]);
  }
});
</script>';
include __DIR__ . '/includes/footer_admin.php';
?>
