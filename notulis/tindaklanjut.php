<?php
// user/tindaklanjut.php
$page_title = 'Tindak Lanjut Rapat';
require_once __DIR__ . '/include/header.php';

$notulis_id = $_SESSION['user_id'] ?? 0;

// --- Hapus Tindak Lanjut ---
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        $pdo->prepare("DELETE FROM tindak_lanjut WHERE id=?")->execute([$id]);
    }
    header('Location: tindaklanjut.php');
    exit;
}

// --- Simpan (Insert / Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $rapat_id = (int)($_POST['rapat_id'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $penanggung_jawab = (int)($_POST['penanggung_jawab'] ?? 0);
    $tenggat = $_POST['tenggat'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE tindak_lanjut 
            SET deskripsi=?, penanggung_jawab=?, tenggat=? 
            WHERE id=?");
        $stmt->execute([$deskripsi, $penanggung_jawab, $tenggat, $id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO tindak_lanjut (rapat_id, deskripsi, penanggung_jawab, tenggat, status) 
            VALUES (?, ?, ?, ?, 'belum')");
        $stmt->execute([$rapat_id, $deskripsi, $penanggung_jawab, $tenggat]);
    }
    header('Location: tindaklanjut.php');
    exit;
}

// --- Ambil daftar rapat milik notulis ini ---
$rapat_user = $pdo->prepare("
    SELECT r.id, r.judul
    FROM rapat r
    JOIN notulen n ON n.rapat_id = r.id
    WHERE n.notulis_id = ?
    ORDER BY r.tanggal DESC
");
$rapat_user->execute([$notulis_id]);
$rapat_list = $rapat_user->fetchAll(PDO::FETCH_ASSOC);

// --- Form Create/Edit ---
$action = $_GET['action'] ?? 'list';
if ($action === 'create' || $action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $row = null;
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM tindak_lanjut WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // daftar user untuk penanggung jawab
    $users = $pdo->query("SELECT id, nama FROM users ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <h2><?= $action === 'edit' ? 'Edit' : 'Tambah' ?> Tindak Lanjut</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">

        <label>Rapat<br>
            <select name="rapat_id" required>
                <option value="">-- Pilih Rapat --</option>
                <?php foreach ($rapat_list as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= isset($row['rapat_id']) && $row['rapat_id'] == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['judul']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>

        <label>Deskripsi<br>
            <textarea name="deskripsi" required><?= htmlspecialchars($row['deskripsi'] ?? '') ?></textarea>
        </label><br>

        <label>Penanggung Jawab<br>
            <select name="penanggung_jawab" required>
                <option value="">-- Pilih User --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= isset($row['penanggung_jawab']) && $row['penanggung_jawab'] == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>

        <label>Tenggat<br>
            <input type="date" name="tenggat" value="<?= htmlspecialchars($row['tenggat'] ?? '') ?>">
        </label><br>

        <button type="submit">💾 Simpan</button>
        <a href="tindaklanjut.php">Batal</a>
    </form>
    <?php
    require __DIR__ . '/include/footer.php';
    exit;
}

// --- Daftar tindak lanjut (rapat milik notulis ini) ---
$stmt = $pdo->prepare("

    SELECT t.*, r.judul AS rapat_judul, u.nama AS pj_name
    FROM tindak_lanjut t
    LEFT JOIN rapat r ON t.rapat_id = r.id
    LEFT JOIN notulen n ON n.rapat_id = r.id
    LEFT JOIN users u ON t.penanggung_jawab = u.id
    WHERE n.notulis_id = ?
    ORDER BY t.tenggat ASC;

");
$stmt->execute([$notulis_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>📋 Daftar Tindak Lanjut</h2>
<p><a href="tindaklanjut.php?action=create">+ Tambah Tindak Lanjut</a></p>

<table border="1" cellspacing="0" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Rapat</th>
        <th>Deskripsi</th>
        <th>Penanggung Jawab</th>
        <th>Tenggat</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['id']) ?></td>
        <td><?= htmlspecialchars($r['rapat_judul']) ?></td>
        <td><?= htmlspecialchars($r['deskripsi']) ?></td>
        <td><?= htmlspecialchars($r['pj_name']) ?></td>
        <td><?= htmlspecialchars($r['tenggat']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
        <td>
            <a href="tindaklanjut.php?action=edit&id=<?= $r['id'] ?>">✏️ Edit</a> | 
            <a href="tindaklanjut.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Hapus tindak lanjut ini?')">🗑️ Hapus</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php require __DIR__ . '/include/footer.php'; ?>
