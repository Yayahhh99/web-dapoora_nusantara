<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$db = getDB();

// Kategori populer (dengan jumlah resep)
$categories = $db->query("
    SELECT c.id, c.nama, c.slug, c.icon,
           COUNT(r.id) AS total_resep
    FROM categories c
    LEFT JOIN recipes r ON r.category_id = c.id AND r.status='publish'
    GROUP BY c.id ORDER BY total_resep DESC LIMIT 6
")->fetchAll();

// Rekomendasi resep (5 terbaru publish)
$recipes = $db->query("
    SELECT r.id, r.judul, r.slug, r.foto, r.waktu_memasak, r.porsi, r.kesulitan,
           c.nama AS kategori,
           ROUND(AVG(rt.nilai),1) AS avg_rating
    FROM recipes r
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN ratings rt   ON rt.recipe_id = r.id
    WHERE r.status = 'publish'
    GROUP BY r.id
    ORDER BY r.created_at DESC LIMIT 5
")->fetchAll();

$pageTitle = 'Beranda';
$pageDesc  = 'Temukan ribuan resep masakan khas Indonesia terbaik di Dapoora Nusantara.';

// Icon emoji per kategori
$catIcons = ['makanan-berat'=>'🍖','makanan-ringan'=>'🍢','minuman'=>'🥤','dessert'=>'🍮','tradisional'=>'🍛','sehat'=>'🥗','seafood'=>'🦐','vegetarian'=>'🥦'];

include __DIR__ . '/includes/header.php';
?>

<!-- ─── HERO ─────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-content">
      <span class="hero-badge"> Selamat Datang!</span>
      <h1 class="hero-title">
        Temukan Resep<br>
        <span>Masakan Khas</span><br>
        Indonesia
      </h1>
      <p class="hero-desc">
        Dapoora Nusantara hadir untuk membantu kamu menemukan resep terbaik, mudah, dan lezat setiap hari.
      </p>
      <div class="hero-cta">
        <a href="<?= BASE_URL ?>/pages/resep.php" class="btn btn-primary btn-lg">
           Jelajahi Resep
        </a>
        <a href="#cara-pakai" class="btn btn-outline btn-lg">
           Cara Menggunakan
        </a>
      </div>
    </div>
    <div class="hero-img-wrap">
      <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="Masakan Indonesia" loading="lazy">
    </div>
  </div>
</section>

<!-- ─── KATEGORI POPULER ──────────────────────────────────── -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div class="section-header">
      <h2>Kategori Populer</h2>
      <p>Pilih kategori favorit dan temukan resep terbaik untukmu</p>
    </div>

    <div class="grid-6" style="display:grid;grid-template-columns:repeat(6,1fr);gap:1.25rem">
      <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/pages/resep.php?kategori=<?= urlencode($cat['slug']) ?>"
           class="cat-card">
          <div class="cat-icon">
            <?= $catIcons[$cat['slug']] ?? '🍽️' ?>
          </div>
          <div class="cat-name"><?= sanitize($cat['nama']) ?></div>
          <div class="cat-count"><?= $cat['total_resep'] ?> Resep</div>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/pages/kategori.php" class="btn btn-outline">
        Lihat Semua Kategori →
      </a>
    </div>
  </div>
</section>

<!-- ─── REKOMENDASI RESEP ─────────────────────────────────── -->
<section class="section" style="background:var(--gray-50)">
  <div class="container">
    <div class="section-header">
      <h2>Rekomendasi Untuk Kamu</h2>
      <p>Resep pilihan berdasarkan favorit dan terbaru</p>
    </div>

    <div class="grid-5">
      <?php foreach ($recipes as $r): ?>
        <div class="card recipe-card">
          <?php if (isLoggedIn()): ?>
            <button class="bookmark-btn" data-id="<?= $r['id'] ?>" aria-label="Bookmark resep">🔖</button>
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
              <span>⏱ <?= $r['waktu_memasak'] ?> Menit</span>
              <span>👥 <?= $r['porsi'] ?> Porsi</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/pages/resep.php" class="btn btn-outline">
        Lihat Semua Resep →
      </a>
    </div>
  </div>
</section>

<!-- ─── AI CHAT WIDGET (CSS only, fungsional via JS) ───────── -->
<div id="aiWidget" style="position:fixed;bottom:2rem;right:2rem;z-index:800">
  <div id="aiBox" class="hidden" style="
       width:280px;background:var(--white);border-radius:var(--radius-lg);
       box-shadow:var(--shadow-xl);border:1.5px solid var(--gray-200);
       overflow:hidden;margin-bottom:.75rem;">
    <div style="background:var(--primary);padding:1rem;color:#fff;display:flex;gap:.6rem;align-items:center">
      <span style="font-size:1.4rem">🤖</span>
      <div>
        <div style="font-weight:700;font-size:.9rem">Asisten AI Dapoora</div>
        <div style="font-size:.75rem;opacity:.85">Tanya apa saja tentang resep!</div>
      </div>
    </div>
    <div style="padding:.75rem">
      <?php
      $suggestions = ['Resep masakan tanpa santan?','Ide menu makan malam simple','Makanan khas Jawa Timur'];
      foreach ($suggestions as $s): ?>
        <button onclick="document.getElementById('aiInput').value='<?= $s ?>'"
                style="display:block;width:100%;text-align:left;padding:.5rem .75rem;margin-bottom:.4rem;
                       border:1.5px solid var(--gray-200);border-radius:var(--radius);
                       background:var(--gray-50);font-size:.82rem;cursor:pointer;transition:all .2s">
          <?= $s ?>
        </button>
      <?php endforeach; ?>
      <div style="display:flex;gap:.4rem;margin-top:.5rem">
        <input id="aiInput" type="text" placeholder="Ketik pertanyaan..."
               style="flex:1;padding:.5rem .75rem;border:1.5px solid var(--gray-300);
                      border-radius:var(--radius-full);font-size:.82rem;outline:none">
        <button style="background:var(--primary);color:#fff;border:none;
                       border-radius:var(--radius-full);padding:.5rem .8rem;cursor:pointer">✈</button>
      </div>
    </div>
  </div>
  <button id="aiToggle" style="
       width:52px;height:52px;border-radius:50%;background:var(--primary);
       color:#fff;border:none;font-size:1.4rem;cursor:pointer;
       box-shadow:var(--shadow-lg);display:flex;align-items:center;justify-content:center;
       margin-left:auto;transition:transform .2s">
    🤖
  </button>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
