<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

unset($_SESSION['admin_id'], $_SESSION['admin_nama'], $_SESSION['admin_email']);
session_regenerate_id(true);

setFlash('success', 'Admin berhasil keluar.');
redirect('admin/login.php');
