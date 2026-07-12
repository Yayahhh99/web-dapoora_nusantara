<?php
/**
 * pages/detail_resep.php — Halaman detail resep
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db   = getDB();
$slug = sanitize($_GET['slug'] ?? '');

if (!$slug) { redirect('pages/resep.php'); }

// Ambil resep
$stmt = $db->prepare("
    SELECT r.*, c.nama AS kategori, c.slug AS cat_slug,
           a.nama AS admin_nama,
           ROUND(AVG(rt.nilai),1) AS avg_rating,
           COUNT(DISTINCT rt.id) AS total_rating,
           COUNT(DISTINCT b.id)  AS total_bookmark
    FROM recipes r
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN admins a     ON a.id = r.admin_id
    LEFT JOIN ratings rt   ON rt.recipe_id = r.id
    LEFT JOIN bookmarks b  ON b.recipe_id  = r.id
    WHERE r.slug = ? AND r.status = 'publish'
    GROUP BY r.id
");
$stmt->execute([$slug]);
$resep = $stmt->fetch();

if (!$resep) { include __DIR__ . '/404.php'; exit; }

// Bahan & langkah (JSON)
$bahan  = json_decode($resep['bahan'],  true) ?? [];
$langkah = json_decode($resep['langkah'], true) ?? [];

// Komentar approved
$comments = $db->prepare("
    SELECT cm.*, u.nama AS user_nama, u.foto_profil
    FROM comments cm JOIN users u ON u.id = cm.user_id
    WHERE cm.recipe_id = ? AND cm.status = 'approved'
    ORDER BY cm.created_at DESC
");
$comments->execute([$resep['id']]);
$comments = $comments->fetchAll();

// Rating user saat ini
$myRating = 0;
if (isLoggedIn()) {
    $rStmt = $db->prepare("SELECT nilai FROM ratings WHERE user_id=? AND recipe_id=?");
    $rStmt->execute([$_SESSION['user_id'], $resep['id']]);
    $myRating = (int)($rStmt->fetchColumn() ?: 0);
}

// Cek bookmark
$isBookmarked = false;
if (isLoggedIn()) {
    $bStmt = $db->prepare("SELECT id FROM bookmarks WHERE user_id=? AND recipe_id=?");
    $bStmt->execute([$_SESSION['user_id'], $resep['id']]);
    $isBookmarked = (bool)$bStmt->fetchColumn();
}

$pageTitle = sanitize($resep['judul']);
include __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">
    <div style="max-width:900px;margin:0 auto">

      <!-- Breadcrumb -->
      <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem">
        <a href="<?= BASE_URL ?>">Home</a> /
        <a href="<?= BASE_URL ?>/pages/resep.php">Resep</a> /
        <a href="<?= BASE_URL ?>/pages/resep.php?kategori=<?= urlencode($resep['cat_slug']) ?>">
          <?= sanitize($resep['kategori']) ?>
        </a> /
        <span><?= sanitize($resep['judul']) ?></span>
      </div>

      <!-- Foto -->
      <img class="recipe-detail-img"
           src="<?= $resep['foto'] ? UPLOAD_URL.'/recipes/'.sanitize($resep['foto']) : BASE_URL.'/assets/images/recipes/placeholder.jpg' ?>"
           alt="<?= sanitize($resep['judul']) ?>" loading="lazy">

      <!-- Header Resep -->
      <div style="margin-top:1.75rem;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem">
        <div>
          <span class="badge badge-primary" style="margin-bottom:.6rem"><?= sanitize($resep['kategori']) ?></span>
          <h1 style="font-size:2rem;margin-bottom:.5rem"><?= sanitize($resep['judul']) ?></h1>
          <?php if ($resep['avg_rating']): ?>
            <div style="display:flex;align-items:center;gap:.5rem">
              <?= renderStars($resep['avg_rating']) ?>
              <span style="font-weight:700"><?= $resep['avg_rating'] ?></span>
              <span style="color:var(--text-muted);font-size:.85rem">(<?= $resep['total_rating'] ?> penilaian)</span>
            </div>
          <?php endif; ?>
        </div>
        <?php if (isLoggedIn()): ?>
          <button id="bookmarkBtn" class="btn <?= $isBookmarked?'btn-primary':'btn-outline' ?>"
                  data-id="<?= $resep['id'] ?>">
            <?= $isBookmarked ? '🔖 Tersimpan' : '🔖 Simpan Resep' ?>
          </button>
        <?php endif; ?>
      </div>

      <!-- Meta Info -->
      <div class="recipe-meta-row">
        <div class="meta-item"><span class="meta-icon">⏱</span> Waktu: <span><?= $resep['waktu_memasak'] ?> Menit</span></div>
        <div class="meta-item"><span class="meta-icon">👥</span> Porsi: <span><?= $resep['porsi'] ?></span></div>
        <div class="meta-item">
          <span class="meta-icon">📊</span> Kesulitan:
          <span class="badge badge-<?= $resep['kesulitan']==='mudah'?'success':($resep['kesulitan']==='sedang'?'warning':'danger') ?>">
            <?= ucfirst($resep['kesulitan']) ?>
          </span>
        </div>
        <div class="meta-item"><span class="meta-icon">🔖</span> Disimpan: <span><?= $resep['total_bookmark'] ?> kali</span></div>
      </div>

      <!-- Deskripsi -->
      <p style="line-height:1.8;margin-bottom:2rem;color:var(--text)"><?= nl2br(sanitize($resep['deskripsi'])) ?></p>

      <div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem">
        <!-- Bahan -->
        <div>
          <h2 style="font-size:1.3rem;margin-bottom:1rem">🧂 Bahan-Bahan</h2>
          <ul class="ingredients-list">
            <?php foreach ($bahan as $b): ?>
              <li><?= sanitize($b) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Langkah -->
        <div>
          <h2 style="font-size:1.3rem;margin-bottom:1rem">👨‍🍳 Langkah Memasak</h2>
          <?php foreach ($langkah as $i => $step): ?>
            <div class="step-item">
              <div class="step-num"><?= $i+1 ?></div>
              <div class="step-text"><?= sanitize($step) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Rating -->
      <?php if (isLoggedIn()): ?>
        <div style="background:var(--primary-pale);border-radius:var(--radius-lg);padding:1.5rem;margin:2rem 0">
          <h3 style="margin-bottom:1rem">⭐ Beri Rating</h3>
          <form id="ratingForm" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <input type="hidden" name="recipe_id" value="<?= $resep['id'] ?>">
            <div class="rating-input">
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" id="star<?= $i ?>" name="nilai" value="<?= $i ?>"
                       <?= $myRating===$i?'checked':'' ?>>
                <label for="star<?= $i ?>">★</label>
              <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Kirim Rating</button>
          </form>
        </div>
      <?php endif; ?>

      <!-- Komentar -->
      <div style="margin-top:2rem">
        <h2 style="font-size:1.3rem;margin-bottom:1.25rem">
          💬 Komentar (<?= count($comments) ?>)
        </h2>

        <?php if (isLoggedIn()): ?>
          <form method="POST" action="<?= BASE_URL ?>/process/comment_process.php"
                style="background:var(--gray-50);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem">
            <?= csrfInput() ?>
            <input type="hidden" name="action"    value="tambah">
            <input type="hidden" name="recipe_id" value="<?= $resep['id'] ?>">
            <div class="form-group">
              <label class="form-label">Tulis komentar kamu</label>
              <textarea name="komentar" class="form-control" rows="3"
                        placeholder="Bagikan pengalaman memasak kamu..." required maxlength="1000"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Kirim Komentar</button>
          </form>
        <?php else: ?>
          <div class="alert alert--info">
            <a href="<?= BASE_URL ?>/pages/login.php">Login</a> untuk memberikan komentar.
          </div>
        <?php endif; ?>

        <!-- Daftar komentar -->
        <?php foreach ($comments as $c): ?>
          <div class="comment-item">
            <img class="comment-avatar"
                 src="<?= $c['foto_profil'] ? UPLOAD_URL.'/profiles/'.sanitize($c['foto_profil']) : BASE_URL.'/assets/images/profiles/default.png' ?>"
                 alt="<?= sanitize($c['user_nama']) ?>" loading="lazy">
            <div class="comment-body">
              <div class="comment-header">
                <span class="comment-name"><?= sanitize($c['user_nama']) ?></span>
                <span class="comment-date"><?= formatTanggal($c['created_at']) ?></span>
              </div>
              <p style="font-size:.9rem;line-height:1.7"><?= nl2br(sanitize($c['komentar'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($comments)): ?>
          <p style="color:var(--text-muted);font-size:.9rem">Belum ada komentar. Jadilah yang pertama!</p>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<?php
$extraJs = '<script>
// Bookmark toggle
const bookmarkBtn = document.getElementById("bookmarkBtn");
if (bookmarkBtn) {
  bookmarkBtn.addEventListener("click", async () => {
    const res  = await fetch("' . BASE_URL . '/process/bookmark_process.php", {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify({recipe_id: bookmarkBtn.dataset.id})
    });
    const data = await res.json();
    if (data.status === "added") {
      bookmarkBtn.className = "btn btn-primary";
      bookmarkBtn.textContent = "🔖 Tersimpan";
    } else {
      bookmarkBtn.className = "btn btn-outline";
      bookmarkBtn.textContent = "🔖 Simpan Resep";
    }
  });
}
// Rating submit
const ratingForm = document.getElementById("ratingForm");
if (ratingForm) {
  ratingForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const fd  = new FormData(ratingForm);
    const res = await fetch("' . BASE_URL . '/process/rating_process.php", { method:"POST", body: fd });
    const data = await res.json();
    if (data.success) alert("Rating berhasil disimpan! ⭐");
  });
}
</script>';
include __DIR__ . '/../includes/footer.php';
?>
