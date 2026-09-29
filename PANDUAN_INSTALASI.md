# Panduan Instalasi - TOKO SEPATU ONLINE

Website ini dibuat dengan **PHP Native**, **MySQL**, **HTML**, **CSS**, **JavaScript**, dan **Bootstrap 5**.
Dirancang untuk dijalankan di **XAMPP**.

## 1. Persiapan

1. Install [XAMPP](https://www.apachefriends.org/) (sudah termasuk Apache + MySQL + PHP).
2. Salin folder `toko-sepatu` ke dalam folder `htdocs` di instalasi XAMPP Anda.
   - Windows: `C:\xampp\htdocs\toko-sepatu`
   - macOS/Linux: `/Applications/XAMPP/htdocs/toko-sepatu` atau `/opt/lampp/htdocs/toko-sepatu`
3. Folder `gambar/` sudah tidak digunakan lagi & boleh diabaikan — foto produk sekarang disimpan langsung di dalam database (kolom `gambar_data`), bukan sebagai file terpisah.

## 2. Membuat Database

1. Jalankan Apache dan MySQL melalui **XAMPP Control Panel**.
2. Buka [http://localhost/phpmyadmin](http://localhost/phpmyadmin) di browser.
3. Klik tab **Import**, lalu pilih file `db_toko_sepatu.sql` yang ada di folder project ini.
4. Klik **Go / Import**. Database `toko_sepatu` beserta tabel `users`, `admin`, `produk`, dan `pesanan` akan otomatis dibuat, lengkap dengan 3 contoh data produk.

   *(Alternatif: buat database baru bernama `toko_sepatu` lalu jalankan isi file `db_toko_sepatu.sql` lewat tab SQL.)*

## 3. Membuat Akun Admin

Karena password admin harus di-hash menggunakan fungsi `password_hash()` milik PHP (bukan lewat query SQL biasa), lakukan langkah berikut **satu kali saja** setelah database berhasil diimpor:

1. Buka browser, akses: `http://localhost/toko-sepatu/admin/setup_admin.php`
2. Halaman akan menampilkan konfirmasi bahwa akun admin berhasil dibuat dengan:
   - **Username:** `admin`
   - **Password:** `admin123`
3. **Setelah berhasil, segera hapus file `admin/setup_admin.php`** dari server untuk keamanan.

## 4. Menjalankan Website

- **Halaman Pelanggan:** `http://localhost/toko-sepatu/index.php`
- **Halaman Admin:** `http://localhost/toko-sepatu/admin/login.php`

### Alur Pelanggan
1. Pelanggan bisa melihat semua produk di halaman utama **tanpa harus login**.
2. Saat menekan tombol **Pesan**, sistem mengecek status login:
   - Jika belum login → diarahkan ke halaman **Login**, dan setelah berhasil login akan otomatis kembali ke halaman pemesanan produk yang dipilih.
   - Jika sudah login → langsung menuju form pemesanan.
3. Setelah pesanan dikirim, pelanggan bisa memantau statusnya di menu **Riwayat Pesanan**.
4. Pelanggan dapat logout melalui tombol **Logout** di navbar.

### Alur Admin
1. Login di `admin/login.php` menggunakan akun admin yang sudah dibuat pada langkah 3.
2. Di **Dashboard**, admin bisa melihat ringkasan statistik dan daftar seluruh produk.
4. Menu **Tambah Produk** untuk menambah produk baru beserta upload foto (foto disimpan langsung ke database, jadi otomatis ikut ketika kamu export/import database ke komputer lain).
4. Ikon pensil pada tabel produk untuk **Edit Produk**, ikon tong sampah untuk **Hapus Produk**.
5. Menu **Data Pesanan** untuk melihat seluruh pesanan pelanggan dan **mengubah status pesanan** (Menunggu Konfirmasi → Diproses → Dikirim → Selesai / Dibatalkan).

## 5. Struktur Folder

```
toko-sepatu/
│
├── index.php                 Halaman utama daftar produk
├── koneksi.php                Koneksi database
├── header.php / footer.php    Template navbar & footer pelanggan
├── login.php                  Halaman login pelanggan
├── registrasi.php             Halaman registrasi pelanggan
├── proses_login.php           Proses backend login
├── proses_registrasi.php      Proses backend registrasi
├── logout.php                 Logout pelanggan
├── pesan.php                  Form pemesanan (wajib login)
├── proses_pesan.php           Proses backend simpan pesanan
├── riwayat_pesanan.php        Riwayat pesanan pelanggan
├── style.css                  Style halaman pelanggan
├── db_toko_sepatu.sql         Script SQL database
│
├── gambar/                    Folder penyimpanan foto produk
│
└── admin/
    ├── index.php               Redirect ke login/dashboard admin
    ├── koneksi.php              Koneksi database admin
    ├── setup_admin.php          Setup akun admin default (hapus setelah dipakai)
    ├── login.php                Login admin
    ├── proses_login.php         Proses backend login admin
    ├── dashboard.php            Dashboard admin (statistik + daftar produk)
    ├── tambah.php                Tambah produk + upload gambar
    ├── edit.php                  Edit produk + ganti gambar
    ├── hapus.php                 Hapus produk
    ├── pesanan.php                Data pesanan pelanggan
    ├── proses_status.php          Proses ubah status pesanan
    ├── logout.php                 Logout admin
    ├── header.php / footer.php    Template sidebar & layout admin
    └── style.css                  Style halaman admin
```

## 6. Catatan Keamanan (untuk pengembangan lebih lanjut)

- Password pelanggan dan admin disimpan menggunakan `password_hash()` (bcrypt), bukan plaintext.
- Semua query database menggunakan **Prepared Statement** untuk mencegah SQL Injection.
- Semua output ke HTML menggunakan `htmlspecialchars()` untuk mencegah XSS.
- Validasi upload gambar membatasi jenis file (`jpg`, `jpeg`, `png`, `webp`) dan ukuran maksimal 2MB.
- Untuk penggunaan produksi (bukan tugas sekolah), disarankan menambahkan CSRF token, rate limiting, dan HTTPS.

Selamat mencoba! 👟
