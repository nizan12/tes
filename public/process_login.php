<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Ambil input
$email = trim($_POST['email']);
$password = $_POST['password'];

// Ambil data user + nama role
$sql = "SELECT u.*, r.nama_role AS role_nama 
        FROM users u
        LEFT JOIN role r ON u.role_id = r.id
        WHERE u.email = ? AND u.status = 'aktif'";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    // Simpan session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = strtolower($user['role_nama']); // contoh: 'admin', 'notulis', 'peserta'
    $_SESSION['unit_id'] = $user['unit_id'];
    $_SESSION['jabatan'] = $user['jabatan'];

    // Arahkan berdasarkan role
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: /nnotulen/admin/index.php");
            break;
        case 'notulis':
            header("Location: /nnotulen/notulis/index.php");
            break;
        case 'peserta':
            header("Location: /nnotulen/peserta/index.php");
            break;
        default:
            $_SESSION['error'] = "Role tidak dikenali.";
            header("Location: index.php");
            break;
    }
    exit;
} else {
    $_SESSION['error'] = "Email atau password salah / akun tidak aktif.";
    header("Location: index.php");
    exit;
}
?>
