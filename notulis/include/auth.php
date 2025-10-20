<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'notulis') {
    echo "<h3>Akses ditolak: Anda bukan notulis.</h3>";
    exit;
}
?>
