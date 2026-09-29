<?php
require_once 'koneksi.php';
$judul_halaman = 'Data Voucher';
$halaman_aktif = 'voucher';
require_once 'header.php';

$hasil_voucher = mysqli_query($koneksi, "SELECT * FROM voucher ORDER BY created_at DESC");

if (isset($_SESSION['pesan_admin'])) {
    $pesan_admin = $_SESSION['pesan_admin'];
    unset($_SESSION['pesan_admin']);
}
?>

<?php if (isset($pesan_admin)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($pesan_admin); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Data Voucher</h5>
    <a href="tambah_voucher.php" class="btn btn-dark-custom btn-sm"><i class="bi bi-plus-circle"></i> Tambah Voucher</a>
</div>

<div class="table-responsive">
    <table class="table table-admin bg-white align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Kode</th>
                <th>Jenis</th>
                <th>Nilai</th>
                <th>Minimal Belanja</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($hasil_voucher) > 0): $no = 1; ?>
                <?php while ($v = mysqli_fetch_assoc($hasil_voucher)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><span class="badge bg-dark"><?php echo htmlspecialchars($v['kode']); ?></span></td>
                        <td><?php echo $v['jenis'] === 'persen' ? 'Persentase' : 'Nominal'; ?></td>
                        <td><?php echo $v['jenis'] === 'persen' ? (int) $v['nilai'] . '%' : 'Rp ' . number_format($v['nilai'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($v['min_belanja'], 0, ',', '.'); ?></td>
                        <td><span class="badge <?php echo $v['status'] === 'Aktif' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo htmlspecialchars($v['status']); ?></span></td>
                        <td>
                            <a href="edit_voucher.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
                            <a href="hapus_voucher.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus voucher ini?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada voucher. Silakan tambah voucher baru.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
