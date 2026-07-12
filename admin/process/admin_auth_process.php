<?php
/**
 * admin/process/admin_auth_process.php
 * Handler POST login admin
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('admin/login.php'); }
verifyCsrf();

$email    = sanitize($_POST['email']    ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    setFlash('error', 'Email dan password wajib diisi.');
    redirect('admin/login.php');
}

if (isLoginBlocked('admin_' . $email)) {
    $sisa = ceil(loginLockoutRemaining('admin_' . $email) / 60);
    setFlash('error', "Terlalu banyak percobaan. Coba lagi dalam {$sisa} menit.");
    redirect('admin/login.php');
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, nama, email, password FROM admins WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    recordFailedLogin('admin_' . $email);
    setFlash('error', 'Email atau password admin salah.');
    $_SESSION['old_admin_email'] = $email;
    redirect('admin/login.php');
}

resetLoginAttempts('admin_' . $email);
session_regenerate_id(true);
$_SESSION['admin_id']    = $admin['id'];
$_SESSION['admin_nama']  = $admin['nama'];
$_SESSION['admin_email'] = $admin['email'];

setFlash('success', 'Selamat datang, ' . $admin['nama'] . '!');
header('Location: ' . BASE_URL . '/admin/index.php');
exit;

// ─────────────────────────────────────────────────────────────
// EDIT PROFIL ADMIN
// ─────────────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'edit_profil') {
    requireAdmin();
    $nama  = sanitize($_POST['nama']  ?? '');
    $email = sanitize($_POST['email'] ?? '');
    if (!$nama || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Data tidak valid.');
        redirect('admin/profil_admin.php');
    }
    $db = getDB();
    $db->prepare("UPDATE admins SET nama=?, email=? WHERE id=?")->execute([$nama, $email, $_SESSION['admin_id']]);
    $_SESSION['admin_nama']  = $nama;
    $_SESSION['admin_email'] = $email;
    setFlash('success', 'Profil admin diperbarui!');
    redirect('admin/profil_admin.php');
}

// ─────────────────────────────────────────────────────────────
// GANTI PASSWORD ADMIN
// ─────────────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'ganti_password_admin') {
    requireAdmin();
    $db   = getDB();
    $lama = $_POST['password_lama'] ?? '';
    $baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    $admin = $db->prepare("SELECT password FROM admins WHERE id=?");
    $admin->execute([$_SESSION['admin_id']]);
    $admin = $admin->fetch();

    if (!password_verify($lama, $admin['password'])) {
        setFlash('error', 'Password lama salah.');
        redirect('admin/profil_admin.php');
    }
    if (strlen($baru) < 8 || $baru !== $konfirmasi) {
        setFlash('error', 'Password baru tidak valid atau tidak cocok.');
        redirect('admin/profil_admin.php');
    }
    $db->prepare("UPDATE admins SET password=? WHERE id=?")->execute([password_hash($baru, PASSWORD_BCRYPT), $_SESSION['admin_id']]);
    setFlash('success', 'Password admin berhasil diubah!');
    redirect('admin/profil_admin.php');
}
