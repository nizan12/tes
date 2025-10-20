<?php
// admin/users.php
$page_title = 'Users';
require_once __DIR__ . '/include/header.php';

// actions: list, create, edit, delete
$action = $_GET['action'] ?? 'list';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: users.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // create or update
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $unit_id = $_POST['unit_id'] ?: null;
    $role_id = $_POST['role_id'] ?: null;
    $jabatan = $_POST['jabatan'] ?: null;
    $no_hp = $_POST['no_hp'] ?: null;
    $status = $_POST['status'] ?? 'aktif';

    if ($id) {
        // update, possibly password
        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $sql = "UPDATE users SET nama=?, email=?, unit_id=?, role_id=?, jabatan=?, no_hp=?, status=?, password=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nama,$email,$unit_id,$role_id,$jabatan,$no_hp,$status,$hash,$id]);
        } else {
            $sql = "UPDATE users SET nama=?, email=?, unit_id=?, role_id=?, jabatan=?, no_hp=?, status=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nama,$email,$unit_id,$role_id,$jabatan,$no_hp,$status,$id]);
        }
    } else {
        // create: require password
        $hash = password_hash($_POST['password'] ?? 'password', PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (nama,email,password,unit_id,role_id,jabatan,no_hp,status) VALUES (?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$nama,$email,$hash,$unit_id,$role_id,$jabatan,$no_hp,$status]);
    }
    header('Location: users.php'); exit;
}

if ($action === 'edit' || $action === 'create') {
    $id = (int)($_GET['id'] ?? 0);
    $user = null;
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]); $user = $stmt->fetch();
    }
    // fetch roles and units
    $roles = $pdo->query("SELECT * FROM role ORDER BY id")->fetchAll();
    $units = $pdo->query("SELECT * FROM unit ORDER BY id")->fetchAll();
    ?>
<h2><?= $action === 'edit' ? 'Edit User' : 'Create User' ?></h2>
<form method="post">
  <input type="hidden" name="id" value="<?= htmlspecialchars($user['id'] ?? '') ?>">
  <label>Nama<br><input type="text" name="nama" required
      value="<?= htmlspecialchars($user['nama'] ?? '') ?>"></label><br>
  <label>Email<br><input type="email" name="email" required
      value="<?= htmlspecialchars($user['email'] ?? '') ?>"></label><br>
  <label>Password <?= $action==='edit' ? '(kosongkan jika tidak ganti)' : '' ?><br><input type="password"
      name="password"></label><br>
  <label>Unit<br>
    <select name="unit_id">
      <option value="">-- pilih unit --</option>
      <?php foreach($units as $u): ?>
      <option value="<?= $u['id'] ?>" <?= (isset($user['unit_id']) && $user['unit_id']==$u['id'])?'selected':'' ?>>
        <?= htmlspecialchars($u['nama_unit']) ?></option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <label>Role<br>
    <select name="role_id">
      <option value="">-- pilih role --</option>
      <?php foreach($roles as $r): ?>
      <option value="<?= $r['id'] ?>" <?= (isset($user['role_id']) && $user['role_id']==$r['id'])?'selected':'' ?>>
        <?= htmlspecialchars($r['nama_role']) ?></option>
      <?php endforeach; ?>
    </select>
  </label><br>
  <label>Jabatan<br><input type="text" name="jabatan"
      value="<?= htmlspecialchars($user['jabatan'] ?? '') ?>"></label><br>
  <label>No HP<br><input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>"></label><br>
  <label>Status<br>
    <select name="status">
      <option value="aktif" <?= (isset($user['status']) && $user['status']=='aktif')?'selected':'' ?>>aktif</option>
      <option value="nonaktif" <?= (isset($user['status']) && $user['status']=='nonaktif')?'selected':'' ?>>nonaktif
      </option>
    </select>
  </label><br><br>
  <button type="submit">Simpan</button>
  <a href="users.php">Batal</a>
</form>
<?php
    require __DIR__ . '/include/footer.php';
    exit;
}

// default list
$rows = $pdo->query("SELECT u.*, r.nama_role AS role_name, un.nama_unit FROM users u LEFT JOIN role r ON u.role_id=r.id LEFT JOIN unit un ON u.unit_id=un.id ORDER BY u.id DESC")->fetchAll();
?>
<h2>Users</h2>
<p><a href="users.php?action=create">+ Create User</a></p>
<table>
  <tr>
    <th>ID</th>
    <th>Nama</th>
    <th>Email</th>
    <th>Role</th>
    <th>Unit</th>
    <th>Status</th>
    <th>Aksi</th>
  </tr>
  <?php foreach($rows as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['id']) ?></td>
    <td><?= htmlspecialchars($r['nama']) ?></td>
    <td><?= htmlspecialchars($r['email']) ?></td>
    <td><?= htmlspecialchars($r['role_name']) ?></td>
    <td><?= htmlspecialchars($r['nama_unit']) ?></td>
    <td><?= htmlspecialchars($r['status']) ?></td>
    <td class="actions">
      <a href="users.php?action=edit&id=<?= $r['id'] ?>">Edit</a>
      <a href="users.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Hapus user?')">Delete</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php require __DIR__ . '/include/footer.php'; ?>
