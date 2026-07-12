<?php
/**
 * process/bookmark_process.php — Toggle bookmark, JSON response
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Belum login']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$recipeId = (int)($input['recipe_id'] ?? 0);
$userId   = (int)$_SESSION['user_id'];

if (!$recipeId) {
    echo json_encode(['error' => 'ID resep tidak valid']);
    exit;
}

$db  = getDB();
$cek = $db->prepare("SELECT id FROM bookmarks WHERE user_id=? AND recipe_id=?");
$cek->execute([$userId, $recipeId]);
$exist = $cek->fetch();

if ($exist) {
    $db->prepare("DELETE FROM bookmarks WHERE user_id=? AND recipe_id=?")->execute([$userId, $recipeId]);
    echo json_encode(['status' => 'removed']);
} else {
    $db->prepare("INSERT INTO bookmarks (user_id, recipe_id) VALUES (?,?)")->execute([$userId, $recipeId]);
    echo json_encode(['status' => 'added']);
}
