<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/security.php';

requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/kategori.php');
verifyCsrf();

$action = $_POST['action'] ?? '';
$db = getDB();

if ($action === 'tambah') {
    $nama = sanitize($_POST['nama'] ?? '');
    $desc = sanitize($_POST['deskripsi'] ?? '');
    if (!$nama) { setFlash('error','Nama kategori wajib diisi.'); redirect('admin/kategori.php'); }

    $slug = generateSlug($nama);
    $cek  = $db->prepare("SELECT COUNT(*) FROM categories WHERE slug=?");
    $cek->execute([$slug]);
    if ($cek->fetchColumn()) $slug .= '-'.time();

    $db->prepare("INSERT INTO categories (nama,slug,deskripsi) VALUES (?,?,?)")->execute([$nama,$slug,$desc]);
    setFlash('success','Kategori berhasil ditambahkan!');
    redirect('admin/kategori.php');
}

if ($action === 'edit') {
    $id   = (int)($_POST['category_id'] ?? 0);
    $nama = sanitize($_POST['nama'] ?? '');
    $desc = sanitize($_POST['deskripsi'] ?? '');
    if (!$id || !$nama) { setFlash('error','Data tidak lengkap.'); redirect('admin/kategori.php'); }

    $db->prepare("UPDATE categories SET nama=?,deskripsi=? WHERE id=?")->execute([$nama,$desc,$id]);
    setFlash('success','Kategori berhasil diperbarui!');
    redirect('admin/kategori.php');
}

if ($action === 'hapus') {
    $id = (int)($_POST['category_id'] ?? 0);
    // Cek ada resep tidak
    $cek = $db->prepare("SELECT COUNT(*) FROM recipes WHERE category_id=?");
    $cek->execute([$id]);
    if ($cek->fetchColumn() > 0) {
        setFlash('error','Kategori tidak bisa dihapus karena masih memiliki resep.');
        redirect('admin/kategori.php');
    }
    $db->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
    setFlash('success','Kategori berhasil dihapus.');
    redirect('admin/kategori.php');
}

redirect('admin/kategori.php');
