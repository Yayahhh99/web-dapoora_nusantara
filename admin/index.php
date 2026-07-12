<?php
/**
 * admin/index.php — Dashboard Admin
 */
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db = getDB();

// ── Stat cards
$totalResep     = $db->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$totalPengguna  = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalKomentar  = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$totalBookmark  = $db->query("SELECT COUNT(*) FROM bookmarks")->fetchColumn();

// Pertumbuhan bulan ini
$thisMonth = date('Y-m');
$newResep   = $db->query("SELECT COUNT(*) FROM recipes  WHERE DATE_FORMAT(created_at,'%Y-%m')='$thisMonth'")->fetchColumn();
$newUser    = $db->query("SELECT COUNT(*) FROM users    WHERE DATE_FORMAT(created_at,'%Y-%m')='$thisMonth'")->fetchColumn();
$newComment = $db->query("SELECT COUNT(*) FROM comments WHERE DATE_FORMAT(created_at,'%Y-%m')='$thisMonth'")->fetchColumn();
$newBm      = $db->query("SELECT COUNT(*) FROM bookmarks WHERE DATE_FORMAT(created_at,'%Y-%m')='$thisMonth'")->fetchColumn();

// ── Statistik resep 6 bulan (untuk line chart)
$chartData = $db->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') AS bln, COUNT(*) AS total
    FROM recipes
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY created_at ASC
")->fetchAll();
$chartLabels = json_encode(array_column($chartData, 'bln'));
$chartValues = json_encode(array_map('intval', array_column($chartData, 'total')));

// ── Resep terpopuler
$popular = $db->query("
    SELECT r.judul, r.foto, ROUND(AVG(rt.nilai),1) AS avg_rating,
           COUNT(DISTINCT b.id) AS bm_count
    FROM recipes r
    LEFT JOIN ratings rt  ON rt.recipe_id = r.id
    LEFT JOIN bookmarks b ON b.recipe_id  = r.id
    WHERE r.status='publish'
    GROUP BY r.id ORDER BY bm_count DESC LIMIT 5
")->fetchAll();

// ── Aktivitas terbaru
$activities = $db->query("
    (SELECT 'recipe' AS type, CONCAT('Admin menambahkan resep baru \"',judul,'\"') AS deskripsi, created_at FROM recipes ORDER BY created_at DESC LIMIT 1)
    UNION
    (SELECT 'user', CONCAT('Pengguna baru \"',nama,'\" telah mendaftar'), created_at FROM users ORDER BY created_at DESC LIMIT 2)
    UNION
    (SELECT 'comment', CONCAT('Komentar baru pada resep'), created_at FROM comments ORDER BY created_at DESC LIMIT 2)
    UNION
    (SELECT 'bookmark', 'Pengguna mem-bookmark sebuah resep', created_at FROM bookmarks ORDER BY created_at DESC LIMIT 1)
    ORDER BY created_at DESC LIMIT 6
")->fetchAll();

// ── Distribusi kategori (donut chart)
$catDist = $db->query("
    SELECT c.nama, COUNT(r.id) AS total
    FROM categories c LEFT JOIN recipes r ON r.category_id=c.id AND r.status='publish'
    GROUP BY c.id HAVING total>0 ORDER BY total DESC
")->fetchAll();
$catLabels = json_encode(array_column($catDist,'nama'));
$catValues = json_encode(array_map('intval', array_column($catDist,'total')));

include __DIR__ . '/includes/header_admin.php';
?>

<!-- Page Header -->
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center">
  <div>
    <h1>Selamat datang, <?= sanitize($_SESSION['admin_nama']) ?>! 👋</h1>
    <p>Kelola konten, pengguna, dan aktivitas di <?= SITE_NAME ?>.</p>
  </div>
  <div style="display:flex;align-items:center;gap:.5rem;background:var(--white);padding:.6rem 1rem;border-radius:var(--radius);border:1px solid var(--gray-200);font-size:.875rem">
    📅 <?= formatTanggal(date('Y-m-d')) ?>
  </div>
</div>

<!-- Stat Cards -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon orange">📋</div>
    <div class="stat-info">
      <div class="stat-label">Total Resep</div>
      <div class="stat-value"><?= number_format($totalResep) ?></div>
      <div class="stat-change up">+<?= $newResep ?> resep baru bulan ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">👥</div>
    <div class="stat-info">
      <div class="stat-label">Total Pengguna</div>
      <div class="stat-value"><?= number_format($totalPengguna) ?></div>
      <div class="stat-change up">+<?= $newUser ?> pengguna baru bulan ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">💬</div>
    <div class="stat-info">
      <div class="stat-label">Total Komentar</div>
      <div class="stat-value"><?= number_format($totalKomentar) ?></div>
      <div class="stat-change up">+<?= $newComment ?> komentar bulan ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple">🔖</div>
    <div class="stat-info">
      <div class="stat-label">Total Bookmark</div>
      <div class="stat-value"><?= number_format($totalBookmark) ?></div>
      <div class="stat-change up">+<?= $newBm ?> bookmark bulan ini</div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="dash-grid-2">
  <!-- Line Chart -->
  <div class="widget">
    <div class="widget-header">
      <h3>📈 Statistik Resep (6 Bulan Terakhir)</h3>
    </div>
    <div class="widget-body">
      <div class="chart-container" style="height:260px">
        <canvas id="lineChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Resep Terpopuler -->
  <div class="widget">
    <div class="widget-header">
      <h3>🔥 Resep Terpopuler</h3>
      <a href="<?= BASE_URL ?>/admin/kelola_resep.php" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div class="widget-body">
      <div class="popular-list">
        <?php foreach ($popular as $p): ?>
          <div class="popular-item">
            <img src="<?= $p['foto'] ? UPLOAD_URL.'/recipes/'.sanitize($p['foto']) : BASE_URL.'/assets/images/recipes/placeholder.jpg' ?>"
                 alt="<?= sanitize($p['judul']) ?>">
            <div class="popular-info">
              <div class="popular-title"><?= sanitize($p['judul']) ?></div>
            </div>
            <div class="popular-stat">
              <div class="popular-rating">⭐ <?= $p['avg_rating'] ?: '–' ?></div>
              <div class="popular-bm">🔖 <?= $p['bm_count'] ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Activity + Donut -->
<div class="dash-grid-2b">
  <!-- Aktivitas Terbaru -->
  <div class="widget">
    <div class="widget-header"><h3>⚡ Aktivitas Terbaru</h3></div>
    <div class="widget-body">
      <div class="activity-list">
        <?php
        $actIcons = ['recipe'=>['🍳','#FEF3EA'],'user'=>['👤','#d4edda'],'comment'=>['💬','#d1ecf1'],'bookmark'=>['🔖','#e8d5f5']];
        foreach ($activities as $act):
          [$icon,$bg] = $actIcons[$act['type']] ?? ['📌','#eee'];
        ?>
          <div class="activity-item">
            <div class="activity-icon" style="background:<?= $bg ?>"><?= $icon ?></div>
            <div class="activity-text">
              <p><?= sanitize($act['deskripsi']) ?></p>
              <span class="time"><?= formatTanggal($act['created_at']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Donut Chart Kategori -->
  <div class="widget">
    <div class="widget-header">
      <h3>🥧 Kategori Resep</h3>
      <a href="<?= BASE_URL ?>/admin/kategori.php" class="btn btn-outline btn-sm">Kelola</a>
    </div>
    <div class="widget-body">
      <div class="chart-container" style="height:240px">
        <canvas id="donutChart"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = '<script>
const PRIMARY = "#E87B2E";
const COLORS  = ["#E87B2E","#2D8A4E","#2471A3","#9B59B6","#D4900A","#C0392B","#1ABC9C","#E74C3C"];

// Line chart
new Chart(document.getElementById("lineChart"), {
  type: "line",
  data: {
    labels: ' . $chartLabels . ',
    datasets: [{
      label: "Resep",
      data: ' . $chartValues . ',
      borderColor: PRIMARY,
      backgroundColor: "rgba(232,123,46,.1)",
      borderWidth: 2.5,
      pointRadius: 5,
      pointBackgroundColor: PRIMARY,
      tension: 0.4,
      fill: true
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: "rgba(0,0,0,.06)" } },
      x: { grid: { display: false } }
    }
  }
});

// Donut chart
new Chart(document.getElementById("donutChart"), {
  type: "doughnut",
  data: {
    labels: ' . $catLabels . ',
    datasets: [{
      data: ' . $catValues . ',
      backgroundColor: COLORS,
      borderWidth: 2,
      borderColor: "#fff"
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { position: "right", labels: { font: { size: 12 }, boxWidth: 14, padding: 12 } }
    },
    cutout: "60%"
  }
});
</script>';

include __DIR__ . '/includes/footer_admin.php';
?>
