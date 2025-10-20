<?php
// admin/agenda.php
$page_title = 'Agenda Rapat';
require_once __DIR__ . '/include/header.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0); if ($id) $pdo->prepare("DELETE FROM agenda_rapat WHERE id=?")->execute([$id]);
    header('Location: agenda.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $rapat_id = $_POST['rapat_id'] ?: null;
    $topik = $_POST['topik'] ?? null;
    $uraian = $_POST['uraian'] ?? null;
    if ($id) $pdo->prepare("UPDATE agenda_rapat SET rapat_id=?, topik=?, uraian=? WHERE id=?")->execute([$rapat_id,$topik,$uraian,$id]);
    else $pdo->prepare("INSERT INTO agenda_rapat (rapat_id,topik,uraian) VALUES (?,?,?)")->execute([$rapat_id,$topik,$uraian]);
    header('Location: agenda.php'); exit;
}
if ($action === 'create' || $action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $row = $id ? $pdo->prepare("SELECT * FROM agenda_rapat WHERE id=?")->execute([$id]) : null;
    if ($id) { $stmt=$pdo->prepare("SELECT * FROM agenda_rapat WHERE id=?"); $stmt->execute([$id]); $row = $stmt->fetch(); }
    $rapats = $pdo->query("SELECT id,judul FROM rapat ORDER BY tanggal DESC")->fetchAll();
    ?>
    <h2><?= $action==='edit' ? 'Edit Agenda' : 'Create Agenda' ?></h2>
    <form method="post">
      <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
      <label>Rapat<br><select name="rapat_id"><option value="">-- pilih rapat --</option>
        <?php foreach($rapats as $rp): ?>
          <option value="<?= $rp['id'] ?>" <?= (isset($row['rapat_id']) && $row['rapat_id']==$rp['id'])?'selected':'' ?>><?= htmlspecialchars($rp['judul']) ?></option>
        <?php endforeach; ?>
      </select></label><br>
      <label>Topik<br><input type="text" name="topik" value="<?= htmlspecialchars($row['topik'] ?? '') ?>"></label><br>
      <label>Uraian<br><textarea name="uraian"><?= htmlspecialchars($row['uraian'] ?? '') ?></textarea></label><br>
      <button type="submit">Simpan</button> <a href="agenda.php">Batal</a>
    </form>
    <?php require __DIR__ . '/include/footer.php'; exit;
}
$rows = $pdo->query("SELECT a.*, r.judul as rapat_judul FROM agenda_rapat a LEFT JOIN rapat r ON a.rapat_id=r.id ORDER BY a.id DESC")->fetchAll();
?>
<h2>Agenda Rapat</h2>
<p><a href="agenda.php?action=create">+ Create Agenda</a></p>
<table>
  <tr><th>ID</th><th>Rapat</th><th>Topik</th><th>Uraian</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['id']) ?></td>
    <td><?= htmlspecialchars($r['rapat_judul']) ?></td>
    <td><?= htmlspecialchars($r['topik']) ?></td>
    <td><?= htmlspecialchars($r['uraian']) ?></td>
    <td><a href="agenda.php?action=edit&id=<?= $r['id'] ?>">Edit</a> | <a href="agenda.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/include/footer.php'; ?>
