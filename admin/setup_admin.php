<?php
// =========================================================
// SETUP ADMIN DEFAULT (JALANKAN SEKALI SAJA, LALU HAPUS FILE INI)
// =========================================================
require_once 'koneksi.php';

$username = 'admin';
$password = 'admin123';

// Cek apakah admin sudah ada
$stmt = mysqli_prepare($koneksi, "SELECT id FROM admin WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo "Akun admin dengan username '<b>admin</b>' sudah ada. Tidak perlu setup ulang.<br>";
    echo "Silakan hapus file setup_admin.php ini demi keamanan.";
    exit;
}
mysqli_stmt_close($stmt);

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($koneksi, "INSERT INTO admin (username, password) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "ss", $username, $password_hash);

if (mysqli_stmt_execute($stmt)) {
    echo "Akun admin berhasil dibuat!<br>";
    echo "Username: <b>admin</b><br>";
    echo "Password: <b>admin123</b><br><br>";
    echo "<b>PENTING:</b> Segera hapus file <code>admin/setup_admin.php</code> ini dari server setelah membaca pesan ini.<br>";
    echo '<a href="login.php">Klik di sini untuk login ke admin</a>';
} else {
    echo "Gagal membuat akun admin: " . mysqli_error($koneksi);
}
mysqli_stmt_close($stmt);
?>
