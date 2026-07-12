<?php
/**
 * pages/resep.php — Halaman semua resep dengan filter & search
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = getDB();

// Parameter filter
$kategori   = sanitize($_GET['kategori']   ?? '');
$kesulitan  = sanitize($_GET['kesulitan']  ?? '');
$search     = sanitize($_GET['q']          ?? '');
$page       = max(1, (int)($_GET['page']   ?? 1));
$perPage    = 12;
$offset     = ($page - 1) * $perPage;

// Build WHERE
$where  = ["r.status = 'publish'"];
$params = [];
if ($kategori)  { $where[] = "c.slug = ?"; $params[] = $kategori; }
if ($kesulitan) { $where[] = "r.kesulitan = ?"; $params[] = $kesulitan; }
if ($search)    { $where[] = "(r.judul LIKE ? OR r.deskripsi LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereSql = 'WHERE ' . implode(' AND ', $where);

// Total count
$totalStmt = $db->prepare("SELECT COUNT(*) FROM recipes r LEFT JOIN categories c ON c.id=r.category_id $whereSql");
$totalStmt->execute($params);
$total   = $totalStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Resep
$stmtParams = array_merge($params, [$perPage, $offset]);
$stmt = $db->prepare("
    SELECT r.id, r.judul, r.slug, r.foto, r.waktu_memasak, r.porsi, r.kesulitan,
           c.nama AS kategori, c.slug AS cat_slug,
           ROUND(AVG(rt.nilai),1) AS avg_rating
    FROM recipes r
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN ratings rt   ON rt.recipe_id = r.id
    $whereSql
    GROUP BY r.id
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($stmtParams);
$recipes = $stmt->fetchAll();

// Semua kategori untuk filter
$allCats = $db->query("SELECT nama, slug FROM categories ORDER BY nama")->fetchAll();

$pageTitle = 'Resep Masakan';
include __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">

    <div class="page-header-pub" style="margin-bottom:2rem">
      <h1 style="font-size:2rem;margin-bottom:.4rem">Semua Resep 🍴</h1>
      <p style="color:var(--text-muted)">Ditemukan <strong><?= $total ?></strong> resep untukmu</p>
    </div>

    <!-- Search bar -->
    <form method="GET" style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
      <input type="text" name="q" value="<?= sanitize($search) ?>"
             placeholder="Cari resep..." class="form-control" style="max-width:320px">
      <select name="kesulitan" class="form-control" style="max-width:160px">
        <option value="">Semua Kesulitan</option>
        <option value="mudah"  <?= $kesulitan==='mudah'  ?'selected':'' ?>>Mudah</option>
        <option value="sedang" <?= $kesulitan==='sedang' ?'selected':'' ?>>Sedang</option>
        <option value="sulit"  <?= $kesulitan==='sulit'  ?'selected':'' ?>>Sulit</option>
      </select>
      <button type="submit" class="btn btn-primary">Cari</button>
      <?php if ($search || $kesulitan || $kategori): ?>
        <a href="<?= BASE_URL ?>/pages/resep.php" class="btn btn-outline">Reset</a>
      <?php endif; ?>
    </form>

    <!-- Filter Kategori Pill -->
    <div class="filter-pills">
      <a href="<?= BASE_URL ?>/pages/resep.php?<?= http_build_query(['q'=>$search,'kesulitan'=>$kesulitan]) ?>"
         class="pill <?= !$kategori?'active':'' ?>">Semua</a>
      <?php foreach ($allCats as $cat): ?>
        <a href="<?= BASE_URL ?>/pages/resep.php?<?= http_build_query(['kategori'=>$cat['slug'],'q'=>$search,'kesulitan'=>$kesulitan]) ?>"
           class="pill <?= $kategori===$cat['slug']?'active':'' ?>">
          <?= sanitize($cat['nama']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Grid Resep -->
    <?php if ($recipes): ?>
      <div class="grid-4">
        <?php foreach ($recipes as $r): ?>
          <div class="card recipe-card">
            <?php if (isLoggedIn()): ?>
              <button class="bookmark-btn" data-id="<?= $r['id'] ?>" aria-label="Bookmark">🔖</button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/pages/detail_resep.php?slug=<?= urlencode($r['slug']) ?>">
              <img class="card-img" loading="lazy"
                   src="<?= $r['foto'] ? UPLOAD_URL.'/recipes/'.sanitize($r['foto']) : BASE_URL.'/assets/images/recipes/placeholder.jpg' ?>"
                   alt="<?= sanitize($r['judul']) ?>">
            </a>
            <div class="card-body">
              <div class="card-category"><?= sanitize($r['kategori']) ?></div>
              <h3 class="card-title">
                <a href="<?= BASE_URL ?>/pages/detail_resep.php?slug=<?= urlencode($r['slug']) ?>">
                  <?= sanitize($r['judul']) ?>
                </a>
              </h3>
              <?php if ($r['avg_rating']): ?>
                <div style="margin-bottom:.4rem">
                  <?= renderStars($r['avg_rating']) ?>
                  <small style="color:var(--text-muted)">(<?= $r['avg_rating'] ?>)</small>
                </div>
              <?php endif; ?>
              <div class="card-meta">
                <span>⏱ <?= $r['waktu_memasak'] ?> Mnt</span>
                <span>👥 <?= $r['porsi'] ?> Porsi</span>
                <span class="badge badge-<?= $r['kesulitan']==='mudah'?'success':($r['kesulitan']==='sedang'?'warning':'danger') ?>">
                  <?= ucfirst($r['kesulitan']) ?>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?<?= http_build_query(['page'=>$i,'q'=>$search,'kategori'=>$kategori,'kesulitan'=>$kesulitan]) ?>"
               class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Resep Tidak Ditemukan</h3>
        <p>Coba ubah kata kunci atau filter pencarian kamu.</p>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
