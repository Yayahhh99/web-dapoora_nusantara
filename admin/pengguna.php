<?php
$pageTitle = 'Daftar Pengguna';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$db = getDB();
$search = sanitize($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['page'] ?? 1));
$perPage= 15; $offset=($page-1)*$perPage;
$where  = $search ? "WHERE nama LIKE ? OR email LIKE ?" : '';
$params = $search ? ["%$search%","%$search%"] : [];

$total = $db->prepare("SELECT COUNT(*) FROM users $where");
$total->execute($params); $total = $total->fetchColumn();
$totalPages = max(1,ceil($total/$perPage));

$stmt = $db->prepare("SELECT * FROM users $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params,[$perPage,$offset]));
$users = $stmt->fetchAll();
include __DIR__ . '/includes/header_admin.php';
?>
<div class="page-header"><h1>Daftar Pengguna</h1></div>

<div class="widget">
  <div class="widget-body">
    <div class="table-toolbar">
      <form method="GET" style="display:flex;gap:.75rem">
        <div class="search-box"><span class="s-icon">🔍</span>
          <input type="text" name="q" placeholder="Cari nama/email..." value="<?= sanitize($search) ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <a href="<?= BASE_URL ?>/admin/pengguna.php" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>

    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Foto</th><th>Nama</th><th>Email</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <img class="table-avatar"
                     src="<?= $u['foto_profil'] ? UPLOAD_URL.'/profiles/'.sanitize($u['foto_profil']) : BASE_URL.'/assets/images/profiles/default.png' ?>"
                     alt="">
              </td>
              <td style="font-weight:600"><?= sanitize($u['nama']) ?></td>
              <td><?= sanitize($u['email']) ?></td>
              <td>
                <button class="badge badge-<?= $u['status']==='aktif'?'success':'gray' ?> toggle-status"
                        data-id="<?= $u['id'] ?>" data-status="<?= $u['status'] ?>"
                        style="border:none;cursor:pointer">
                  <?= ucfirst($u['status']) ?>
                </button>
              </td>
              <td><?= formatTanggal($u['created_at']) ?></td>
              <td>
                <div class="action-btns">
                  <button class="btn-icon delete"
                          onclick="hapusUser(<?= $u['id'] ?>)"
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
    <?php if ($totalPages>1): ?>
      <div class="pagination" style="margin-top:1rem">
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
          <a href="?<?= http_build_query(['page'=>$i,'q'=>$search]) ?>"
             class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<form id="hapusUserForm" method="POST" action="<?= BASE_URL ?>/process/user_process.php">
  <?= csrfInput() ?>
  <input type="hidden" name="action"  value="hapus_user">
  <input type="hidden" name="user_id" id="hapusUserId">
</form>

<?php
$extraJs = '<script>
function hapusUser(id){
  if(confirm("Hapus akun pengguna ini? Semua data terkait akan ikut terhapus.")){
    document.getElementById("hapusUserId").value=id;
    document.getElementById("hapusUserForm").submit();
  }
}
document.querySelectorAll(".toggle-status").forEach(btn => {
  btn.addEventListener("click", async () => {
    const res  = await fetch("' . BASE_URL . '/process/user_process.php", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({action:"toggle_status",user_id:btn.dataset.id})
    });
    const data = await res.json();
    if(data.success){
      btn.dataset.status = data.new_status;
      btn.textContent    = data.new_status.charAt(0).toUpperCase()+data.new_status.slice(1);
      btn.className = "badge badge-"+(data.new_status==="aktif"?"success":"gray")+" toggle-status";
    }
  });
});
</script>';
include __DIR__ . '/includes/footer_admin.php';
?>
