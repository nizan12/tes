<?php
session_start();

// Koneksi database
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /public/login.php");
    exit;
}

// Role routing helper
function redirect_by_role($role) {
    switch ($role) {
        case 'admin': header("Location: /admin/index.php"); break;
        case 'notulis': header("Location: /notulis/index.php"); break;
        case 'peserta': header("Location: /peserta/index.php"); break;
        default: header("Location: /public/login.php");
    }
    exit;
}
?>
