-- =========================================================
-- DATABASE: toko_sepatu
-- Website: TOKO SEPATU ONLINE
-- =========================================================

CREATE DATABASE IF NOT EXISTS toko_sepatu CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE toko_sepatu;

-- =========================================================
-- Tabel users (pelanggan)
-- =========================================================
CREATE TABLE IF NOT EXISTS users (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  alamat TEXT,
  no_hp VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- Tabel admin
-- =========================================================
CREATE TABLE IF NOT EXISTS admin (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- Tabel produk
-- =========================================================
CREATE TABLE IF NOT EXISTS produk (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  nama_produk VARCHAR(150) NOT NULL,
  harga DECIMAL(12,2) NOT NULL,
  deskripsi TEXT,
  gambar_data LONGBLOB DEFAULT NULL,
  gambar_type VARCHAR(50) DEFAULT NULL,
  stok INT(11) NOT NULL DEFAULT 0,
  kategori ENUM('Pria','Wanita','Anak-anak') NOT NULL DEFAULT 'Pria',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- CATATAN: Foto produk sekarang disimpan LANGSUNG di dalam
-- database (kolom gambar_data), bukan sebagai file terpisah
-- di folder gambar/. Tujuannya supaya waktu kamu export lalu
-- import database ke komputer lain, foto produk otomatis ikut
-- pindah tanpa perlu copy folder gambar/ secara manual.
-- =========================================================

-- =========================================================
-- Tabel keranjang (cart) - item yang dipilih pelanggan sebelum checkout
-- =========================================================
CREATE TABLE IF NOT EXISTS keranjang (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) NOT NULL,
  produk_id INT(11) NOT NULL,
  ukuran INT(11) NOT NULL,
  jumlah INT(11) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unik_item_keranjang (user_id, produk_id, ukuran),
  CONSTRAINT fk_keranjang_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_keranjang_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- Tabel voucher (kode diskon)
-- =========================================================
CREATE TABLE IF NOT EXISTS voucher (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(30) NOT NULL UNIQUE,
  jenis ENUM('persen','nominal') NOT NULL DEFAULT 'persen',
  nilai DECIMAL(12,2) NOT NULL,
  min_belanja DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- Tabel pesanan (HEADER pesanan - bisa berisi banyak produk sekaligus)
-- =========================================================
CREATE TABLE IF NOT EXISTS pesanan (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) NOT NULL,
  kode_voucher VARCHAR(30) DEFAULT NULL,
  metode_pembayaran ENUM('Transfer Bank','COD (Bayar di Tempat)','E-Wallet') NOT NULL DEFAULT 'Transfer Bank',
  subtotal DECIMAL(12,2) NOT NULL,
  diskon DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_harga DECIMAL(12,2) NOT NULL,
  alamat_pengiriman TEXT NOT NULL,
  status ENUM('Menunggu Konfirmasi','Diproses','Dikirim','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Konfirmasi',
  tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- Tabel pesanan_detail (DETAIL item per pesanan)
-- =========================================================
CREATE TABLE IF NOT EXISTS pesanan_detail (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  pesanan_id INT(11) NOT NULL,
  produk_id INT(11) NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  ukuran INT(11) NOT NULL,
  jumlah INT(11) NOT NULL,
  harga_satuan DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_detail_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
  CONSTRAINT fk_detail_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- PENTING - Jika database toko_sepatu SUDAH pernah dibuat sebelumnya
-- =========================================================
-- Struktur tabel `pesanan` di atas BERUBAH TOTAL (dulu 1 pesanan = 1 produk,
-- sekarang 1 pesanan = header, detail produknya ada di tabel pesanan_detail
-- terpisah) untuk mendukung fitur Keranjang Belanja (checkout banyak produk
-- sekaligus). Karena perubahan strukturnya besar, cara PALING AMAN adalah
-- install ulang dari nol:
--
--   DROP TABLE IF EXISTS pesanan_detail;
--   DROP TABLE IF EXISTS pesanan;
--   DROP TABLE IF EXISTS keranjang;
--   DROP TABLE IF EXISTS voucher;
--   DROP TABLE IF EXISTS produk;
--   DROP TABLE IF EXISTS users;
--   DROP TABLE IF EXISTS admin;
--
-- Lalu import ulang file db_toko_sepatu.sql ini dari awal, dan jalankan
-- kembali admin/setup_admin.php untuk membuat akun admin.
-- =========================================================

-- =========================================================
-- Contoh data produk (opsional, bisa dihapus)
-- =========================================================
INSERT INTO produk (nama_produk, harga, deskripsi, gambar_data, stok, kategori) VALUES
('Sneakers Putih Classic', 350000, 'Sepatu sneakers putih, nyaman untuk sehari-hari.', NULL, 20, 'Pria'),
('Running Shoes Pro', 425000, 'Sepatu lari ringan dengan sol empuk anti slip.', NULL, 15, 'Wanita'),
('Boots Kulit Coklat', 599000, 'Sepatu boots kulit asli, cocok untuk gaya kasual.', NULL, 10, 'Pria'),
('Sneakers Anak Warna-warni', 175000, 'Sepatu anak yang ringan, empuk, dan mudah dipakai sendiri.', NULL, 25, 'Anak-anak');

-- =========================================================
-- Contoh data voucher (opsional, bisa dihapus/ubah lewat admin)
-- =========================================================
INSERT INTO voucher (kode, jenis, nilai, min_belanja, status) VALUES
('DISKON10', 'persen', 10, 100000, 'Aktif'),
('HEMAT20K', 'nominal', 20000, 150000, 'Aktif');

-- =========================================================
-- CATATAN PENTING SOAL AKUN ADMIN
-- =========================================================
-- Tabel admin SENGAJA dikosongkan di sini karena password harus
-- di-hash menggunakan fungsi password_hash() milik PHP agar aman
-- dan valid saat proses login (password_verify()).
--
-- Setelah database ini diimpor, buka file berikut SEKALI lewat browser:
--   http://localhost/toko-sepatu/admin/setup_admin.php
-- File tersebut akan membuat akun admin default:
--   username: admin
--   password: admin123
-- Setelah berhasil, HAPUS file admin/setup_admin.php dari server.
