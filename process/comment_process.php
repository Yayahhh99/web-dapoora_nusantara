<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/security.php';

$isJson = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');

if ($isJson) {
    header('Content-Type: application/json');
    $input  = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'update_status') {
        requireAdmin_inline();
        $id     = (int)($input['comment_id'] ?? 0);
        $status = in_array($input['status']??'', ['approved','pending','spam']) ? $input['status'] : '';
        if (!$id || !$status) { echo json_encode(['success'=>false]); exit; }
        getDB()->prepare("UPDATE comments SET status=? WHERE id=?")->execute([$status,$id]);
        echo json_encode(['success'=>true]);
        exit;
    }
    echo json_encode(['success'=>false]);
    exit;
}

// Form POST
verifyCsrf();
$action = $_POST['action'] ?? '';
$db     = getDB();

if ($action === 'tambah') {
    if (!isLoggedIn()) { setFlash('error','Login dahulu.'); redirect('pages/login.php'); }
    $recipeId = (int)($_POST['recipe_id'] ?? 0);
    $komentar = sanitize($_POST['komentar'] ?? '');
    if (!$recipeId || empty($komentar)) { setFlash('error','Komentar tidak boleh kosong.'); redirect('pages/resep.php'); }
    $db->prepare("INSERT INTO comments (user_id,recipe_id,komentar) VALUES (?,?,?)")->execute([$_SESSION['user_id'],$recipeId,$komentar]);
    // Dapatkan slug resep untuk redirect
    $slug = $db->prepare("SELECT slug FROM recipes WHERE id=?"); $slug->execute([$recipeId]);
    $slug = $slug->fetchColumn();
    setFlash('success','Komentar berhasil dikirim! Menunggu persetujuan.');
    redirect("pages/detail_resep.php?slug=$slug");
}

if ($action === 'hapus') {
    $id = (int)($_POST['comment_id'] ?? 0);
    if (isAdminLoggedIn()) {
        $db->prepare("DELETE FROM comments WHERE id=?")->execute([$id]);
    } elseif (isLoggedIn()) {
        $db->prepare("DELETE FROM comments WHERE id=? AND user_id=?")->execute([$id,$_SESSION['user_id']]);
    }
    setFlash('success','Komentar dihapus.');
    redirect('admin/komentar.php');
}

function requireAdmin_inline() {
    if (!isAdminLoggedIn()) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
}

redirect('index.php');
