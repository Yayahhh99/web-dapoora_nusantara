<?php
/**
 * config/database.php
 * Koneksi database terpusat menggunakan PDO
 */

require_once __DIR__ . '/config.php';

// ─── Kredensial Database ────────────────────────────────────
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'dapoora_nsfix');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8mb4');

/**
 * Mengembalikan instance PDO (singleton).
 *
 * @return PDO
 * @throws RuntimeException jika koneksi gagal
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHAR
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Catat error ke log, jangan tampilkan detail ke user
            error_log('[Dapoora DB Error] ' . $e->getMessage());
            die('<p style="font-family:sans-serif;color:#c0392b;padding:2rem;">
                 ⚠️ Koneksi database gagal. Silakan hubungi administrator.</p>');
        }
    }

    return $pdo;
}
