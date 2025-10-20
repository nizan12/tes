<?php
$page_title = "Daftar Rapat";
include 'include/header.php';

$user_id = $_SESSION['user_id'];

// Ambil unit_id dari user notulis saat ini
$stmtUnit = $pdo->prepare("SELECT unit_id FROM users WHERE id = ?");
$stmtUnit->execute([$user_id]);
$user = $stmtUnit->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p>User tidak ditemukan.</p>";
    include 'include/footer.php';
    exit;
}

$unit_id = $user['unit_id'];

// Ambil semua rapat yang diselenggarakan oleh unit yang sama
$stmt = $pdo->prepare("
    SELECT 
        r.*, 
        n.id AS notulen_id, 
        u.nama_unit AS nama_penyelenggara
    FROM rapat r
    LEFT JOIN notulen n ON r.id = n.rapat_id
    LEFT JOIN unit u ON r.penyelenggara = u.id
    WHERE r.penyelenggara = ?
    ORDER BY r.tanggal DESC
");
$stmt->execute([$unit_id]);
$rapatList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>📅 Daftar Rapat Unit Anda (<?= htmlspecialchars($user['unit_id']) ?>)</h2>

    <?php if (count($rapatList) === 0): ?>
        <p>Tidak ada rapat untuk unit Anda.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th>Penyelenggara</th>
                    <th>Status Notulen</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rapatList as $index => $r): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($r['judul']) ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($r['tanggal']))) ?></td>
                        <td><?= htmlspecialchars($r['lokasi']) ?></td>
                        <td><?= htmlspecialchars($r['nama_penyelenggara'] ?? '-') ?></td>
                        <td>
                            <?php if ($r['notulen_id']): ?>
                                <span class="badge badge-success">Sudah Dibuat</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Belum Dibuat</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="detail.php?id=<?= $r['id'] ?>" class="btn btn-info btn-sm">Detail</a>
                            <?php if ($r['notulen_id']): ?>
                                <a href="notulen_form.php?rapat_id=<?= $r['id'] ?>" class="btn btn-primary btn-sm">Lihat / Edit</a>
                            <?php else: ?>
                                <a href="notulen_form.php?rapat_id=<?= $r['id'] ?>" class="btn btn-success btn-sm">Buat Notulen</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'include/footer.php'; ?>
