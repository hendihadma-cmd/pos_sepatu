<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pesanan.php');
    exit;
}

$pesanan_id = (int) $_POST['pesanan_id'];
$status     = $_POST['status'];

$status_valid = ['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

if (!in_array($status, $status_valid)) {
    $_SESSION['pesan_admin'] = 'Status tidak valid.';
    header('Location: pesanan.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, "UPDATE pesanan SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $pesanan_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_admin'] = 'Status pesanan berhasil diperbarui.';
} else {
    $_SESSION['pesan_admin'] = 'Gagal memperbarui status pesanan.';
}
mysqli_stmt_close($stmt);

header('Location: pesanan.php');
exit;
