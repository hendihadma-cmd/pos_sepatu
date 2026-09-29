<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

// 1. Ambil ID produk
$produk_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($produk_id <= 0) {
    header('Location: index.php');
    exit;
}

// 2. Cek apakah user sudah login. Jika belum, arahkan ke login
//    dan setelah login kembali lagi ke halaman pesan ini.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['pesan_info'] = 'Silakan login atau registrasi terlebih dahulu untuk melakukan pemesanan.';
    header('Location: login.php?redirect=' . urlencode('pesan.php?id=' . $produk_id));
    exit;
}

// 3. Ambil data produk
$stmt = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $produk_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$produk) {
    header('Location: index.php');
    exit;
}

$judul_halaman = 'Pemesanan';
require_once 'header.php';
?>

<div class="container my-5">
    <h4 class="section-title">Pilih Ukuran &amp; Jumlah</h4>

    <?php if (isset($_SESSION['error_pesan'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_pesan']); unset($_SESSION['error_pesan']); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card card-produk">
                <img src="gambar.php?id=<?php echo $produk['id']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                <div class="card-body">
                    <div class="nama-produk"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                    <div class="harga-produk">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                    <div class="deskripsi-produk"><?php echo nl2br(htmlspecialchars($produk['deskripsi'] ?? '')); ?></div>
                    <small class="text-muted">Stok tersedia: <?php echo (int) $produk['stok']; ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-produk p-4">
                <?php if ((int) $produk['stok'] <= 0): ?>
                    <div class="alert alert-danger mb-3"><i class="bi bi-x-circle-fill"></i> Maaf, stok produk ini sedang habis.</div>
                    <a href="index.php" class="btn btn-outline-dark-custom w-100">Lihat Produk Lain</a>
                <?php else: ?>
                <form action="proses_pesan.php" method="POST">
                    <input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label">Ukuran Sepatu</label>
                        <select name="ukuran" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Ukuran --</option>
                            <?php for ($ukuran = 36; $ukuran <= 44; $ukuran++): ?>
                                <option value="<?php echo $ukuran; ?>"><?php echo $ukuran; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" value="1" min="1" max="<?php echo (int) $produk['stok']; ?>" required>
                    </div>

                    <div class="alert alert-light border">
                        Produk akan dimasukkan ke keranjang belanja. Kamu bisa memesan produk lain sebelum checkout.
                    </div>

                    <button type="submit" class="btn btn-dark-custom w-100">
                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                    </button>
                    <a href="keranjang.php" class="btn btn-outline-dark-custom w-100 mt-2">
                        <i class="bi bi-bag-check"></i> Lihat Keranjang
                    </a>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
