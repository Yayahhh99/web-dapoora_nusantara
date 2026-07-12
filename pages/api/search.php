<?php
/**
 * pages/api/search.php
 * Endpoint search resep — response JSON
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

// Hanya GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$q = sanitize($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

// Rate limiting sederhana via session
if (!isset($_SESSION['search_count'])) {
    $_SESSION['search_count']    = 0;
    $_SESSION['search_window']   = time();
}
if (time() - $_SESSION['search_window'] > 60) {
    $_SESSION['search_count']  = 0;
    $_SESSION['search_window'] = time();
}
$_SESSION['search_count']++;
if ($_SESSION['search_count'] > 60) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("
    SELECT r.id, r.judul, r.slug, r.foto,
           c.nama AS kategori
    FROM recipes r
    LEFT JOIN categories c ON c.id = r.category_id
    WHERE r.status = 'publish'
      AND (r.judul LIKE ? OR r.deskripsi LIKE ? OR c.nama LIKE ?)
    ORDER BY r.judul ASC
    LIMIT 8
");

$like = '%' . $q . '%';
$stmt->execute([$like, $like, $like]);
$rows = $stmt->fetchAll();

$results = array_map(fn($r) => [
    'id'      => $r['id'],
    'judul'   => $r['judul'],
    'slug'    => $r['slug'],
    'kategori'=> $r['kategori'] ?? '',
    'foto'    => $r['foto']
                  ? UPLOAD_URL . '/recipes/' . $r['foto']
                  : BASE_URL   . '/assets/images/recipes/placeholder.jpg',
    'url'     => BASE_URL . '/pages/detail_resep.php?slug=' . urlencode($r['slug']),
], $rows);

echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
