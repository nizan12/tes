<?php
// admin/tindaklanjut.php
$page_title = 'Tindak Lanjut';
require_once __DIR__ . '/include/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update status / catatan
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'belum';
    $catatan = $_POST['catatan'] ?? null;
    if ($id) $pdo->prepare("UPDATE tindak_lanjut SET status=?, catatan=? WHERE id=?")->execute([$status,$catatan,$id]);
    header('Location: tindaklanjut.php'); exit;
}
$rows = $pdo->query("SELECT t.*, r.judul as rapat_judul, u.nama as pj_name FROM tindak_lanjut t LEFT JOIN rapat r ON t.rapat_id=r.id LEFT JOIN users u ON t.penanggung_jawab=u.id ORDER BY t.tenggat ASC")->fetchAll();
?>
<h2>Tindak Lanjut</h2>
<table>
  <tr><th>ID</th><th>Rapat</th><th>Deskripsi</th><th>Penanggung Jawab</th><th>Tenggat</th><th>Status</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['id']) ?></td>
      <td><?= htmlspecialchars($r['rapat_judul']) ?></td>
      <td><?= htmlspecialchars($r['deskripsi']) ?></td>
      <td><?= htmlspecialchars($r['pj_name']) ?></td>
      <td><?= htmlspecialchars($r['tenggat']) ?></td>
      <td><?= htmlspecialchars($r['status']) ?></td>
      <td>
        <form method="post" style="display:inline-block">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <select name="status">
            <option value="belum" <?= $r['status']=='belum'?'selected':'' ?>>belum</option>
            <option value="proses" <?= $r['status']=='proses'?'selected':'' ?>>proses</option>
            <option value="selesai" <?= $r['status']=='selesai'?'selected':'' ?>>selesai</option>
          </select>
          <input type="text" name="catatan" placeholder="Catatan" value="<?= htmlspecialchars($r['catatan'] ?? '') ?>">
          <button type="submit">Update</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/include/footer.php'; ?>
