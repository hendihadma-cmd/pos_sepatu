<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

$stmt = mysqli_prepare($koneksi, "SELECT id, username, password FROM admin WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if ($admin && password_verify($password, $admin['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    header('Location: dashboard.php');
    exit;
} else {
    $_SESSION['error_login_admin'] = 'Username atau password salah.';
    header('Location: login.php');
    exit;
}
