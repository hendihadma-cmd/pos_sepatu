<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';

$produk_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($produk_id > 0) {
    // Foto tersimpan di database (kolom gambar_data), jadi otomatis ikut
    // terhapus bersama baris produknya - tidak perlu hapus file terpisah.
    $stmt = mysqli_prepare($koneksi, "DELETE FROM produk WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $produk_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['pesan_admin'] = 'Produk berhasil dihapus.';
    } else {
        $_SESSION['pesan_admin'] = 'Gagal menghapus produk.';
    }
    mysqli_stmt_close($stmt);
}

header('Location: produk.php');
exit;
