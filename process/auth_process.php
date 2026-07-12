<?php
/**
 * process/auth_process.php
 * Handler POST login & register user
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('pages/login.php'); }
verifyCsrf();

$action = $_POST['action'] ?? '';

// ─────────────────────────────────────────────────────────────
// LOGIN
// ─────────────────────────────────────────────────────────────
if ($action === 'login') {
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Email dan password wajib diisi.');
        redirect('pages/login.php');
    }

    // Rate limiting
    if (isLoginBlocked($email)) {
        $sisa = ceil(loginLockoutRemaining($email) / 60);
        setFlash('error', "Terlalu banyak percobaan gagal. Coba lagi dalam {$sisa} menit.");
        redirect('pages/login.php');
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT id, nama, email, password, foto_profil, status FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        recordFailedLogin($email);
        setFlash('error', 'Email atau password salah.');
        $_SESSION['old_email'] = $email;
        redirect('pages/login.php');
    }

    if ($user['status'] === 'nonaktif') {
        setFlash('error', 'Akun kamu dinonaktifkan. Hubungi admin.');
        redirect('pages/login.php');
    }

    // Login berhasil
    resetLoginAttempts($email);
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_nama']  = $user['nama'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_foto']  = $user['foto_profil'];

    $intended = $_SESSION['intended_url'] ?? '';
    unset($_SESSION['intended_url']);

    setFlash('success', 'Selamat datang kembali, ' . $user['nama'] . '!');
    redirect($intended ?: BASE_URL);
}

// ─────────────────────────────────────────────────────────────
// REGISTER
// ─────────────────────────────────────────────────────────────
if ($action === 'register') {
    $nama      = sanitize($_POST['nama']      ?? '');
    $email     = sanitize($_POST['email']     ?? '');
    $password  = $_POST['password']  ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Validasi
    $errors = [];
    if (empty($nama))                          $errors[] = 'Nama wajib diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (strlen($password) < 8)                 $errors[] = 'Password minimal 8 karakter.';
    if ($password !== $konfirmasi)             $errors[] = 'Konfirmasi password tidak cocok.';

    if ($errors) {
        setFlash('error', implode(' ', $errors));
        $_SESSION['old_nama']  = $nama;
        $_SESSION['old_email'] = $email;
        redirect('pages/register.php');
    }

    $db = getDB();

    // Cek email sudah ada
    $cek = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $cek->execute([$email]);
    if ($cek->fetch()) {
        setFlash('error', 'Email sudah terdaftar. Silakan gunakan email lain.');
        $_SESSION['old_nama']  = $nama;
        $_SESSION['old_email'] = $email;
        redirect('pages/register.php');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $ins  = $db->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
    $ins->execute([$nama, $email, $hash]);
    $newId = $db->lastInsertId();

    // Auto login setelah register
    session_regenerate_id(true);
    $_SESSION['user_id']    = $newId;
    $_SESSION['user_nama']  = $nama;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_foto']  = null;

    setFlash('success', 'Akun berhasil dibuat! Selamat datang, ' . $nama . '!');
    redirect(BASE_URL);
}

redirect('pages/login.php');
