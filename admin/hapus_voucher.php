<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';

$voucher_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($voucher_id > 0) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM voucher WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $voucher_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['pesan_admin'] = 'Voucher berhasil dihapus.';
    } else {
        $_SESSION['pesan_admin'] = 'Gagal menghapus voucher.';
    }
    mysqli_stmt_close($stmt);
}

header('Location: voucher.php');
exit;
