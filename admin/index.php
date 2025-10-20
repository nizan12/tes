<?php
// admin/index.php
$page_title = 'Dashboard';
require_once __DIR__ . '/include/header.php';

// basic counts
$counts = [];
$tables = ['users','unit','role','rapat','notulen','tindak_lanjut','notifikasi'];
foreach ($tables as $t) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$t`");
    $counts[$t] = $stmt->fetchColumn();
}
?>
<h2>Dashboard</h2>
<p>Ringkasan data sistem:</p>
<table>
  <tr><th>Entity</th><th>Count</th></tr>
  <?php foreach($counts as $k=>$v): ?>
    <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars($v) ?></td></tr>
  <?php endforeach; ?>
</table>

<?php require __DIR__ . '/include/footer.php'; ?>
