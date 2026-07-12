<?php
$pageTitle = 'Tambah Resep';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db = getDB();
$categories = $db->query("SELECT id, nama FROM categories ORDER BY nama")->fetchAll();
include __DIR__ . '/includes/header_admin.php';
?>
<div class="page-header">
  <h1>Tambah Resep Baru</h1>
  <p>Isi form lengkap untuk menambahkan resep baru</p>
</div>

<div class="admin-form-card">
  <form method="POST" action="<?= BASE_URL ?>/process/recipe_process.php" enctype="multipart/form-data">
    <?= csrfInput() ?>
    <input type="hidden" name="action" value="tambah">

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Judul Resep *</label>
        <input type="text" name="judul" class="form-control" required maxlength="200" placeholder="Contoh: Rendang Daging Sapi">
      </div>
      <div class="form-group">
        <label class="form-label">Kategori *</label>
        <select name="category_id" class="form-control" required>
          <option value="">Pilih kategori</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= sanitize($c['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Foto Resep</label>
      <input type="file" name="foto" class="form-control" accept="image/*" id="fotoInput">
      <div class="img-preview-wrap" id="previewWrap">
        <img id="imgPreview" src="" alt="Preview foto">
      </div>
      <div class="form-hint">JPG/PNG/WebP, maks. 2MB</div>
    </div>

    <div class="form-group">
      <label class="form-label">Deskripsi Singkat</label>
      <textarea name="deskripsi" class="form-control" rows="3" placeholder="Ceritakan tentang resep ini..."></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Bahan-bahan *</label>
        <textarea name="bahan" class="form-control" rows="8" required
                  placeholder="Tulis setiap bahan di baris baru:&#10;500 g daging sapi&#10;2 sdt garam&#10;..."></textarea>
        <div class="form-hint">Tulis setiap bahan di satu baris baru</div>
      </div>
      <div class="form-group">
        <label class="form-label">Langkah Memasak *</label>
        <textarea name="langkah" class="form-control" rows="8" required
                  placeholder="Tulis setiap langkah di baris baru:&#10;Panaskan minyak di wajan&#10;Tumis bumbu hingga harum&#10;..."></textarea>
        <div class="form-hint">Tulis setiap langkah di satu baris baru</div>
      </div>
    </div>

    <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr">
      <div class="form-group">
        <label class="form-label">Waktu Memasak (menit) *</label>
        <input type="number" name="waktu_memasak" class="form-control" required min="1" max="9999" value="30">
      </div>
      <div class="form-group">
        <label class="form-label">Porsi *</label>
        <input type="number" name="porsi" class="form-control" required min="1" max="100" value="4">
      </div>
      <div class="form-group">
        <label class="form-label">Tingkat Kesulitan</label>
        <select name="kesulitan" class="form-control">
          <option value="mudah">Mudah</option>
          <option value="sedang">Sedang</option>
          <option value="sulit">Sulit</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="publish">Publish</option>
          <option value="draft">Draft</option>
        </select>
      </div>
    </div>

    <div style="display:flex;gap:1rem;margin-top:.5rem">
      <button type="submit" class="btn btn-primary">💾 Simpan Resep</button>
      <a href="<?= BASE_URL ?>/admin/kelola_resep.php" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>

<?php
$extraJs = '<script>
document.getElementById("fotoInput").addEventListener("change", function() {
  const wrap = document.getElementById("previewWrap");
  const img  = document.getElementById("imgPreview");
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; wrap.classList.add("show"); };
    reader.readAsDataURL(this.files[0]);
  }
});
</script>';
include __DIR__ . '/includes/footer_admin.php';
?>
