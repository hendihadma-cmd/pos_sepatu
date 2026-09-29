<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

$produk_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($produk_id <= 0) {
    header('Location: index.php');
    exit;
}

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

$stok_habis = ((int) $produk['stok']) <= 0;

$judul_halaman = $produk['nama_produk'];
require_once 'header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="index.php?kategori=<?php echo urlencode($produk['kategori']); ?>"><?php echo htmlspecialchars($produk['kategori']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($produk['nama_produk']); ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['error_pesan'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_pesan']); unset($_SESSION['error_pesan']); ?></div>
    <?php endif; ?>

    <div class="row g-5">
        <div class="col-md-6">
            <div class="card card-produk">
                <img src="gambar.php?id=<?php echo $produk['id']; ?>" class="card-img-top" style="height:400px;" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
            </div>
        </div>

        <div class="col-md-6">
            <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($produk['kategori']); ?></span>
            <h2 class="fw-bold"><?php echo htmlspecialchars($produk['nama_produk']); ?></h2>
            <div class="harga-produk fs-3 mb-3">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>

            <?php if ($stok_habis): ?>
                <div class="alert alert-danger d-inline-flex align-items-center gap-2 py-2 px-3">
                    <i class="bi bi-x-circle-fill"></i> Stok Habis
                </div>
            <?php else: ?>
                <div class="alert alert-success d-inline-flex align-items-center gap-2 py-2 px-3">
                    <i class="bi bi-check-circle-fill"></i> Stok tersedia: <?php echo (int) $produk['stok']; ?>
                </div>
            <?php endif; ?>

            <p class="mt-3" style="color: var(--gray);"><?php echo nl2br(htmlspecialchars($produk['deskripsi'] ?? 'Tidak ada deskripsi untuk produk ini.')); ?></p>

            <hr class="my-4">

            <?php if ($stok_habis): ?>
                <div class="alert alert-secondary">
                    Maaf, stok produk ini sedang habis. Silakan cek produk lain atau kembali lagi nanti.
                </div>
                <a href="index.php" class="btn btn-outline-dark-custom w-100">
                    <i class="bi bi-arrow-left"></i> Lihat Produk Lain
                </a>
            <?php else: ?>
                <form action="proses_pesan.php" method="POST">
                    <input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Ukuran Sepatu</label>
                            <select name="ukuran" class="form-select" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <?php for ($ukuran = 36; $ukuran <= 44; $ukuran++): ?>
                                    <option value="<?php echo $ukuran; ?>"><?php echo $ukuran; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="1" min="1" max="<?php echo (int) $produk['stok']; ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark-custom w-100">
                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                    </button>
                </form>
                <a href="keranjang.php" class="btn btn-outline-dark-custom w-100 mt-2">
                    <i class="bi bi-bag-check"></i> Lihat Keranjang
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
