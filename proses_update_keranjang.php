<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: keranjang.php');
    exit;
}

$user_id      = (int) $_SESSION['user_id'];
$keranjang_id = (int) $_POST['keranjang_id'];
$aksi         = $_POST['aksi'];

// Pastikan item keranjang ini memang milik user yang sedang login
$stmt = mysqli_prepare($koneksi, "SELECT k.id, k.produk_id, pr.stok FROM keranjang k JOIN produk pr ON k.produk_id = pr.id WHERE k.id = ? AND k.user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $keranjang_id, $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$item) {
    header('Location: keranjang.php');
    exit;
}

if ($aksi === 'hapus') {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM keranjang WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $keranjang_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} elseif ($aksi === 'update') {
    $jumlah_baru = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;

    if ($jumlah_baru <= 0) {
        $_SESSION['error_keranjang'] = 'Jumlah tidak valid.';
        header('Location: keranjang.php');
        exit;
    }

    if ($jumlah_baru > $item['stok']) {
        $_SESSION['error_keranjang'] = 'Jumlah melebihi stok yang tersedia.';
        header('Location: keranjang.php');
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE keranjang SET jumlah = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "iii", $jumlah_baru, $keranjang_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: keranjang.php');
exit;
