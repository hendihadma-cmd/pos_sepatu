<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id   = (int) $_SESSION['user_id'];
$produk_id = (int) $_POST['produk_id'];
$ukuran    = isset($_POST['ukuran']) ? (int) $_POST['ukuran'] : 0;
$jumlah    = (int) $_POST['jumlah'];

if ($produk_id <= 0 || $jumlah <= 0) {
    $_SESSION['error_pesan'] = 'Data pesanan tidak lengkap.';
    header('Location: pesan.php?id=' . $produk_id);
    exit;
}

if ($ukuran < 36 || $ukuran > 44) {
    $_SESSION['error_pesan'] = 'Silakan pilih ukuran sepatu.';
    header('Location: pesan.php?id=' . $produk_id);
    exit;
}

// Cek stok produk
$stmt = mysqli_prepare($koneksi, "SELECT stok FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $produk_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$produk) {
    header('Location: index.php');
    exit;
}

if ($jumlah > $produk['stok']) {
    $_SESSION['error_pesan'] = 'Jumlah melebihi stok yang tersedia.';
    header('Location: pesan.php?id=' . $produk_id);
    exit;
}

// Tambah ke keranjang. Jika produk+ukuran yang sama sudah ada di keranjang,
// jumlahnya otomatis ditambahkan (bukan bikin baris baru).
$stmt = mysqli_prepare($koneksi, "INSERT INTO keranjang (user_id, produk_id, ukuran, jumlah) VALUES (?, ?, ?, ?)
                                   ON DUPLICATE KEY UPDATE jumlah = jumlah + VALUES(jumlah)");
mysqli_stmt_bind_param($stmt, "iiii", $user_id, $produk_id, $ukuran, $jumlah);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    $_SESSION['pesan_sukses'] = 'Produk berhasil ditambahkan ke keranjang.';
    header('Location: keranjang.php');
    exit;
} else {
    mysqli_stmt_close($stmt);
    $_SESSION['error_pesan'] = 'Gagal menambahkan ke keranjang, silakan coba lagi.';
    header('Location: pesan.php?id=' . $produk_id);
    exit;
}
