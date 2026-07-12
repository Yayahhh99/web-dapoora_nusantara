<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = getDB();
$cats = $db->query("
    SELECT c.*, COUNT(r.id) AS total_resep
    FROM categories c LEFT JOIN recipes r ON r.category_id=c.id AND r.status='publish'
    GROUP BY c.id ORDER BY total_resep DESC
")->fetchAll();

$catIcons = ['makanan-berat'=>'🍖','makanan-ringan'=>'🍢','minuman'=>'🥤','dessert'=>'🍮','tradisional'=>'🍛','sehat'=>'🥗','seafood'=>'🦐','vegetarian'=>'🥦'];
$pageTitle = 'Semua Kategori';
include __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <h1>Semua Kategori 🏷️</h1>
      <p>Temukan resep berdasarkan kategori favorit kamu</p>
    </div>
    <div class="grid-4">
      <?php foreach ($cats as $cat): ?>
        <a href="<?= BASE_URL ?>/pages/resep.php?kategori=<?= urlencode($cat['slug']) ?>" class="cat-card" style="padding:2rem 1.5rem">
          <div class="cat-icon" style="width:72px;height:72px;font-size:2.2rem">
            <?= $catIcons[$cat['slug']] ?? '🍽️' ?>
          </div>
          <div class="cat-name" style="font-size:1.05rem"><?= sanitize($cat['nama']) ?></div>
          <div class="cat-count"><?= $cat['total_resep'] ?> Resep</div>
          <?php if ($cat['deskripsi']): ?>
            <p style="font-size:.8rem;color:var(--text-muted);text-align:center;margin-top:.5rem">
              <?= truncateText($cat['deskripsi'], 60) ?>
            </p>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
