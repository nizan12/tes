<?php
// admin/role.php
$page_title = 'Roles';
require_once __DIR__ . '/include/header.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) $pdo->prepare("DELETE FROM role WHERE id=?")->execute([$id]);
    header('Location: role.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama_role = trim($_POST['nama_role'] ?? '');
    if ($id) $pdo->prepare("UPDATE role SET nama_role=? WHERE id=?")->execute([$nama_role,$id]);
    else $pdo->prepare("INSERT INTO role (nama_role) VALUES (?)")->execute([$nama_role]);
    header('Location: role.php'); exit;
}
if ($action === 'edit' || $action === 'create') {
    $id = (int)($_GET['id'] ?? 0);
    $row = $id ? $pdo->prepare("SELECT * FROM role WHERE id=?")->execute([$id]) : null;
    if ($id) { $stmt=$pdo->prepare("SELECT * FROM role WHERE id=?"); $stmt->execute([$id]); $row = $stmt->fetch(); }
    ?>
    <h2><?= $action==='edit' ? 'Edit Role' : 'Create Role' ?></h2>
    <form method="post">
      <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
      <label>nama_role Role<br><input type="text" name="nama_role" value="<?= htmlspecialchars($row['nama_role'] ?? '') ?>" required></label><br>
      <button type="submit">Simpan</button> <a href="role.php">Batal</a>
    </form>
    <?php require __DIR__ . '/include/footer.php'; exit;
}
$rows = $pdo->query("SELECT * FROM role ORDER BY id DESC")->fetchAll();
?>
<h2>Roles</h2>
<p><a href="role.php?action=create">+ Create Role</a></p>
<table>
  <tr><th>ID</th><th>nama_role</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
  <tr><td><?= htmlspecialchars($r['id']) ?></td><td><?= htmlspecialchars($r['nama_role']) ?></td>
    <td><a href="role.php?action=edit&id=<?= $r['id'] ?>">Edit</a> | <a href="role.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td></tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/include/footer.php'; ?>
