<?php
$pageTitle = 'Statistik';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = getDB();

// Bar chart: resep per kategori
$catBar = $db->query("SELECT c.nama, COUNT(r.id) AS total FROM categories c LEFT JOIN recipes r ON r.category_id=c.id AND r.status='publish' GROUP BY c.id")->fetchAll();

// Line chart: user baru per bulan (6 bulan)
$userLine = $db->query("SELECT DATE_FORMAT(created_at,'%b %Y') AS bln, COUNT(*) AS total FROM users WHERE created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at")->fetchAll();

// Pie chart: kesulitan resep
$diffPie = $db->query("SELECT kesulitan, COUNT(*) AS total FROM recipes WHERE status='publish' GROUP BY kesulitan")->fetchAll();

// Top 10 resep terpopuler
$top10 = $db->query("SELECT r.judul, c.nama AS kategori, ROUND(AVG(rt.nilai),1) AS avg_rating, COUNT(DISTINCT b.id) AS bm, COUNT(DISTINCT cm.id) AS kom FROM recipes r LEFT JOIN categories c ON c.id=r.category_id LEFT JOIN ratings rt ON rt.recipe_id=r.id LEFT JOIN bookmarks b ON b.recipe_id=r.id LEFT JOIN comments cm ON cm.recipe_id=r.id WHERE r.status='publish' GROUP BY r.id ORDER BY bm DESC LIMIT 10")->fetchAll();

$catBarLabels = json_encode(array_column($catBar,'nama'));
$catBarValues = json_encode(array_map('intval',array_column($catBar,'total')));
$userLineLabels = json_encode(array_column($userLine,'bln'));
$userLineValues = json_encode(array_map('intval',array_column($userLine,'total')));
$diffLabels  = json_encode(array_map('ucfirst',array_column($diffPie,'kesulitan')));
$diffValues  = json_encode(array_map('intval',array_column($diffPie,'total')));

include __DIR__ . '/includes/header_admin.php';
?>
<div class="page-header"><h1>Statistik</h1><p>Analitik performa konten Dapoora Nusantara</p></div>

<div class="dash-grid-2b" style="margin-bottom:1.5rem">
  <div class="widget">
    <div class="widget-header"><h3>📊 Resep per Kategori</h3></div>
    <div class="widget-body"><div class="chart-container" style="height:280px"><canvas id="catBarChart"></canvas></div></div>
  </div>
  <div class="widget">
    <div class="widget-header"><h3>👤 Pertumbuhan Pengguna</h3></div>
    <div class="widget-body"><div class="chart-container" style="height:280px"><canvas id="userLineChart"></canvas></div></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem">
  <div class="widget">
    <div class="widget-header"><h3>🥧 Tingkat Kesulitan</h3></div>
    <div class="widget-body"><div class="chart-container" style="height:260px"><canvas id="diffPieChart"></canvas></div></div>
  </div>

  <div class="widget">
    <div class="widget-header"><h3>🔥 Top 10 Resep Terpopuler</h3></div>
    <div class="widget-body">
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>#</th><th>Judul</th><th>Kategori</th><th>Rating</th><th>Bookmark</th><th>Komentar</th></tr></thead>
          <tbody>
            <?php foreach ($top10 as $i=>$r): ?>
              <tr>
                <td style="font-weight:700;color:var(--primary)"><?= $i+1 ?></td>
                <td style="font-weight:600"><?= sanitize($r['judul']) ?></td>
                <td><?= sanitize($r['kategori']) ?></td>
                <td>⭐ <?= $r['avg_rating'] ?: '–' ?></td>
                <td>🔖 <?= $r['bm'] ?></td>
                <td>💬 <?= $r['kom'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = '<script>
const COLORS=["#E87B2E","#2D8A4E","#2471A3","#9B59B6","#D4900A","#C0392B","#1ABC9C","#E74C3C"];

new Chart(document.getElementById("catBarChart"),{
  type:"bar",
  data:{labels:'.$catBarLabels.',datasets:[{label:"Jumlah Resep",data:'.$catBarValues.',backgroundColor:"#E87B2E",borderRadius:6}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}
});

new Chart(document.getElementById("userLineChart"),{
  type:"line",
  data:{labels:'.$userLineLabels.',datasets:[{label:"Pengguna Baru",data:'.$userLineValues.',borderColor:"#2D8A4E",backgroundColor:"rgba(45,138,78,.1)",borderWidth:2.5,pointRadius:5,tension:.4,fill:true}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}
});

new Chart(document.getElementById("diffPieChart"),{
  type:"pie",
  data:{labels:'.$diffLabels.',datasets:[{data:'.$diffValues.',backgroundColor:["#2D8A4E","#D4900A","#C0392B"],borderWidth:2,borderColor:"#fff"}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:"bottom"}}}
});
</script>';
include __DIR__ . '/includes/footer_admin.php';
?>
