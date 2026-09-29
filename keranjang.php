<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['pesan_info'] = 'Silakan login terlebih dahulu untuk melihat keranjang.';
    header('Location: login.php?redirect=' . urlencode('keranjang.php'));
    exit;
}

$judul_halaman = 'Keranjang Belanja';
require_once 'header.php';

$user_id = (int) $_SESSION['user_id'];

$query = "SELECT k.id, k.ukuran, k.jumlah, pr.id AS produk_id, pr.nama_produk, pr.harga, pr.stok
          FROM keranjang k
          JOIN produk pr ON k.produk_id = pr.id
          WHERE k.user_id = ?
          ORDER BY k.created_at DESC";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$item_keranjang = [];
$total_belanja = 0;
while ($row = mysqli_fetch_assoc($hasil)) {
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $total_belanja += $row['subtotal'];
    $item_keranjang[] = $row;
}
?>

<div class="container my-5">
    <h4 class="section-title">Keranjang Belanja Saya</h4>

    <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['pesan_sukses']); unset($_SESSION['pesan_sukses']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_keranjang'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_keranjang']); unset($_SESSION['error_keranjang']); ?></div>
    <?php endif; ?>

    <?php if (count($item_keranjang) === 0): ?>
        <div class="alert alert-secondary text-center">
            Keranjang belanja kamu masih kosong. <a href="index.php">Yuk mulai belanja!</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-toko align-middle bg-white">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Ukuran</th>
                        <th>Harga Satuan</th>
                        <th style="width:160px;">Jumlah</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($item_keranjang as $item): ?>
                        <tr>
                            <td class="d-flex align-items-center gap-2">
                                <img src="gambar.php?id=<?php echo $item['produk_id']; ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                <span><?php echo htmlspecialchars($item['nama_produk']); ?></span>
                            </td>
                            <td><?php echo (int) $item['ukuran']; ?></td>
                            <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                            <td>
                                <form action="proses_update_keranjang.php" method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="keranjang_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="aksi" value="update">
                                    <input type="number" name="jumlah" value="<?php echo (int) $item['jumlah']; ?>" min="1" max="<?php echo (int) $item['stok']; ?>" class="form-control form-control-sm" style="width:70px;">
                                    <button type="submit" class="btn btn-sm btn-outline-dark-custom">Ubah</button>
                                </form>
                            </td>
                            <td class="fw-semibold">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            <td>
                                <form action="proses_update_keranjang.php" method="POST" onsubmit="return confirm('Hapus produk ini dari keranjang?');">
                                    <input type="hidden" name="keranjang_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            <div class="card card-produk p-4" style="max-width:380px; width:100%;">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Belanja</span>
                    <span class="fw-bold">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></span>
                </div>
                <a href="checkout.php" class="btn btn-dark-custom w-100 mt-2">
                    <i class="bi bi-bag-check-fill"></i> Lanjut ke Checkout
                </a>
                <a href="index.php" class="btn btn-outline-dark-custom w-100 mt-2">Tambah Produk Lain</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
mysqli_stmt_close($stmt);
require_once 'footer.php';
?>
