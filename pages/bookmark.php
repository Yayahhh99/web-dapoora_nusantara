<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$db = getDB();
$bookmarks = $db->prepare("
    SELECT r.id, r.judul, r.slug, r.foto, r.waktu_memasak, r.porsi, r.kesulitan,
           c.nama AS kategori, ROUND(AVG(rt.nilai),1) AS avg_rating
    FROM bookmarks bk
    JOIN recipes r    ON r.id = bk.recipe_id
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN ratings rt   ON rt.recipe_id = r.id
    WHERE bk.user_id = ? AND r.status='publish'
    GROUP BY r.id
    ORDER BY bk.created_at DESC
");
$bookmarks->execute([$_SESSION['user_id']]);
$bookmarks = $bookmarks->fetchAll();

$pageTitle = 'Resep Tersimpan';
include __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div style="margin-bottom:2rem">
      <h1 style="font-size:1.75rem">🔖 Resep Tersimpan</h1>
      <p style="color:var(--text-muted)"><?= count($bookmarks) ?> resep tersimpan</p>
    </div>

    <?php if ($bookmarks): ?>
      <div class="grid-4">
        <?php foreach ($bookmarks as $r): ?>
          <div class="card recipe-card">
            <button class="bookmark-btn active" data-id="<?= $r['id'] ?>" aria-label="Hapus bookmark">🔖</button>
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
              <div class="card-meta">
                <span>⏱ <?= $r['waktu_memasak'] ?> Mnt</span>
                <span>👥 <?= $r['porsi'] ?> Porsi</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">🔖</div>
        <h3>Belum Ada Resep Tersimpan</h3>
        <p>Jelajahi resep dan simpan favoritmu!</p>
        <a href="<?= BASE_URL ?>/pages/resep.php" class="btn btn-primary mt-2">Jelajahi Resep</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
