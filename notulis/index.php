<?php
$page_title = "Dashboard Notulis";
include 'include/header.php';

// Ambil data ringkasan rapat yang dibuat notulis
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM rapat WHERE notulis_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total = $stmt->fetchColumn();
?>

<h2>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h2>
<p>Total rapat yang kamu catat: <b><?= $total ?></b></p>

<?php include 'include/footer.php'; ?>
