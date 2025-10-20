<?php
$page_title = "Detail Rapat";
include __DIR__ . '/include/header.php';

// Ambil ID rapat dari URL
$rapat_id = $_GET['id'] ?? null;
if (!$rapat_id) {
    echo "<p>ID rapat tidak ditemukan.</p>";
    include __DIR__ . '/include/footer.php';
    exit;
}

// Ambil data rapat + penyelenggara + notulis
$stmt = $pdo->prepare("
    SELECT r.*, 
           u1.nama AS penyelenggara_nama, 
           u2.nama AS notulis_nama
    FROM rapat r
    LEFT JOIN users u1 ON r.penyelenggara = u1.id
    LEFT JOIN users u2 ON r.notulis_id = u2.id
    WHERE r.id = ?
");
$stmt->execute([$rapat_id]);
$rapat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rapat) {
    echo "<p>Rapat tidak ditemukan.</p>";
    include __DIR__ . '/include/footer.php';
    exit;
}

// Ambil peserta rapat
$stmtPeserta = $pdo->prepare("
    SELECT u.nama, u.jabatan, k.status
    FROM kehadiran k
    JOIN users u ON k.user_id = u.id
    WHERE k.rapat_id = ?
");
$stmtPeserta->execute([$rapat_id]);
$peserta = $stmtPeserta->fetchAll(PDO::FETCH_ASSOC);

// Ambil notulen
$stmtNotulen = $pdo->prepare("SELECT * FROM notulen WHERE rapat_id = ?");
$stmtNotulen->execute([$rapat_id]);
$notulen = $stmtNotulen->fetch(PDO::FETCH_ASSOC);

// Ambil lampiran
$stmtLampiran = $pdo->prepare("SELECT * FROM lampiran WHERE rapat_id = ?");
$stmtLampiran->execute([$rapat_id]);
$lampiran = $stmtLampiran->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>📄 Detail Rapat</h2>

    <div class="card">
        <h3><?= htmlspecialchars($rapat['judul']) ?></h3>
        <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($rapat['tanggal'])) ?></p>
        <p><strong>Waktu:</strong> <?= htmlspecialchars($rapat['waktu_mulai']) ?> - <?= htmlspecialchars($rapat['waktu_selesai']) ?></p>
        <p><strong>Lokasi:</strong> <?= htmlspecialchars($rapat['lokasi']) ?></p>
        <p><strong>Penyelenggara:</strong> <?= htmlspecialchars($rapat['penyelenggara_nama'] ?? '-') ?></p>
        <p><strong>Notulis:</strong> <?= htmlspecialchars($rapat['notulis_nama'] ?? '-') ?></p>
        <p><strong>Keterangan:</strong><br><?= nl2br(htmlspecialchars($rapat['keterangan'])) ?></p>
    </div>

    <h3>👥 Peserta Rapat</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($peserta as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nama']) ?></td>
                    <td><?= htmlspecialchars($p['jabatan']) ?></td>
                    <td><?= ucfirst($p['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>📝 Notulen</h3>
    <?php if ($notulen): ?>
        <div class="card">
            <p><strong>Ringkasan:</strong><br><?= nl2br(htmlspecialchars($notulen['ringkasan'])) ?></p>
            <p><strong>Keputusan:</strong><br><?= nl2br(htmlspecialchars($notulen['keputusan'])) ?></p>
            <p><strong>Catatan:</strong><br><?= nl2br(htmlspecialchars($notulen['catatan'])) ?></p>
            <p><small>Dibuat pada: <?= $notulen['created_at'] ?></small></p>
            <a href="notulen_form.php?rapat_id=<?= $rapat_id ?>" class="btn btn-primary">✏️ Edit Notulen</a>
        </div>
    <?php else: ?>
        <p>Belum ada notulen.</p>
        <a href="notulen_form.php?rapat_id=<?= $rapat_id ?>" class="btn btn-success">📝 Buat Notulen</a>
    <?php endif; ?>

    <h3>📎 Lampiran</h3>
    <?php if (count($lampiran) > 0): ?>
        <ul>
            <?php foreach ($lampiran as $l): ?>
                <li>
                    <a href="<?= htmlspecialchars($l['path_file']) ?>" target="_blank">
                        <?= htmlspecialchars($l['nama_file']) ?>
                    </a>
                    <small><?= htmlspecialchars($l['keterangan'] ?? '') ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Tidak ada lampiran.</p>
    <?php endif; ?>

    <div class="actions">
        <a href="tindaklanjut.php?rapat_id=<?= $rapat_id ?>" class="btn btn-warning">✅ Input Tindak Lanjut</a>
        <a href="rapat.php" class="btn btn-secondary">⬅ Kembali</a>
    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
