<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success'=>false,'error'=>'Belum login']);
    exit;
}

$recipeId = (int)($_POST['recipe_id'] ?? 0);
$nilai    = (int)($_POST['nilai']     ?? 0);
$userId   = (int)$_SESSION['user_id'];

if ($recipeId < 1 || $nilai < 1 || $nilai > 5) {
    echo json_encode(['success'=>false,'error'=>'Data tidak valid']);
    exit;
}

$db  = getDB();
$cek = $db->prepare("SELECT id FROM ratings WHERE user_id=? AND recipe_id=?");
$cek->execute([$userId, $recipeId]);

if ($cek->fetch()) {
    $db->prepare("UPDATE ratings SET nilai=? WHERE user_id=? AND recipe_id=?")->execute([$nilai,$userId,$recipeId]);
} else {
    $db->prepare("INSERT INTO ratings (user_id,recipe_id,nilai) VALUES (?,?,?)")->execute([$userId,$recipeId,$nilai]);
}
echo json_encode(['success'=>true]);
