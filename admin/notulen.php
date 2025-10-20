<?php
// admin/notulen.php
$page_title = 'Notulen';
require_once __DIR__ . '/include/header.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0); if ($id) $pdo->prepare("DELETE FROM notulen WHERE id=?")->execute([$id]);
    header('Location: notulen.php'); exit;
}
if ($action === 'show') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT n.*, r.judul as rapat_judul, u.nama as notulis_name FROM notulen n LEFT JOIN rapat r ON n.rapat_id=r.id LEFT JOIN users u ON n.notulis_id=u.id WHERE n.id=? LIMIT 1");
    $stmt->execute([$id]); $row = $stmt->fetch();
    if (!$row) { echo "<p>Notulen tidak ditemukan</p>"; require __DIR__ . '/include/footer.php'; exit; }
    ?>
    <h2>Notulen — <?= htmlspecialchars($row['rapat_judul']) ?></h2>
    <p><strong>Notulis:</strong> <?= htmlspecialchars($row['notulis_name']) ?></p>
    <h3>Ringkasan</h3>
    <div><?= nl2br(htmlspecialchars($row['ringkasan'])) ?></div>
    <h3>Keputusan</h3>
    <div><?= nl2br(htmlspecialchars($row['keputusan'])) ?></div>
    <h3>Catatan</h3>
    <div><?= nl2br(htmlspecialchars($row['catatan'])) ?></div>
    <p><a href="notulen.php">Kembali</a></p>
    <?php require __DIR__ . '/include/footer.php'; exit;
}
$rows = $pdo->query("SELECT n.*, r.judul as rapat_judul, u.nama as notulis_name FROM notulen n LEFT JOIN rapat r ON n.rapat_id=r.id LEFT JOIN users u ON n.notulis_id=u.id ORDER BY n.created_at DESC")->fetchAll();
?>
<h2>Notulen</h2>
<table>
  <tr><th>ID</th><th>Rapat</th><th>Notulis</th><th>Tgl</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['id']) ?></td>
      <td><?= htmlspecialchars($r['rapat_judul']) ?></td>
      <td><?= htmlspecialchars($r['notulis_name']) ?></td>
      <td><?= htmlspecialchars($r['created_at']) ?></td>
      <td><a href="notulen.php?action=show&id=<?= $r['id'] ?>">Show</a> | <a href="notulen.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/include/footer.php'; ?>
