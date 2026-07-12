<?php
/**
 * pages/logout.php
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

// Hapus data user dari session
unset($_SESSION['user_id'], $_SESSION['user_nama'], $_SESSION['user_email'], $_SESSION['user_foto']);
session_regenerate_id(true);

setFlash('success', 'Kamu telah berhasil keluar. Sampai jumpa!');
redirect('pages/login.php');
