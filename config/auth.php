<?php
/**
 * config/auth.php
 * Guard functions – paksa redirect jika belum terautentikasi
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

/**
 * Paksa user login.
 * Jika belum login, simpan URL tujuan lalu redirect ke halaman login.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '';
        setFlash('warning', 'Silakan login terlebih dahulu.');
        redirect('pages/login.php');
    }
}

/**
 * Paksa admin login.
 * Jika belum login sebagai admin, redirect ke halaman login admin.
 */
function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        setFlash('warning', 'Anda harus login sebagai admin.');
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
