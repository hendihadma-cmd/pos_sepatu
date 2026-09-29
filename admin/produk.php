<?php
require_once 'koneksi.php';
$judul_halaman = 'Data Produk';
$halaman_aktif = 'produk';
require_once 'header.php';

$hasil_produk = mysqli_query($koneksi, "SELECT id, nama_produk, harga, deskripsi, stok, kategori, LENGTH(gambar_data) AS ada_foto FROM produk ORDER BY created_at DESC");

if (isset($_SESSION['pesan_admin'])) {
    $pesan_admin = $_SESSION['pesan_admin'];
    unset($_SESSION['pesan_admin']);
}
?>

<?php if (isset($pesan_admin)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($pesan_admin); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Data Produk</h5>
    <a href="tambah.php" class="btn btn-dark-custom btn-sm"><i class="bi bi-plus-circle"></i> Tambah Produk</a>
</div>

<div class="table-responsive">
    <table class="table table-admin bg-white align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Foto</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($hasil_produk) > 0): $no = 1; ?>
                <?php while ($p = mysqli_fetch_assoc($hasil_produk)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <img src="../gambar.php?id=<?php echo $p['id']; ?>" class="thumbnail-produk">
                        </td>
                        <td><?php echo htmlspecialchars($p['nama_produk']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['kategori']); ?></span></td>
                        <td>Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo (int) $p['stok']; ?></td>
                        <td><?php echo htmlspecialchars(mb_strimwidth($p['deskripsi'] ?? '', 0, 50, '...')); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
                            <a href="hapus.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus produk ini?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada produk. Silakan tambah produk baru.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
