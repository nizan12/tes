<?php
// Upload file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['lampiran'])) {
    $file = $_FILES['lampiran'];
    $keterangan = $_POST['keterangan'] ?? '';

    $upload_dir = __DIR__ . "/../../../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = "lampiran_" . time() . "_" . rand(100,999) . "." . $ext;
        $target = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("
                INSERT INTO lampiran (rapat_id, nama_file, path_file, keterangan, uploaded_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$rapat_id, $file['name'], $new_name, $keterangan]);
            echo "<p style='color:green;'>✅ File berhasil diupload.</p>";
        } else {
            echo "<p style='color:red;'>❌ Gagal menyimpan file.</p>";
        }
    }
}

// Ambil lampiran
$stmt = $pdo->prepare("SELECT * FROM lampiran WHERE rapat_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$rapat_id]);
$lampiran = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="lampiran" required>
    <input type="text" name="keterangan" placeholder="Keterangan (opsional)">
    <button type="submit">Upload</button>
</form>

<table border="1" cellpadding="5" cellspacing="0">
<tr><th>No</th><th>Nama File</th><th>Keterangan</th><th>Tanggal</th><th>Aksi</th></tr>
<?php foreach ($lampiran as $i => $l): ?>
<tr>
    <td><?= $i+1 ?></td>
    <td><a href="/uploads/<?= htmlspecialchars($l['path_file']) ?>" target="_blank"><?= htmlspecialchars($l['nama_file']) ?></a></td>
    <td><?= htmlspecialchars($l['keterangan']) ?></td>
    <td><?= $l['uploaded_at'] ?></td>
    <td><a href="lampiran_delete.php?id=<?= $l['id'] ?>&rapat_id=<?= $rapat_id ?>" onclick="return confirm('Hapus lampiran ini?')">🗑️</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($lampiran)) echo "<tr><td colspan='5'>Belum ada lampiran.</td></tr>"; ?>
</table>
