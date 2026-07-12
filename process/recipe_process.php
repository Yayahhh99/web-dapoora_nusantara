<?php
/**
 * process/recipe_process.php
 * CRUD resep (admin only)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/security.php';

requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/kelola_resep.php');
verifyCsrf();

$action = $_POST['action'] ?? '';
$db     = getDB();

// ─── Helper: parse textarea ke JSON array ────────────────────
function textareaToJson(string $text): string
{
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    return json_encode(array_values($lines), JSON_UNESCAPED_UNICODE);
}

// ─── TAMBAH ──────────────────────────────────────────────────
if ($action === 'tambah') {
    $judul      = sanitize($_POST['judul']        ?? '');
    $catId      = (int)($_POST['category_id']     ?? 0);
    $deskripsi  = sanitize($_POST['deskripsi']    ?? '');
    $bahan      = textareaToJson($_POST['bahan']  ?? '');
    $langkah    = textareaToJson($_POST['langkah'] ?? '');
    $waktu      = (int)($_POST['waktu_memasak']   ?? 30);
    $porsi      = (int)($_POST['porsi']           ?? 4);
    $kesulitan  = in_array($_POST['kesulitan']??'', ['mudah','sedang','sulit']) ? $_POST['kesulitan'] : 'mudah';
    $status     = in_array($_POST['status']??'',    ['publish','draft'])        ? $_POST['status']    : 'draft';

    if (!$judul || !$catId) {
        setFlash('error', 'Judul dan kategori wajib diisi.');
        redirect('admin/tambah_resep.php');
    }

    $slug = generateSlug($judul);
    // Pastikan slug unik
    $cek = $db->prepare("SELECT COUNT(*) FROM recipes WHERE slug=?");
    $cek->execute([$slug]);
    if ($cek->fetchColumn()) $slug .= '-' . time();

    $foto = uploadGambar($_FILES['foto'] ?? [], 'recipes') ?: null;

    $stmt = $db->prepare("INSERT INTO recipes (admin_id, category_id, judul, slug, foto, deskripsi, bahan, langkah, waktu_memasak, porsi, kesulitan, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$_SESSION['admin_id'], $catId, $judul, $slug, $foto, $deskripsi, $bahan, $langkah, $waktu, $porsi, $kesulitan, $status]);

    setFlash('success', 'Resep "' . $judul . '" berhasil ditambahkan!');
    redirect('admin/kelola_resep.php');
}

// ─── EDIT ────────────────────────────────────────────────────
if ($action === 'edit') {
    $recipeId   = (int)($_POST['recipe_id']       ?? 0);
    $judul      = sanitize($_POST['judul']        ?? '');
    $catId      = (int)($_POST['category_id']     ?? 0);
    $deskripsi  = sanitize($_POST['deskripsi']    ?? '');
    $bahan      = textareaToJson($_POST['bahan']  ?? '');
    $langkah    = textareaToJson($_POST['langkah'] ?? '');
    $waktu      = (int)($_POST['waktu_memasak']   ?? 30);
    $porsi      = (int)($_POST['porsi']           ?? 4);
    $kesulitan  = in_array($_POST['kesulitan']??'', ['mudah','sedang','sulit']) ? $_POST['kesulitan'] : 'mudah';
    $status     = in_array($_POST['status']??'',    ['publish','draft'])        ? $_POST['status']    : 'draft';

    if (!$recipeId || !$judul || !$catId) {
        setFlash('error', 'Data tidak lengkap.');
        redirect('admin/kelola_resep.php');
    }

    // Ambil foto lama
    $old = $db->prepare("SELECT foto, slug FROM recipes WHERE id=?");
    $old->execute([$recipeId]);
    $old = $old->fetch();

    $foto = $old['foto']; // default foto lama
    if (!empty($_FILES['foto']['name'])) {
        $newFoto = uploadGambar($_FILES['foto'], 'recipes');
        if ($newFoto) {
            hapusGambar($foto, 'recipes');
            $foto = $newFoto;
        }
    }

    // Update slug hanya jika judul berubah
    $slug = $old['slug'];
    if (generateSlug($judul) !== $slug) {
        $newSlug = generateSlug($judul);
        $cek = $db->prepare("SELECT COUNT(*) FROM recipes WHERE slug=? AND id!=?");
        $cek->execute([$newSlug, $recipeId]);
        $slug = $cek->fetchColumn() ? $newSlug . '-' . time() : $newSlug;
    }

    $stmt = $db->prepare("UPDATE recipes SET category_id=?,judul=?,slug=?,foto=?,deskripsi=?,bahan=?,langkah=?,waktu_memasak=?,porsi=?,kesulitan=?,status=? WHERE id=?");
    $stmt->execute([$catId, $judul, $slug, $foto, $deskripsi, $bahan, $langkah, $waktu, $porsi, $kesulitan, $status, $recipeId]);

    setFlash('success', 'Resep berhasil diperbarui!');
    redirect('admin/kelola_resep.php');
}

// ─── HAPUS ───────────────────────────────────────────────────
if ($action === 'hapus') {
    $recipeId = (int)($_POST['recipe_id'] ?? 0);
    if (!$recipeId) redirect('admin/kelola_resep.php');

    $old = $db->prepare("SELECT foto FROM recipes WHERE id=?");
    $old->execute([$recipeId]);
    $old = $old->fetch();

    hapusGambar($old['foto'] ?? '', 'recipes');

    $db->prepare("DELETE FROM recipes WHERE id=?")->execute([$recipeId]);

    setFlash('success', 'Resep berhasil dihapus.');
    redirect('admin/kelola_resep.php');
}

redirect('admin/kelola_resep.php');
