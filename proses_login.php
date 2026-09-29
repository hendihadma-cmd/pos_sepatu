<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email    = trim($_POST['email']);
$password = $_POST['password'];
$redirect_ke = isset($_POST['redirect']) && $_POST['redirect'] !== '' ? $_POST['redirect'] : 'index.php';

// Cegah open redirect: hanya izinkan file lokal .php
if (!preg_match('/^[a-zA-Z0-9_\-\.\?\=\&]+\.php.*$/', $redirect_ke)) {
    $redirect_ke = 'index.php';
}

$stmt = mysqli_prepare($koneksi, "SELECT id, nama, password FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_nama'] = $user['nama'];
    header('Location: ' . $redirect_ke);
    exit;
} else {
    $_SESSION['error_login'] = 'Email atau password salah.';
    header('Location: login.php');
    exit;
}
