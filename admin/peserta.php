<?php
// admin/peserta.php
$page_title = 'Peserta Rapat';
require_once __DIR__ . '/include/header.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0); if ($id) $pdo->prepare("DELETE FROM peserta_rapat WHERE id=?")->execute([$id]);
    header('Location: peserta.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $rapat_id = $_POST['rapat_id'] ?: null;
    $user_id = $_POST['user_id'] ?: null;
    $peran = $_POST['peran'] ?? 'peserta';
    if ($id) $pdo->prepare("UPDATE peserta_rapat SET rapat_id=?, user_id=?, peran=? WHERE id=?")->execute([$rapat_id,$user_id,$peran,$id]);
    else $pdo->prepare("INSERT INTO peserta_rapat (rapat_id,user_id,peran) VALUES (?,?,?)")->execute([$rapat_id,$user_id,$peran]);
    header('Location: peserta.php'); exit;
}
if ($action === 'create' || $action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $row = null;
    if ($id) { $stmt = $pdo->prepare("SELECT * FROM peserta_rapat WHERE id=?"); $stmt->execute([$id]); $row = $stmt->fetch(); }
    $rapats = $pdo->query("SELECT id,judul FROM rapat ORDER BY tanggal DESC")->fetchAll();
    $users = $pdo->query("SELECT id,nama FROM users ORDER BY nama")->fetchAll();
    ?>
    <h2><?= $action==='edit' ? 'Edit Peserta' : 'Add Peserta' ?></h2>
    <form method="post">
      <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
      <label>Rapat<br><select name="rapat_id"><option value="">-- pilih --</option>
        <?php foreach($rapats as $rp): ?>
          <option value="<?= $rp['id'] ?>" <?= (isset($row['rapat_id']) && $row['rapat_id']==$rp['id'])?'selected':'' ?>><?= htmlspecialchars($rp['judul']) ?></option>
        <?php endforeach; ?>
      </select></label><br>
      <label>User<br><select name="user_id"><option value="">-- pilih --</option>
        <?php foreach($users as $u): ?>
          <option value="<?= $u['id'] ?>" <?= (isset($row['user_id']) && $row['user_id']==$u['id'])?'selected':'' ?>><?= htmlspecialchars($u['nama']) ?></option>
        <?php endforeach; ?>
      </select></label><br>
      <label>Peran<br><select name="peran"><option value="pimpinan">pimpinan</option><option value="notulis">notulis</option><option value="peserta">peserta</option></select></label><br>
      <button type="submit">Simpan</button> <a href="peserta.php">Batal</a>
    </form>
    <?php require __DIR__ . '/include/footer.php'; exit;
}
$rows = $pdo->query("SELECT p.*, u.nama as user_name, r.judul as rapat_judul FROM peserta_rapat p LEFT JOIN users u ON p.user_id=u.id LEFT JOIN rapat r ON p.rapat_id=r.id ORDER BY p.id DESC")->fetchAll();
?>
<h2>Peserta Rapat</h2>
<p><a href="peserta.php?action=create">+ Tambah Peserta</a></p>
<table>
  <tr><th>ID</th><th>Rapat</th><th>User</th><th>Peran</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['id']) ?></td>
      <td><?= htmlspecialchars($r['rapat_judul']) ?></td>
      <td><?= htmlspecialchars($r['user_name']) ?></td>
      <td><?= htmlspecialchars($r['peran']) ?></td>
      <td><a href="peserta.php?action=edit&id=<?= $r['id'] ?>">Edit</a> | <a href="peserta.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/include/footer.php'; ?>
