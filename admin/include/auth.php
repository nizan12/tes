<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<h3>Akses ditolak: Anda bukan admin.</h3>";
    exit;
}
?>
