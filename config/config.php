<?php
/**
 * config/config.php
 * Konfigurasi global aplikasi Dapoora Nusantara
 */

// ─── Timezone ───────────────────────────────────────────────
date_default_timezone_set('Asia/Jakarta');

// ─── Konstanta Utama ────────────────────────────────────────
define('SITE_NAME',    'Dapoora Nusantara');
define('SITE_TAGLINE', 'Cita Rasa, Cerita Kita');

// Deteksi otomatis BASE_URL (lebih andal)
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'];
$rootPath  = dirname(__DIR__); // path absolut root project
$docRoot   = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
$subFolder = str_replace($docRoot, '', $rootPath); // misal: /dapoora_nusantara

// ─── BASE_URL ────────────────────────────────────────────────
define('BASE_URL', 'http://localhost/dapoora_nusantara');

// Path absolut root project
define('ROOT_PATH',    dirname(__DIR__));

// Path upload (relatif terhadap root)
define('UPLOAD_PATH',  ROOT_PATH . '/uploads');
define('UPLOAD_URL',   BASE_URL  . '/uploads');

// Ukuran maksimum upload gambar: 2 MB
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

// Tipe MIME gambar yang diizinkan
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// ─── Session ────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
