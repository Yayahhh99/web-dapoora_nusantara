<?php
$pageTitle = 'Kelola Resep';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db      = getDB();
$search  = sanitize($_GET['q']        ?? '');
$catF    = sanitize($_GET['kategori'] ?? '');
$page    = max(1,(int)($_GET['page']  ?? 1));
$perPage = 10; $offset = ($page-1)*$perPage;

$where = []; $params = [];
if ($search) { $where[] = "r.judul LIKE ?"; $params[] = "%$search%"; }
if ($catF)   { $where[] = "c.id = ?";       $params[] = $catF; }
$whereSql = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total = $db->prepare("SELECT COUNT(*) FROM recipes r LEFT JOIN categories c ON c.id=r.category_id $whereSql");
$total->execute($params); $total = $total->fetchColumn();
$totalPages = max(1,ceil($total/$perPage));

$stmt = $db->prepare("
    SELECT r.id, r.judul, r.foto, r.status, r.kesulitan, r.waktu_memasak, r.created_at,
           c.nama AS kategori
    FROM recipes r LEFT JOIN categories c ON c.id=r.category_id
    $whereSql ORDER BY r.created_at DESC LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$recipes = $stmt->fetchAll();

$allCats = $db->query("SELECT id, nama FROM categories ORDER BY nama")->fetchAll();
include __DIR__ . '/includes/header_admin.php';
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;
     background:#fff;border:0.5px solid var(--gray-200);border-radius:12px;
     padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
  <div style="display:flex;align-items:center;gap:1rem">
    <div style="width:48px;height:48px;border-radius:12px;background:var(--primary);
                display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">
      📋
    </div>
    <div>
      <h1 style="margin:0;font-size:1.2rem;">Kelola Resep</h1>
      <p style="margin:0;font-size:.82rem;color:var(--text-muted);">
        Daftar semua resep di <?= SITE_NAME ?> &nbsp;·&nbsp;
        <span style="color:var(--primary);font-weight:600;"><?= $total ?> resep</span>
      </p>
    </div>
  </div>
  <a href="<?= BASE_URL ?>/admin/tambah_resep.php" class="btn btn-primary"
     style="display:flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;">
    <span style="font-size:1.1rem;">＋</span> Tambah Resep
  </a>
</div>

<div class="widget">
  <div class="widget-body">
    <!-- Toolbar -->
    <div class="table-toolbar">
      <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;width:100%">
        <div class="search-box">
          <span class="s-icon">🔍</span>
          <input type="text" name="q" placeholder="Cari resep..." value="<?= sanitize($search) ?>">
        </div>
        <select name="kategori" class="filter-select">
          <option value="">Semua Kategori</option>
          <?php foreach ($allCats as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $catF==(string)$c['id']?'selected':'' ?>><?= sanitize($c['nama']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="<?= BASE_URL ?>/admin/kelola_resep.php" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>

    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Foto</th><th>Judul</th><th>Kategori</th><th>Kesulitan</th>
            <th>Status</th><th>Tanggal</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recipes as $r): ?>
            <tr>
              <td>
                <img class="table-thumb"
                     src="<?= $r['foto'] ? UPLOAD_URL.'/recipes/'.sanitize($r['foto']) : BASE_URL.'/assets/images/recipes/placeholder.jpg' ?>"
                     alt="">
              </td>
              <td style="max-width:200px">
                <div style="font-weight:600"><?= sanitize($r['judul']) ?></div>
                <div style="font-size:.78rem;color:var(--text-muted)">⏱ <?= $r['waktu_memasak'] ?> mnt</div>
              </td>
              <td><?= sanitize($r['kategori']) ?></td>
              <td>
                <span class="badge badge-<?= $r['kesulitan']==='mudah'?'success':($r['kesulitan']==='sedang'?'warning':'danger') ?>">
                  <?= ucfirst($r['kesulitan']) ?>
                </span>
              </td>
              <td>
                <span class="badge badge-<?= $r['status']==='publish'?'success':'gray' ?>">
                  <?= ucfirst($r['status']) ?>
                </span>
              </td>
              <td style="white-space:nowrap"><?= formatTanggal($r['created_at']) ?></td>
              <td>
                <div class="action-btns">
  <a href="<?= BASE_URL ?>/admin/edit_resep.php?id=<?= $r['id'] ?>"
     class="btn-icon edit"
     title="Edit">
      <i class="fa-solid fa-pen-to-square"></i>
  </a>

  <button class="btn-icon delete"
          onclick="confirmHapus(<?= $r['id'] ?>)"
          title="Hapus">
      <i class="fa-solid fa-trash"></i>
  </button>
</div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination" style="margin-top:1.25rem">
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
          <a href="?<?= http_build_query(['page'=>$i,'q'=>$search,'kategori'=>$catF]) ?>"
             class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Form hapus tersembunyi -->
<form id="hapusForm" method="POST" action="<?= BASE_URL ?>/process/recipe_process.php">
  <?= csrfInput() ?>
  <input type="hidden" name="action" value="hapus">
  <input type="hidden" name="recipe_id" id="hapusId">
</form>

<?php
$extraJs = '<script>
function confirmHapus(id) {
  if (confirm("Yakin ingin menghapus resep ini? Tindakan tidak bisa dibatalkan.")) {
    document.getElementById("hapusId").value = id;
    document.getElementById("hapusForm").submit();
  }
}
</script>';
include __DIR__ . '/includes/footer_admin.php';
?>
