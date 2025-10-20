<?php
$page_title = "Daftar Rapat";
include __DIR__ . '/include/header.php';


if (!isset($_GET['rapat_id'])) {
    echo "<p>Rapat tidak ditemukan.</p>";
    include 'include/footer.php';
    exit;
}

$rapat_id = $_GET['rapat_id'];

// Ambil data rapat
$stmt = $pdo->prepare("SELECT * FROM rapat WHERE id = ?");
$stmt->execute([$rapat_id]);
$rapat = $stmt->fetch();

if (!$rapat) {
    echo "<p>Data rapat tidak ditemukan.</p>";
    include 'include/footer.php';
    exit;
}

// Cek apakah notulen sudah ada
$stmt = $pdo->prepare("SELECT * FROM notulen WHERE rapat_id = ?");
$stmt->execute([$rapat_id]);
$notulen = $stmt->fetch();

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ringkasan = $_POST['ringkasan'];
    $keputusan = $_POST['keputusan'];
    $catatan = $_POST['catatan'];
    $now = date('Y-m-d H:i:s');

    if ($notulen) {
        // Update notulen
        $stmt = $pdo->prepare("UPDATE notulen SET ringkasan=?, keputusan=?, catatan=?, updated_at=? WHERE rapat_id=?");
        $stmt->execute([$ringkasan, $keputusan, $catatan, $now, $rapat_id]);
        $msg = "Notulen berhasil diperbarui!";
    } else {
        // Insert notulen baru
        $stmt = $pdo->prepare("INSERT INTO notulen (rapat_id, notulis_id, ringkasan, keputusan, catatan, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$rapat_id, $user_id, $ringkasan, $keputusan, $catatan, $now]);
        $msg = "Notulen berhasil disimpan!";
    }

    echo "<script>alert('$msg'); window.location.href='rapat.php';</script>";
    exit;
}
?>

<div class="content">
    <h2>📝 Form Notulen Rapat</h2>

    <div class="card">
        <h3><?= htmlspecialchars($rapat['judul']) ?></h3>
        <p><strong>Tanggal:</strong> <?= $rapat['tanggal'] ?> |
           <strong>Lokasi:</strong> <?= htmlspecialchars($rapat['lokasi']) ?></p>
        <p><strong>Keterangan:</strong> <?= nl2br(htmlspecialchars($rapat['keterangan'])) ?></p>
    </div>

    <form method="POST" class="form">
        <div class="form-group">
            <label>Ringkasan Rapat</label>
            <textarea name="ringkasan" rows="4" required><?= $notulen['ringkasan'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label>Keputusan</label>
            <textarea name="keputusan" rows="4" required><?= $notulen['keputusan'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label>Catatan Tambahan</label>
            <textarea name="catatan" rows="3"><?= $notulen['catatan'] ?? '' ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><?= $notulen ? 'Perbarui Notulen' : 'Simpan Notulen' ?></button>
        <a href="rapat.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?php include 'include/footer.php'; ?>
