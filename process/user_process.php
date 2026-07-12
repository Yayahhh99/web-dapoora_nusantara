<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/auth.php';

$isJson = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');

if ($isJson) {
    header('Content-Type: application/json');
    requireAdmin();
    $input  = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'toggle_status') {
        $id  = (int)($input['user_id'] ?? 0);
        $db  = getDB();
        $cur = $db->prepare("SELECT status FROM users WHERE id=?");
        $cur->execute([$id]);
        $cur = $cur->fetchColumn();
        $new = $cur === 'aktif' ? 'nonaktif' : 'aktif';
        $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new,$id]);
        echo json_encode(['success'=>true,'new_status'=>$new]);
        exit;
    }
    echo json_encode(['success'=>false]);
    exit;
}

// Form POST
verifyCsrf();
$action = $_POST['action'] ?? '';
$db     = getDB();

if ($action === 'edit_profil') {
    requireLogin();
    $nama = sanitize($_POST['nama'] ?? '');
    if (!$nama) { setFlash('error','Nama wajib diisi.'); redirect('pages/profil.php'); }

    $foto = null;
    if (!empty($_FILES['foto']['name'])) {
        $foto = uploadGambar($_FILES['foto'], 'profiles');
        if ($foto) {
            // Hapus foto lama
            $old = $db->prepare("SELECT foto_profil FROM users WHERE id=?");
            $old->execute([$_SESSION['user_id']]);
            $oldFoto = $old->fetchColumn();
            hapusGambar($oldFoto, 'profiles');
        }
    }

    if ($foto) {
        $db->prepare("UPDATE users SET nama=?,foto_profil=? WHERE id=?")->execute([$nama,$foto,$_SESSION['user_id']]);
        $_SESSION['user_foto'] = $foto;
    } else {
        $db->prepare("UPDATE users SET nama=? WHERE id=?")->execute([$nama,$_SESSION['user_id']]);
    }
    $_SESSION['user_nama'] = $nama;
    setFlash('success','Profil berhasil diperbarui!');
    redirect('pages/profil.php');
}

if ($action === 'ganti_password') {
    requireLogin();
    $lama      = $_POST['password_lama'] ?? '';
    $baru      = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi']    ?? '';

    $user = $db->prepare("SELECT password FROM users WHERE id=?");
    $user->execute([$_SESSION['user_id']]);
    $user = $user->fetch();

    if (!password_verify($lama, $user['password'])) {
        setFlash('error','Password lama salah.'); redirect('pages/profil.php');
    }
    if (strlen($baru) < 8) { setFlash('error','Password baru minimal 8 karakter.'); redirect('pages/profil.php'); }
    if ($baru !== $konfirmasi) { setFlash('error','Konfirmasi password tidak cocok.'); redirect('pages/profil.php'); }

    $hash = password_hash($baru, PASSWORD_BCRYPT);
    $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash,$_SESSION['user_id']]);
    setFlash('success','Password berhasil diubah!');
    redirect('pages/profil.php');
}

if ($action === 'hapus_user') {
    requireAdmin();
    $id = (int)($_POST['user_id'] ?? 0);
    if ($id) {
        // Hapus foto profil
        $u = $db->prepare("SELECT foto_profil FROM users WHERE id=?");
        $u->execute([$id]); $u=$u->fetch();
        hapusGambar($u['foto_profil'] ?? '', 'profiles');
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        setFlash('success','Akun pengguna berhasil dihapus.');
    }
    redirect('admin/pengguna.php');
}

redirect('index.php');
