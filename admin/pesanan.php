<?php
require_once 'koneksi.php';
$judul_halaman = 'Data Pesanan';
$halaman_aktif = 'pesanan';
require_once 'header.php';

$query = "SELECT p.*, u.nama AS nama_user, u.no_hp
          FROM pesanan p
          JOIN users u ON p.user_id = u.id
          ORDER BY p.tanggal_pesan DESC";
$daftar_pesanan = mysqli_query($koneksi, $query);

$daftar_status = ['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

function warna_status_admin($status) {
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

<h5 class="fw-bold mb-3">Data Pesanan Pelanggan</h5>

<?php if (mysqli_num_rows($daftar_pesanan) === 0): ?>
    <div class="card-stat text-center text-muted py-4">Belum ada pesanan masuk.</div>
<?php else: ?>
    <?php while ($pesanan = mysqli_fetch_assoc($daftar_pesanan)): ?>
        <?php
        $stmt_detail = mysqli_prepare($koneksi, "SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
        mysqli_stmt_bind_param($stmt_detail, "i", $pesanan['id']);
        mysqli_stmt_execute($stmt_detail);
        $detail_items = mysqli_stmt_get_result($stmt_detail);
        ?>
        <div class="card-stat mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <strong>Pesanan #<?php echo $pesanan['id']; ?></strong> —
                    <?php echo htmlspecialchars($pesanan['nama_user']); ?>
                    (<?php echo htmlspecialchars($pesanan['no_hp']); ?>)
                    <span class="text-muted ms-2"><?php echo date('d-m-Y H:i', strtotime($pesanan['tanggal_pesan'])); ?></span>
                </div>
                <span class="badge badge-status <?php echo warna_status_admin($pesanan['status']); ?>"><?php echo htmlspecialchars($pesanan['status']); ?></span>
            </div>

            <div class="table-responsive">
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
            </div>
            <?php mysqli_stmt_close($stmt_detail); ?>

            <div class="row align-items-end">
                <div class="col-md-6">
                    <small class="text-muted">Alamat Pengiriman:</small>
                    <p class="mb-1"><?php echo htmlspecialchars($pesanan['alamat_pengiriman']); ?></p>
                    <small class="text-muted">Metode Pembayaran:</small>
                    <p class="mb-2"><span class="badge bg-dark"><?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?></span></p>
                    <div>Subtotal: Rp <?php echo number_format($pesanan['subtotal'], 0, ',', '.'); ?></div>
                    <?php if ($pesanan['diskon'] > 0): ?>
                        <div class="text-success">
                            Diskon<?php echo $pesanan['kode_voucher'] ? ' (' . htmlspecialchars($pesanan['kode_voucher']) . ')' : ''; ?>:
                            - Rp <?php echo number_format($pesanan['diskon'], 0, ',', '.'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="fw-bold">Total: Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></div>
                </div>
                <div class="col-md-6">
                    <form action="proses_status.php" method="POST" class="d-flex gap-2 justify-content-md-end">
                        <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                        <select name="status" class="form-select form-select-sm" style="max-width:220px;">
                            <?php foreach ($daftar_status as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $pesanan['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-dark-custom">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
