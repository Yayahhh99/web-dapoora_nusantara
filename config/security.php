<?php
/**
 * config/security.php
 * Fungsi keamanan: CSRF, security headers, rate limiting
 */

require_once __DIR__ . '/config.php';

// ─────────────────────────────────────────────────────────────
// SECURITY HEADERS
// ─────────────────────────────────────────────────────────────

/**
 * Kirim HTTP security headers standar.
 */
function setSecurityHeaders(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; "
         . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
         . "font-src 'self' https://fonts.gstatic.com; "
         . "img-src 'self' data: blob:;");
}

// ─────────────────────────────────────────────────────────────
// CSRF PROTECTION
// ─────────────────────────────────────────────────────────────

/**
 * Generate (atau ambil yang sudah ada) CSRF token.
 *
 * @return string Token 64 karakter hex
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF token dari form POST.
 *
 * @param  string $token  Nilai token dari $_POST
 * @return bool
 */
function validateCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Render hidden input CSRF untuk form.
 *
 * @return string HTML <input>
 */
function csrfInput(): string
{
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verifikasi CSRF atau hentikan eksekusi.
 * Panggil di awal setiap handler POST.
 */
function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        die('403 Forbidden – Invalid CSRF token.');
    }
}

// ─────────────────────────────────────────────────────────────
// RATE LIMITING LOGIN
// ─────────────────────────────────────────────────────────────

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 menit dalam detik

/**
 * Catat satu kali percobaan login gagal.
 *
 * @param string $identifier  Email atau IP
 */
function recordFailedLogin(string $identifier): void
{
    $key = 'login_attempts_' . md5($identifier);
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    $_SESSION[$key]['count']++;
    $_SESSION[$key]['last_attempt'] = time();
}

/**
 * Cek apakah identifier sedang diblokir karena terlalu banyak gagal.
 *
 * @param  string $identifier
 * @return bool   true = diblokir
 */
function isLoginBlocked(string $identifier): bool
{
    $key = 'login_attempts_' . md5($identifier);
    if (!isset($_SESSION[$key])) return false;

    $data = $_SESSION[$key];

    // Reset jika sudah lewat lockout time
    if (time() - ($data['last_attempt'] ?? 0) > LOGIN_LOCKOUT_TIME) {
        unset($_SESSION[$key]);
        return false;
    }

    return $data['count'] >= MAX_LOGIN_ATTEMPTS;
}

/**
 * Reset counter login gagal (dipanggil setelah login berhasil).
 *
 * @param string $identifier
 */
function resetLoginAttempts(string $identifier): void
{
    unset($_SESSION['login_attempts_' . md5($identifier)]);
}

/**
 * Sisa waktu lockout dalam detik.
 *
 * @param  string $identifier
 * @return int
 */
function loginLockoutRemaining(string $identifier): int
{
    $key = 'login_attempts_' . md5($identifier);
    if (!isset($_SESSION[$key])) return 0;
    $elapsed = time() - ($_SESSION[$key]['last_attempt'] ?? 0);
    return max(0, LOGIN_LOCKOUT_TIME - $elapsed);
}
