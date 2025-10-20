<?php
// admin/unit.php
$page_title = 'Units';
require_once __DIR__ . '/include/header.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) { $pdo->prepare("DELETE FROM unit WHERE id = ?")->execute([$id]); }
    header('Location: unit.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_unit'] ?? '');
    $keterangan = $_POST['keterangan'] ?? '';
    if ($id) {
        $pdo->prepare("UPDATE unit SET nama_unit=?, keterangan=? WHERE id=?")->execute([$nama,$keterangan,$id]);
    } else {
        $pdo->prepare("INSERT INTO unit (nama_unit,keterangan) VALUES (?,?)")->execute([$nama,$keterangan]);
    }
    header('Location: unit.php'); exit;
}
if ($action === 'edit' || $action === 'create') {
    $id = (int)($_GET['id'] ?? 0);
    $unit = $id ? $pdo->prepare("SELECT * FROM unit WHERE id=?")->execute([$id]) : null;
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM unit WHERE id=? LIMIT 1"); $stmt->execute([$id]); $unit = $stmt->fetch();
    }
    ?>
    <h2><?= $action === 'edit' ? 'Edit Unit' : 'Create Unit' ?></h2>
    <form method="post">
      <input type="hidden" name="id" value="<?= htmlspecialchars($unit['id'] ?? '') ?>">
      <label>Nama Unit<br><input type="text" name="nama_unit" value="<?= htmlspecialchars($unit['nama_unit'] ?? '') ?>" required></label><br>
      <label>Keterangan<br><textarea name="keterangan"><?= htmlspecialchars($unit['keterangan'] ?? '') ?></textarea></label><br>
      <button type="submit">Simpan</button><a href="unit.php">Batal</a>
    </form>
    <?php require __DIR__ . '/include/footer.php'; exit;
}
$rows = $pdo->query("SELECT * FROM unit ORDER BY id DESC")->fetchAll();
?>
<h2>Units</h2>
<p><a href="unit.php?action=create">+ Create Unit</a></p>
<table>
  <tr><th>ID</th><th>Nama Unit</th><th>Keterangan</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['id']) ?></td>
    <td><?= htmlspecialchars($r['nama_unit']) ?></td>
    <td><?= htmlspecialchars($r['keterangan']) ?></td>
    <td><a href="unit.php?action=edit&id=<?= $r['id'] ?>">Edit</a> | <a href="unit.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/include/footer.php'; ?>
