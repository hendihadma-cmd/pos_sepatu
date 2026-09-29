<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registrasi.php');
    exit;
}

$nama    = trim($_POST['nama']);
$email   = trim($_POST['email']);
$no_hp   = trim($_POST['no_hp']);
$alamat  = trim($_POST['alamat']);
$password = $_POST['password'];
$konfirmasi_password = $_POST['konfirmasi_password'];

// Validasi sederhana
if ($nama === '' || $email === '' || $no_hp === '' || $alamat === '' || $password === '') {
    $_SESSION['error_registrasi'] = 'Semua field wajib diisi.';
    header('Location: registrasi.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_registrasi'] = 'Format email tidak valid.';
    header('Location: registrasi.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error_registrasi'] = 'Password minimal 6 karakter.';
    header('Location: registrasi.php');
    exit;
}

if ($password !== $konfirmasi_password) {
    $_SESSION['error_registrasi'] = 'Konfirmasi password tidak sesuai.';
    header('Location: registrasi.php');
    exit;
}

// Cek email sudah terdaftar atau belum
$stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $_SESSION['error_registrasi'] = 'Email sudah terdaftar, silakan login.';
    mysqli_stmt_close($stmt);
    header('Location: registrasi.php');
    exit;
}
mysqli_stmt_close($stmt);

// Simpan user baru
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, alamat, no_hp) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $password_hash, $alamat, $no_hp);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    $_SESSION['sukses_registrasi'] = 'Registrasi berhasil! Silakan login.';
    header('Location: login.php');
    exit;
} else {
    mysqli_stmt_close($stmt);
    $_SESSION['error_registrasi'] = 'Registrasi gagal, silakan coba lagi.';
    header('Location: registrasi.php');
    exit;
}
