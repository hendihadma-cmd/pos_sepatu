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

$user_id           = (int) $_SESSION['user_id'];
$alamat_pengiriman = trim($_POST['alamat_pengiriman']);
$metode_pembayaran = isset($_POST['metode_pembayaran']) ? trim($_POST['metode_pembayaran']) : '';
$metode_valid      = ['Transfer Bank', 'COD (Bayar di Tempat)', 'E-Wallet'];

if ($alamat_pengiriman === '') {
    $_SESSION['error_pesan'] = 'Alamat pengiriman wajib diisi.';
    header('Location: checkout.php');
    exit;
}

if (!in_array($metode_pembayaran, $metode_valid)) {
    $_SESSION['error_pesan'] = 'Silakan pilih metode pembayaran.';
    header('Location: checkout.php');
    exit;
}

// Ambil isi keranjang
$stmt = mysqli_prepare($koneksi, "SELECT k.id AS keranjang_id, k.produk_id, k.ukuran, k.jumlah,
                                          pr.nama_produk, pr.harga, pr.stok
                                   FROM keranjang k
                                   JOIN produk pr ON k.produk_id = pr.id
                                   WHERE k.user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$item_keranjang = [];
$subtotal_belanja = 0;
while ($row = mysqli_fetch_assoc($hasil)) {
    $item_keranjang[] = $row;
    $subtotal_belanja += $row['harga'] * $row['jumlah'];
}
mysqli_stmt_close($stmt);

if (count($item_keranjang) === 0) {
    header('Location: keranjang.php');
    exit;
}

// Validasi ulang stok (jaga-jaga kalau stok berubah sejak terakhir cek)
foreach ($item_keranjang as $item) {
    if ($item['jumlah'] > $item['stok']) {
        $_SESSION['error_pesan'] = 'Stok produk "' . $item['nama_produk'] . '" tidak mencukupi lagi. Silakan sesuaikan jumlah di keranjang.';
        header('Location: keranjang.php');
        exit;
    }
}

// Hitung diskon voucher (jika ada dan masih valid)
$diskon = 0;
$kode_voucher_final = null;
if (isset($_SESSION['voucher_aktif'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM voucher WHERE kode = ? AND status = 'Aktif'");
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['voucher_aktif']);
    mysqli_stmt_execute($stmt);
    $voucher = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($voucher && $subtotal_belanja >= $voucher['min_belanja']) {
        $diskon = $voucher['jenis'] === 'persen'
            ? ($subtotal_belanja * $voucher['nilai'] / 100)
            : $voucher['nilai'];
        $diskon = min($diskon, $subtotal_belanja);
        $kode_voucher_final = $voucher['kode'];
    }
}

$total_akhir = $subtotal_belanja - $diskon;

// Simpan header pesanan
$stmt = mysqli_prepare($koneksi, "INSERT INTO pesanan (user_id, kode_voucher, metode_pembayaran, subtotal, diskon, total_harga, alamat_pengiriman, status)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu Konfirmasi')");
mysqli_stmt_bind_param($stmt, "issddds", $user_id, $kode_voucher_final, $metode_pembayaran, $subtotal_belanja, $diskon, $total_akhir, $alamat_pengiriman);
mysqli_stmt_execute($stmt);
$pesanan_id = mysqli_insert_id($koneksi);
mysqli_stmt_close($stmt);

// Simpan detail pesanan + kurangi stok tiap produk
foreach ($item_keranjang as $item) {
    $subtotal_item = $item['harga'] * $item['jumlah'];

    $stmt = mysqli_prepare($koneksi, "INSERT INTO pesanan_detail (pesanan_id, produk_id, nama_produk, ukuran, jumlah, harga_satuan, subtotal)
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisiidd", $pesanan_id, $item['produk_id'], $item['nama_produk'], $item['ukuran'], $item['jumlah'], $item['harga'], $subtotal_item);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($koneksi, "UPDATE produk SET stok = stok - ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $item['jumlah'], $item['produk_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Kosongkan keranjang & voucher aktif
$stmt = mysqli_prepare($koneksi, "DELETE FROM keranjang WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

unset($_SESSION['voucher_aktif']);

$_SESSION['pesan_sukses'] = 'Pesanan berhasil dibuat! Silakan cek status pesanan di halaman Riwayat Pesanan.';
header('Location: riwayat_pesanan.php');
exit;
