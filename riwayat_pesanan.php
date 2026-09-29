<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['pesan_info'] = 'Silakan login terlebih dahulu untuk melihat riwayat pesanan.';
    header('Location: login.php?redirect=' . urlencode('riwayat_pesanan.php'));
    exit;
}

$judul_halaman = 'Riwayat Pesanan';
require_once 'header.php';

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE user_id = ? ORDER BY tanggal_pesan DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$daftar_pesanan = mysqli_stmt_get_result($stmt);

function warna_status($status) {
    switch ($status) {
        case 'Menunggu Konfirmasi': return 'bg-secondary';
        case 'Diproses': return 'bg-warning text-dark';
        case 'Dikirim': return 'bg-info text-dark';
        case 'Selesai': return 'bg-success';
        case 'Dibatalkan': return 'bg-danger';
        default: return 'bg-secondary';
    }
}
?>

<div class="container my-5">
    <h4 class="section-title">Riwayat Pesanan Saya</h4>

    <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['pesan_sukses']); unset($_SESSION['pesan_sukses']); ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($daftar_pesanan) === 0): ?>
        <div class="alert alert-secondary text-center">Belum ada pesanan. <a href="index.php">Yuk mulai belanja!</a></div>
    <?php else: ?>
        <?php while ($pesanan = mysqli_fetch_assoc($daftar_pesanan)): ?>
            <?php
            $stmt_detail = mysqli_prepare($koneksi, "SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
            mysqli_stmt_bind_param($stmt_detail, "i", $pesanan['id']);
            mysqli_stmt_execute($stmt_detail);
            $detail_items = mysqli_stmt_get_result($stmt_detail);
            ?>
            <div class="card card-produk p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <strong>Pesanan #<?php echo $pesanan['id']; ?></strong>
                        <span class="text-muted ms-2"><?php echo date('d-m-Y H:i', strtotime($pesanan['tanggal_pesan'])); ?></span>
                    </div>
                    <span class="badge badge-status <?php echo warna_status($pesanan['status']); ?>"><?php echo htmlspecialchars($pesanan['status']); ?></span>
                </div>

                <table class="table table-sm align-middle mb-3">
                    <thead>
                        <tr><th>Produk</th><th>Ukuran</th><th>Jumlah</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($detail_items)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                <td><?php echo (int) $item['ukuran']; ?></td>
                                <td><?php echo (int) $item['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php mysqli_stmt_close($stmt_detail); ?>

                <div class="row">
                    <div class="col-md-7">
                        <small class="text-muted">Alamat Pengiriman:</small>
                        <p class="mb-1"><?php echo htmlspecialchars($pesanan['alamat_pengiriman']); ?></p>
                        <small class="text-muted">Metode Pembayaran:</small>
                        <p class="mb-0"><?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?></p>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <div>Subtotal: Rp <?php echo number_format($pesanan['subtotal'], 0, ',', '.'); ?></div>
                        <?php if ($pesanan['diskon'] > 0): ?>
                            <div class="text-success">
                                Diskon<?php echo $pesanan['kode_voucher'] ? ' (' . htmlspecialchars($pesanan['kode_voucher']) . ')' : ''; ?>:
                                - Rp <?php echo number_format($pesanan['diskon'], 0, ',', '.'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="fw-bold fs-5">Total: Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php
mysqli_stmt_close($stmt);
require_once 'footer.php';
?>
