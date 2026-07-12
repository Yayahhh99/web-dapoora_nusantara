<?php
$pageTitle = 'Kelola Kategori';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db = getDB();
$cats = $db->query("
    SELECT c.*, COUNT(r.id) AS total_resep
    FROM categories c LEFT JOIN recipes r ON r.category_id=c.id
    GROUP BY c.id ORDER BY c.created_at DESC
")->fetchAll();

// Edit mode
$editCat = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare("SELECT * FROM categories WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editCat = $s->fetch();
}
include __DIR__ . '/includes/header_admin.php';
?>
<div class="page-header"><h1>Kelola Kategori</h1></div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">
  <!-- Tabel -->
  <div class="widget">
    <div class="widget-body">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Nama</th><th>Slug</th><th>Jml Resep</th><th>Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($cats as $c): ?>
              <tr>
                <td style="font-weight:600"><?= sanitize($c['nama']) ?></td>
                <td style="font-size:.8rem;color:var(--text-muted)"><?= sanitize($c['slug']) ?></td>
                <td><?= $c['total_resep'] ?></td>
                <td>
                  <div class="action-btns">
                  <a href="?edit=<?= $c['id'] ?>"
                    class="btn-icon edit"
                    title="Edit">
                      <i class="fa-solid fa-pen-to-square"></i>
                  </a>

                  <?php if ($c['total_resep'] == 0): ?>
                    <button class="btn-icon delete"
                            onclick="hapusKat(<?= $c['id'] ?>)"
                            title="Hapus">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                  <?php else: ?>
                    <span class="btn-icon lock" title="Masih ada resep">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                  <?php endif; ?>
                </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Form tambah / edit -->
  <div class="widget">
    <div class="widget-header">
          <h3 class="widget-title">
    <?php if($editCat): ?>
        <i class="fa-solid fa-pen-to-square"></i>
        Edit Kategori
    <?php else: ?>
        <i class="fa-solid fa-folder"></i>
        Tambah Kategori
    <?php endif; ?>
    </h3>
      <?php if ($editCat): ?>
        <a href="<?= BASE_URL ?>/admin/kategori.php" class="btn btn-outline btn-sm">Batal</a>
      <?php endif; ?>
    </div>
    <div class="widget-body">
      <form method="POST" action="<?= BASE_URL ?>/process/category_process.php">
        <?= csrfInput() ?>
        <input type="hidden" name="action"      value="<?= $editCat ? 'edit' : 'tambah' ?>">
        <?php if ($editCat): ?>
          <input type="hidden" name="category_id" value="<?= $editCat['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Nama Kategori *</label>
          <input type="text" name="nama" class="form-control" required maxlength="100"
                 value="<?= sanitize($editCat['nama'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi</label>
          <textarea name="deskripsi" class="form-control" rows="3"><?= sanitize($editCat['deskripsi'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn-save"> Simpan Kategori</button>
      </form>
    </div>
  </div>
</div>

<form id="hapusKatForm" method="POST" action="<?= BASE_URL ?>/process/category_process.php">
  <?= csrfInput() ?>
  <input type="hidden" name="action"      value="hapus">
  <input type="hidden" name="category_id" id="hapusKatId">
</form>
<?php
$extraJs = '<script>
function hapusKat(id){
  if(confirm("Hapus kategori ini?")) {
    document.getElementById("hapusKatId").value=id;
    document.getElementById("hapusKatForm").submit();
  }
}
</script>';
include __DIR__ . '/includes/footer_admin.php';
?>
