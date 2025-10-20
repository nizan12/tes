<?php
// admin/rapat.php
$page_title = 'Rapat';
require_once __DIR__ . '/include/header.php';

$action = $_GET['action'] ?? 'list';

// --- Hapus Rapat ---
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        $pdo->prepare("DELETE FROM rapat WHERE id=?")->execute([$id]);
    }
    header('Location: rapat.php');
    exit;
}

// --- Simpan (Insert/Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id             = (int)($_POST['id'] ?? 0);
    $judul          = trim($_POST['judul'] ?? '');
    $tanggal        = $_POST['tanggal'] ?? null;
    $w_mulai        = $_POST['waktu_mulai'] ?? null;
    $w_selesai      = $_POST['waktu_selesai'] ?? null;
    $lokasi         = trim($_POST['lokasi'] ?? '');
    $penyelenggara  = $_POST['penyelenggara'] ?: null;
    $keterangan     = trim($_POST['keterangan'] ?? '');

    if ($id) {
        $pdo->prepare("
            UPDATE rapat 
            SET judul=?, tanggal=?, waktu_mulai=?, waktu_selesai=?, lokasi=?, penyelenggara=?, keterangan=? 
            WHERE id=?")
        ->execute([$judul, $tanggal, $w_mulai, $w_selesai, $lokasi, $penyelenggara, $keterangan, $id]);
        $rapat_id = $id;
    } else {
        $pdo->prepare("
            INSERT INTO rapat (judul, tanggal, waktu_mulai, waktu_selesai, lokasi, penyelenggara, keterangan) 
            VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$judul, $tanggal, $w_mulai, $w_selesai, $lokasi, $penyelenggara, $keterangan]);
        $rapat_id = $pdo->lastInsertId();
    }

    // === Simpan Peserta Rapat ===
    if (!empty($_POST['peserta'])) {
        // Hapus semua peserta lama dulu
        $pdo->prepare("DELETE FROM peserta_rapat WHERE rapat_id = ?")->execute([$rapat_id]);

        // Insert peserta baru
        $stmt = $pdo->prepare("INSERT INTO peserta_rapat (rapat_id, user_id, peran) VALUES (?, ?, ?)");
        foreach ($_POST['peserta'] as $uid) {
            $peran = $_POST['peran'][$uid] ?? 'peserta';
            $stmt->execute([$rapat_id, $uid, $peran]);
        }
    }

    header('Location: rapat.php');
    exit;
}

// --- Form Create / Edit ---
if ($action === 'edit' || $action === 'create') {
    $id = (int)($_GET['id'] ?? 0);
    $row = null;
    $selectedPeserta = [];
    $pesertaPeran = [];

    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM rapat WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ambil peserta rapat
        $stmtP = $pdo->prepare("SELECT user_id, peran FROM peserta_rapat WHERE rapat_id=?");
        $stmtP->execute([$id]);
        $pesertaData = $stmtP->fetchAll(PDO::FETCH_KEY_PAIR);
        $selectedPeserta = array_keys($pesertaData);
        $pesertaPeran = $pesertaData;
    }

    // Ambil data unit & user
    $units = $pdo->query("SELECT id, nama_unit FROM unit ORDER BY nama_unit ASC")->fetchAll(PDO::FETCH_ASSOC);
    $users = $pdo->query("SELECT id, nama, jabatan FROM users ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <h2><?= $action === 'edit' ? '✏️ Edit Rapat' : '🆕 Tambah Rapat' ?></h2>

    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">

        <label>Judul<br>
            <input type="text" name="judul" required value="<?= htmlspecialchars($row['judul'] ?? '') ?>">
        </label><br>

        <label>Tanggal<br>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($row['tanggal'] ?? '') ?>">
        </label><br>

        <label>Waktu Mulai<br>
            <input type="time" name="waktu_mulai" value="<?= htmlspecialchars($row['waktu_mulai'] ?? '') ?>">
        </label><br>

        <label>Waktu Selesai<br>
            <input type="time" name="waktu_selesai" value="<?= htmlspecialchars($row['waktu_selesai'] ?? '') ?>">
        </label><br>

        <label>Lokasi<br>
            <input type="text" name="lokasi" value="<?= htmlspecialchars($row['lokasi'] ?? '') ?>">
        </label><br>

        <label>Penyelenggara (Unit)<br>
            <select name="penyelenggara">
                <option value="">-- Pilih Unit --</option>
                <?php foreach ($units as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= (isset($row['penyelenggara']) && $row['penyelenggara'] == $u['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nama_unit']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>

        <label>Keterangan<br>
            <textarea name="keterangan"><?= htmlspecialchars($row['keterangan'] ?? '') ?></textarea>
        </label><br>

        <hr>
        <h3>👥 Daftar Peserta Rapat</h3>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Peran</th>
                <th>Pilih</th>
            </tr>
            <?php foreach ($users as $u): 
                $checked = in_array($u['id'], $selectedPeserta);
                $peran = $pesertaPeran[$u['id']] ?? 'peserta';
            ?>
            <tr>
                <td><?= htmlspecialchars($u['nama']) ?></td>
                <td><?= htmlspecialchars($u['jabatan'] ?? '-') ?></td>
                <td>
                    <select name="peran[<?= $u['id'] ?>]">
                        <option value="peserta" <?= $peran === 'peserta' ? 'selected' : '' ?>>Peserta</option>
                        <option value="pimpinan" <?= $peran === 'pimpinan' ? 'selected' : '' ?>>Pimpinan</option>
                        <option value="notulis" <?= $peran === 'notulis' ? 'selected' : '' ?>>Notulis</option>
                    </select>
                </td>
                <td><input type="checkbox" name="peserta[]" value="<?= $u['id'] ?>" <?= $checked ? 'checked' : '' ?>></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <br>
        <button type="submit">💾 Simpan</button>
        <a href="rapat.php">Batal</a>
    </form>
    <?php
    require __DIR__ . '/include/footer.php';
    exit;
}

// --- Daftar Rapat ---
$rows = $pdo->query("
    SELECT r.*, u.nama_unit AS penyelenggara_name,
           COUNT(p.user_id) AS jumlah_peserta
    FROM rapat r
    LEFT JOIN unit u ON r.penyelenggara = u.id
    LEFT JOIN peserta_rapat p ON p.rapat_id = r.id
    GROUP BY r.id
    ORDER BY r.tanggal DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>📅 Daftar Rapat</h2>
<p><a href="rapat.php?action=create">+ Tambah Rapat</a></p>

<table border="1" cellspacing="0" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Judul</th>
        <th>Tanggal</th>
        <th>Waktu</th>
        <th>Penyelenggara (Unit)</th>
        <th>Peserta</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['id']) ?></td>
        <td><?= htmlspecialchars($r['judul']) ?></td>
        <td><?= htmlspecialchars($r['tanggal']) ?></td>
        <td><?= htmlspecialchars($r['waktu_mulai']) ?> - <?= htmlspecialchars($r['waktu_selesai']) ?></td>
        <td><?= htmlspecialchars($r['penyelenggara_name'] ?? '-') ?></td>
        <td><?= (int)$r['jumlah_peserta'] ?> orang</td>
        <td>
            <a href="rapat.php?action=edit&id=<?= $r['id'] ?>">✏️ Edit</a> | 
            <a href="rapat.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Hapus rapat ini?')">🗑️ Hapus</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require __DIR__ . '/include/footer.php'; ?>
