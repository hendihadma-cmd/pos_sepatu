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
    header('Location: checkout.php');
    exit;
}

$aksi = $_POST['aksi'];

if ($aksi === 'batal') {
    unset($_SESSION['voucher_aktif']);
    header('Location: checkout.php');
    exit;
}

$kode_voucher = strtoupper(trim($_POST['kode_voucher']));

if ($kode_voucher === '') {
    $_SESSION['error_voucher'] = 'Silakan masukkan kode voucher.';
    header('Location: checkout.php');
    exit;
}

// Cek voucher valid
$stmt = mysqli_prepare($koneksi, "SELECT * FROM voucher WHERE kode = ? AND status = 'Aktif'");
mysqli_stmt_bind_param($stmt, "s", $kode_voucher);
mysqli_stmt_execute($stmt);
$voucher = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$voucher) {
    $_SESSION['error_voucher'] = 'Kode voucher tidak ditemukan atau sudah tidak aktif.';
    header('Location: checkout.php');
    exit;
}

// Hitung subtotal keranjang untuk cek syarat minimal belanja
$user_id = (int) $_SESSION['user_id'];
$stmt = mysqli_prepare($koneksi, "SELECT k.jumlah, pr.harga FROM keranjang k JOIN produk pr ON k.produk_id = pr.id WHERE k.user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$subtotal = 0;
while ($row = mysqli_fetch_assoc($hasil)) {
    $subtotal += $row['harga'] * $row['jumlah'];
}
mysqli_stmt_close($stmt);

if ($subtotal < $voucher['min_belanja']) {
    $_SESSION['error_voucher'] = 'Minimal belanja untuk voucher ini adalah Rp ' . number_format($voucher['min_belanja'], 0, ',', '.') . '.';
    header('Location: checkout.php');
    exit;
}

$_SESSION['voucher_aktif'] = $kode_voucher;
$_SESSION['sukses_voucher'] = 'Voucher berhasil diterapkan.';
header('Location: checkout.php');
exit;
