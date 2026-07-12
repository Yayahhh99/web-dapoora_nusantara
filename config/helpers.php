<?php
/**
 * config/helpers.php
 * Fungsi-fungsi pembantu global
 */

require_once __DIR__ . '/config.php';

// ─────────────────────────────────────────────────────────────
// NAVIGASI
// ─────────────────────────────────────────────────────────────

/**
 * Redirect ke URL tertentu.
 *
 * @param string $url URL tujuan (relatif atau absolut)
 */
function redirect(string $url): void
{
    if (!str_starts_with($url, 'http')) {
        $url = BASE_URL . '/' . ltrim($url, '/');
    }
    header('Location: ' . $url);
    exit;
}


// KEAMANAN & SANITASI

/**
 * Sanitasi input untuk mencegah XSS.
 *
 * @param  string|array $input
 * @return string|array
 */
function sanitize(mixed $input): mixed
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim((string)$input)), ENT_QUOTES, 'UTF-8');
}

// ─────────────────────────────────────────────────────────────
// SESSION / AUTENTIKASI
// ─────────────────────────────────────────────────────────────

/**
 * Cek apakah user (pengguna) sudah login.
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Cek apakah admin sudah login.
 *
 * @return bool
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// ─────────────────────────────────────────────────────────────
// FORMAT & TEKS
// ─────────────────────────────────────────────────────────────

/**
 * Format tanggal ke bahasa Indonesia.
 * Contoh: "20 Mei 2025"
 *
 * @param  string $date  String tanggal (Y-m-d atau datetime)
 * @return string
 */
function formatTanggal(string $date): string
{
    $bulan = [
        1=>'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Potong teks panjang dan tambahkan ellipsis.
 *
 * @param  string $text
 * @param  int    $length  Jumlah karakter maksimum
 * @return string
 */
function truncateText(string $text, int $length = 100): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Generate slug URL-friendly dari string.
 * Contoh: "Rendang Daging Sapi" → "rendang-daging-sapi"
 *
 * @param  string $string
 * @return string
 */
function generateSlug(string $string): string
{
    $string = mb_strtolower($string, 'UTF-8');

    // Karakter khusus Indonesia → ASCII
    $map = [
        'à'=>'a','á'=>'a','â'=>'a','ä'=>'a','å'=>'a','ã'=>'a',
        'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
        'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
        'ò'=>'o','ó'=>'o','ô'=>'o','ö'=>'o','õ'=>'o',
        'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
        'ñ'=>'n','ç'=>'c',
    ];
    $string = strtr($string, $map);
    $string = preg_replace('/[^a-z0-9\s\-]/', '', $string);
    $string = preg_replace('/[\s\-]+/', '-', $string);
    return trim($string, '-');
}

// ─────────────────────────────────────────────────────────────
// UPLOAD GAMBAR
// ─────────────────────────────────────────────────────────────

/**
 * Proses upload gambar ke server.
 *
 * @param  array  $file    Elemen dari $_FILES['nama_input']
 * @param  string $folder  Sub-folder di dalam uploads/ (misal: 'recipes')
 * @return string|false    Nama file jika sukses, false jika gagal
 */
function uploadGambar(array $file, string $folder = 'recipes'): string|false
{
    // Tidak ada file yang diunggah
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return false;

    // Error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        setFlash('error', 'Gagal mengunggah gambar (kode: ' . $file['error'] . ')');
        return false;
    }

    // Validasi ukuran
    if ($file['size'] > MAX_FILE_SIZE) {
        setFlash('error', 'Ukuran gambar maksimal 2 MB.');
        return false;
    }

    // Validasi tipe MIME secara nyata (bukan dari ekstensi saja)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_TYPES, true)) {
        setFlash('error', 'Format gambar harus JPG, PNG, atau WebP.');
        return false;
    }

    // Tentukan ekstensi
    $ext = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };

    $fileName  = uniqid('img_', true) . '.' . $ext;
    $targetDir = UPLOAD_PATH . '/' . $folder . '/';
    $targetPath = $targetDir . $fileName;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        setFlash('error', 'Gagal menyimpan gambar.');
        return false;
    }

    return $fileName;
}

/**
 * Hapus file gambar dari server.
 *
 * @param  string $fileName  Nama file
 * @param  string $folder    Sub-folder di uploads/
 */
function hapusGambar(string $fileName, string $folder = 'recipes'): void
{
    if (empty($fileName)) return;
    $path = UPLOAD_PATH . '/' . $folder . '/' . $fileName;
    if (file_exists($path)) {
        unlink($path);
    }
}

// ─────────────────────────────────────────────────────────────
// FLASH MESSAGES
// ─────────────────────────────────────────────────────────────

/**
 * Set flash message untuk ditampilkan satu kali.
 *
 * @param string $type  'success' | 'error' | 'warning' | 'info'
 * @param string $msg
 */
function setFlash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/**
 * Ambil dan hapus flash message.
 *
 * @return array|null  ['type'=>..., 'msg'=>...] atau null
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Tampilkan flash message sebagai HTML.
 */
function showFlash(): void
{
    $flash = getFlash();
    if (!$flash) return;

    $icons = [
        'success' => '✅',
        'error'   => '❌',
        'warning' => '⚠️',
        'info'    => 'ℹ️',
    ];
    $icon = $icons[$flash['type']] ?? 'ℹ️';

    echo '<div class="alert alert--' . htmlspecialchars($flash['type'], ENT_QUOTES) . '">'
       . $icon . ' ' . htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8')
       . '</div>';
}

// ─────────────────────────────────────────────────────────────
// RATING HELPER
// ─────────────────────────────────────────────────────────────

/**
 * Render bintang rating HTML.
 *
 * @param  float $rating  Nilai 0–5
 * @return string
 */
function renderStars(float $rating): string
{
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= round($rating) ? '★' : '☆';
    }
    $html .= '</span>';
    return $html;
}
