<?php
// =========================================================
// KONEKSI DATABASE - TOKO SEPATU ONLINE
// =========================================================
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "toko_sepatu";

$koneksi = mysqli_connect($host, $user, $pass, $dbname);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");
?>
