<?php
include 'include/header.php';

$id = $_GET['id'] ?? 0;
$rapat_id = $_GET['rapat_id'] ?? 0;

// Validasi kepemilikan rapat
$stmt = $pdo->prepare("
    SELECT l.*, r.notulis_id 
    FROM lampiran l
    JOIN rapat r ON l.rapat_id = r.id
    WHERE l.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data || $data['notulis_id'] != $_SESSION['user_id']) {
    echo "<p>❌ Tidak diizinkan menghapus lampiran ini.</p>";
} else {
    $file_path = __DIR__ . "/../../../uploads/" . $data['path_file'];
    if (file_exists($file_path)) unlink($file_path);
    $pdo->prepare("DELETE FROM lampiran WHERE id = ?")->execute([$id]);
    echo "<p style='color:green;'>✅ Lampiran dihapus.</p>";
}

header("Refresh: 1; url=detail.php?id=" . $rapat_id);
include '../../include/footer.php';
?>
