<?php
require_once 'koneksi.php';
$judul_halaman = 'Beranda';
require_once 'header.php';

$kategori_dipilih = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$kategori_valid = ['Pria', 'Wanita', 'Anak-anak'];

if (in_array($kategori_dipilih, $kategori_valid)) {
    $stmt_produk = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE kategori = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt_produk, "s", $kategori_dipilih);
    mysqli_stmt_execute($stmt_produk);
    $hasil = mysqli_stmt_get_result($stmt_produk);
} else {
    $kategori_dipilih = '';
    $query = "SELECT * FROM produk ORDER BY created_at DESC";
    $hasil = mysqli_query($koneksi, $query);
}
?>

<div class="hero-section text-center">
    <div class="container">
        <h1>Koleksi Sepatu Terbaik untuk Setiap Langkahmu</h1>
        <p class="mb-0">Nyaman, berkualitas, dan siap dikirim ke seluruh Indonesia.</p>
    </div>
</div>

<div class="container mb-5">
    <h4 class="section-title">Daftar Produk</h4>

    <?php
    $label_kategori = $kategori_dipilih === '' ? 'Semua Kategori' : $kategori_dipilih;
    ?>
    <div class="mb-4 dropdown">
        <button class="btn btn-dark-custom dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-funnel"></i> Kategori: <?php echo htmlspecialchars($label_kategori); ?>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item <?php echo $kategori_dipilih === '' ? 'active' : ''; ?>" href="index.php">Semua</a></li>
            <li><a class="dropdown-item <?php echo $kategori_dipilih === 'Pria' ? 'active' : ''; ?>" href="index.php?kategori=Pria">Pria</a></li>
            <li><a class="dropdown-item <?php echo $kategori_dipilih === 'Wanita' ? 'active' : ''; ?>" href="index.php?kategori=Wanita">Wanita</a></li>
            <li><a class="dropdown-item <?php echo $kategori_dipilih === 'Anak-anak' ? 'active' : ''; ?>" href="index.php?kategori=Anak-anak">Anak-anak</a></li>
        </ul>
    </div>

    <?php if (isset($_SESSION['pesan_info'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['pesan_info']); unset($_SESSION['pesan_info']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if ($hasil && mysqli_num_rows($hasil) > 0): ?>
            <?php while ($produk = mysqli_fetch_assoc($hasil)): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card card-produk">
                        <a href="detail.php?id=<?php echo $produk['id']; ?>" class="position-relative">
                            <img src="gambar.php?id=<?php echo $produk['id']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                            <?php if ((int) $produk['stok'] <= 0): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Stok Habis</span>
                            <?php endif; ?>
                        </a>
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2" style="width:fit-content;"><?php echo htmlspecialchars($produk['kategori']); ?></span>
                            <div class="nama-produk"><a href="detail.php?id=<?php echo $produk['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($produk['nama_produk']); ?></a></div>
                            <div class="harga-produk">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                            <div class="deskripsi-produk"><?php echo htmlspecialchars(mb_strimwidth($produk['deskripsi'] ?? '', 0, 90, '...')); ?></div>
                            <?php if ((int) $produk['stok'] <= 0): ?>
                                <button class="btn btn-outline-secondary w-100" disabled>Stok Habis</button>
                            <?php else: ?>
                                <a href="pesan.php?id=<?php echo $produk['id']; ?>" class="btn btn-dark-custom w-100">
                                    <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-secondary text-center">Belum ada produk tersedia saat ini.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
