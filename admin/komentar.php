<?php
$pageTitle = 'Kelola Komentar';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db = getDB();
$filter = sanitize($_GET['status'] ?? '');
$page   = max(1,(int)($_GET['page'] ?? 1));
$perPage= 15; $offset=($page-1)*$perPage;

$where  = $filter ? "WHERE cm.status=?" : '';
$params = $filter ? [$filter] : [];
$total  = $db->prepare("SELECT COUNT(*) FROM comments cm $where");
$total->execute($params); $total=$total->fetchColumn();
$totalPages = max(1,ceil($total/$perPage));

$stmt = $db->prepare("
    SELECT cm.*, u.nama AS user_nama, r.judul AS resep_judul, r.slug AS resep_slug
    FROM comments cm
    JOIN users u  ON u.id=cm.user_id
    JOIN recipes r ON r.id=cm.recipe_id
    $where ORDER BY cm.created_at DESC LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($params,[$perPage,$offset]));
$comments = $stmt->fetchAll();
include __DIR__ . '/includes/header_admin.php';
?>
<div class="page-header"><h1>Kelola Komentar</h1></div>

<!-- Filter status -->
<div class="filter-pills" style="margin-bottom:1.25rem">
  <?php foreach ([''  =>'Semua','pending'=>'Pending','approved'=>'Approved','spam'=>'Spam'] as $v=>$l): ?>
    <a href="?status=<?= $v ?>" class="pill <?= $filter===$v?'active':'' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>

<div class="widget">
  <div class="widget-body">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Pengguna</th><th>Resep</th><th>Komentar</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($comments as $c): ?>
            <tr>
              <td style="font-weight:600;white-space:nowrap"><?= sanitize($c['user_nama']) ?></td>
              <td>
                <a href="<?= BASE_URL ?>/pages/detail_resep.php?slug=<?= urlencode($c['resep_slug']) ?>"
                   style="font-size:.85rem" target="_blank">
                  <?= truncateText(sanitize($c['resep_judul']),30) ?>
                </a>
              </td>
              <td style="max-width:260px;font-size:.875rem"><?= truncateText(sanitize($c['komentar']),80) ?></td>
              <td>
                <span class="badge badge-<?= $c['status']==='approved'?'success':($c['status']==='pending'?'warning':'danger') ?>">
                  <?= ucfirst($c['status']) ?>
                </span>
              </td>
              <td style="white-space:nowrap;font-size:.8rem"><?= formatTanggal($c['created_at']) ?></td>
              <td>
                <div class="action-btns">
                  <?php if ($c['status'] !== 'approved'): ?>
                    <button class="btn-icon approve update-komentar" data-id="<?= $c['id'] ?>" data-status="approved" title="Approve">✅</button>
                  <?php endif; ?>
                  <?php if ($c['status'] !== 'spam'): ?>
                    <button class="btn-icon spam update-komentar" data-id="<?= $c['id'] ?>" data-status="spam" title="Spam">⚠️</button>
                  <?php endif; ?>
                  <button class="btn-icon delete hapus-komentar" data-id="<?= $c['id'] ?>" title="Hapus">🗑️</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($totalPages>1): ?>
      <div class="pagination" style="margin-top:1rem">
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
          <a href="?<?= http_build_query(['page'=>$i,'status'=>$filter]) ?>"
             class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<form id="hapusKomForm" method="POST" action="<?= BASE_URL ?>/process/comment_process.php">
  <?= csrfInput() ?>
  <input type="hidden" name="action"     value="hapus">
  <input type="hidden" name="comment_id" id="hapusKomId">
</form>

<?php
$extraJs = '<script>
document.querySelectorAll(".update-komentar").forEach(btn => {
  btn.addEventListener("click", async () => {
    const res  = await fetch("' . BASE_URL . '/process/comment_process.php", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({action:"update_status", comment_id: btn.dataset.id, status: btn.dataset.status})
    });
    const data = await res.json();
    if(data.success) location.reload();
  });
});
document.querySelectorAll(".hapus-komentar").forEach(btn => {
  btn.addEventListener("click", () => {
    if(confirm("Hapus komentar ini?")){
      document.getElementById("hapusKomId").value=btn.dataset.id;
      document.getElementById("hapusKomForm").submit();
    }
  });
});
</script>';
include __DIR__ . '/includes/footer_admin.php';
?>
