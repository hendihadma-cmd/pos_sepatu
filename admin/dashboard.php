<?php
require_once 'koneksi.php';
$judul_halaman = 'Dashboard';
$halaman_aktif = 'dashboard';
require_once 'header.php';

// Statistik
$total_produk  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM produk"))['jml'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pesanan"))['jml'];
$total_user    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM users"))['jml'];
$pesanan_baru  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pesanan WHERE status = 'Menunggu Konfirmasi'"))['jml'];

// Produk dengan stok menipis (5 ke bawah)
$produk_stok_menipis = mysqli_query($koneksi, "SELECT nama_produk, stok FROM produk WHERE stok <= 5 ORDER BY stok ASC LIMIT 5");

// Notifikasi: jumlah produk yang stoknya benar-benar habis (0)
$daftar_stok_habis = mysqli_query($koneksi, "SELECT nama_produk FROM produk WHERE stok = 0 ORDER BY nama_produk ASC");
$nama_stok_habis = [];
while ($row = mysqli_fetch_assoc($daftar_stok_habis)) {
    $nama_stok_habis[] = $row['nama_produk'];
}

// Pesanan terbaru
$pesanan_terbaru = mysqli_query($koneksi, "SELECT p.id, p.status, p.total_harga, p.tanggal_pesan, u.nama AS nama_user,
                                                   COUNT(pd.id) AS jumlah_item
                                            FROM pesanan p
                                            JOIN users u ON p.user_id = u.id
                                            LEFT JOIN pesanan_detail pd ON pd.pesanan_id = p.id
                                            GROUP BY p.id
                                            ORDER BY p.tanggal_pesan DESC LIMIT 5");

function warna_status_dashboard($status) {
    switch ($status) {
        case 'Menunggu Konfirmasi': return 'bg-secondary';
        case 'Diproses': return 'bg-warning text-dark';
        case 'Dikirim': return 'bg-info text-dark';
        case 'Selesai': return 'bg-success';
        case 'Dibatalkan': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

if (isset($_SESSION['pesan_admin'])) {
    $pesan_admin = $_SESSION['pesan_admin'];
    unset($_SESSION['pesan_admin']);
}
?>

<?php if (isset($pesan_admin)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($pesan_admin); ?></div>
<?php endif; ?>

<?php if (count($nama_stok_habis) > 0): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>
            <strong><?php echo count($nama_stok_habis); ?> produk stok habis:</strong>
            <?php echo htmlspecialchars(implode(', ', $nama_stok_habis)); ?>.
            Segera <a href="produk.php" class="alert-link">tambah stok</a> supaya bisa dipesan lagi.
        </div>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <i class="bi bi-box-seam"></i>
            <div>
                <div class="angka"><?php echo $total_produk; ?></div>
                <div class="label">Total Produk</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <i class="bi bi-receipt"></i>
            <div>
                <div class="angka"><?php echo $total_pesanan; ?></div>
                <div class="label">Total Pesanan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <i class="bi bi-people"></i>
            <div>
                <div class="angka"><?php echo $total_user; ?></div>
                <div class="label">Total Pelanggan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-stat d-flex align-items-center gap-3">
            <i class="bi bi-hourglass-split"></i>
            <div>
                <div class="angka"><?php echo $pesanan_baru; ?></div>
                <div class="label">Menunggu Konfirmasi</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-stat">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Pesanan Terbaru</h6>
                <a href="pesanan.php" class="small">Lihat Semua &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Jumlah Item</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($pesanan_terbaru) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_user']); ?></td>
                                    <td><?php echo (int) $row['jumlah_item']; ?> produk</td>
                                    <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                    <td><span class="badge badge-status <?php echo warna_status_dashboard($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pesanan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-stat">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Stok Menipis</h6>
                <a href="produk.php" class="small">Lihat Semua &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($produk_stok_menipis) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($produk_stok_menipis)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                    <td><span class="badge <?php echo $row['stok'] == 0 ? 'bg-danger' : 'bg-warning text-dark'; ?>"><?php echo $row['stok'] == 0 ? 'HABIS' : (int) $row['stok']; ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">Stok semua produk aman.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
