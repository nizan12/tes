<?php
// admin/notifikasi.php
$page_title = 'Notifikasi';
require_once __DIR__ . '/include/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?: null; // null = broadcast? but our table requires user_id -> handle per-user sends
    $judul = $_POST['judul'] ?? '';
    $pesan = $_POST['pesan'] ?? '';
    $tipe = $_POST['tipe'] ?? 'sistem';
    $ref = $_POST['referensi_id'] ?: null;
    if ($user_id) {
        $pdo->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, referensi_id) VALUES (?,?,?,?,?)")
            ->execute([$user_id, $judul, $pesan, $tipe, $ref]);
    } else {
        // broadcast: insert for all active users
        $users = $pdo->query("SELECT id FROM users WHERE status='aktif'")->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, referensi_id) VALUES (?,?,?,?,?)");
        foreach($users as $u) $stmt->execute([$u,$judul,$pesan,$tipe,$ref]);
    }
    header('Location: notifikasi.php'); exit;
}
if (isset($_GET['delete'])) {
    $id = (int)($_GET['delete'] ?? 0);
    if ($id) $pdo->prepare("DELETE FROM notifikasi WHERE id=?")->execute([$id]);
    header('Location: notifikasi.php'); exit;
}
$rows = $pdo->query("SELECT n.*, u.nama as user_name FROM notifikasi n LEFT JOIN users u ON n.user_id=u.id ORDER BY n.created_at DESC")->fetchAll();
$users = $pdo->query("SELECT id,nama FROM users WHERE status='aktif' ORDER BY nama")->fetchAll();
?>
<h2>Notifikasi</h2>
<h3>Buat Notifikasi</h3>
<form method="post">
  <label>Ke (kosong = broadcast)<br>
    <select name="user_id">
      <option value="">-- Semua (broadcast) --</option>
      <?php foreach($users as $u): ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama']) ?></option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <label>Judul<br><input type="text" name="judul" required></label><br>
  <label>Pesan<br><textarea name="pesan" required></textarea></label><br>
  <label>Tipe<br><input type="text" name="tipe" value="sistem"></label><br>
  <label>Referensi ID (opsional)<br><input type="text" name="referensi_id"></label><br>
  <button type="submit">Kirim</button>
</form>

<h3>Daftar Notifikasi</h3>
<table>
  <tr><th>ID</th><th>User</th><th>Judul</th><th>Pesan</th><th>Tipe</th><th>Tgl</th><th>Aksi</th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['id']) ?></td>
      <td><?= htmlspecialchars($r['user_name']) ?></td>
      <td><?= htmlspecialchars($r['judul']) ?></td>
      <td><?= htmlspecialchars($r['pesan']) ?></td>
      <td><?= htmlspecialchars($r['tipe']) ?></td>
      <td><?= htmlspecialchars($r['created_at']) ?></td>
      <td><a href="notifikasi.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
  <?php endforeach; ?>
</table>

<?php require __DIR__ . '/include/footer.php'; ?>
