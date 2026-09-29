<?php
// =========================================================
// gambar.php - Menampilkan foto produk yang tersimpan di database
// Dipakai seperti: <img src="gambar.php?id=5">
// =========================================================
require_once 'koneksi.php';

$produk_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = mysqli_prepare($koneksi, "SELECT gambar_data, gambar_type FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $produk_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if ($produk && !empty($produk['gambar_data'])) {
    header('Content-Type: ' . $produk['gambar_type']);
    header('Cache-Control: public, max-age=86400');
    echo $produk['gambar_data'];
} else {
    // Tidak ada foto - kirim gambar placeholder abu-abu polos
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="400" height="300" fill="#e9ecef"/><text x="50%" y="50%" font-family="sans-serif" font-size="20" fill="#6c757d" text-anchor="middle" dominant-baseline="middle">Tanpa Foto</text></svg>';
}
exit;
