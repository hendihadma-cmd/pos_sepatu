<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['pesan_info'] = 'Silakan login terlebih dahulu untuk checkout.';
    header('Location: login.php?redirect=' . urlencode('checkout.php'));
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Ambil isi keranjang
$stmt = mysqli_prepare($koneksi, "SELECT k.id, k.ukuran, k.jumlah, pr.id AS produk_id, pr.nama_produk, pr.harga, pr.stok
                                   FROM keranjang k
                                   JOIN produk pr ON k.produk_id = pr.id
                                   WHERE k.user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$item_keranjang = [];
$subtotal_belanja = 0;
while ($row = mysqli_fetch_assoc($hasil)) {
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $subtotal_belanja += $row['subtotal'];
    $item_keranjang[] = $row;
}
mysqli_stmt_close($stmt);

if (count($item_keranjang) === 0) {
    header('Location: keranjang.php');
    exit;
}

// Ambil data user untuk auto-isi alamat
$stmt = mysqli_prepare($koneksi, "SELECT nama, alamat, no_hp FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$data_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Cek voucher yang sedang dipakai (disimpan sementara di session)
$diskon = 0;
$kode_voucher_aktif = isset($_SESSION['voucher_aktif']) ? $_SESSION['voucher_aktif'] : null;

if ($kode_voucher_aktif) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM voucher WHERE kode = ? AND status = 'Aktif'");
    mysqli_stmt_bind_param($stmt, "s", $kode_voucher_aktif);
    mysqli_stmt_execute($stmt);
    $voucher = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($voucher && $subtotal_belanja >= $voucher['min_belanja']) {
        $diskon = $voucher['jenis'] === 'persen'
            ? ($subtotal_belanja * $voucher['nilai'] / 100)
            : $voucher['nilai'];
        $diskon = min($diskon, $subtotal_belanja); // diskon tidak boleh melebihi total belanja
    } else {
        // Voucher sudah tidak valid, batalkan
        unset($_SESSION['voucher_aktif']);
        $kode_voucher_aktif = null;
    }
}

$total_akhir = $subtotal_belanja - $diskon;

$judul_halaman = 'Checkout';
require_once 'header.php';
?>

<div class="container my-5">
    <h4 class="section-title">Checkout</h4>

    <?php if (isset($_SESSION['error_voucher'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_voucher']); unset($_SESSION['error_voucher']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['sukses_voucher'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['sukses_voucher']); unset($_SESSION['sukses_voucher']); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card card-produk p-4 mb-4">
                <h6 class="fw-bold mb-3">Ringkasan Pesanan</h6>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr><th>Produk</th><th>Ukuran</th><th>Jumlah</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($item_keranjang as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                <td><?php echo (int) $item['ukuran']; ?></td>
                                <td><?php echo (int) $item['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card card-produk p-4">
                <h6 class="fw-bold mb-3">Kode Voucher</h6>
                <form action="proses_voucher.php" method="POST" class="d-flex gap-2 mb-2">
                    <input type="text" name="kode_voucher" class="form-control" placeholder="Masukkan kode voucher" value="<?php echo htmlspecialchars($kode_voucher_aktif ?? ''); ?>">
                    <button type="submit" name="aksi" value="pakai" class="btn btn-dark-custom">Pakai</button>
                    <?php if ($kode_voucher_aktif): ?>
                        <button type="submit" name="aksi" value="batal" class="btn btn-outline-danger">Batal</button>
                    <?php endif; ?>
                </form>
                <?php if ($kode_voucher_aktif && $diskon > 0): ?>
                    <small class="text-success"><i class="bi bi-check-circle-fill"></i> Voucher "<?php echo htmlspecialchars($kode_voucher_aktif); ?>" berhasil dipakai.</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-produk p-4">
                <form action="proses_checkout.php" method="POST">
                    <h6 class="fw-bold mb-3">Alamat Pengiriman</h6>
                    <div class="mb-3">
                        <label class="form-label">Nama Penerima</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($data_user['nama'] ?? ''); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat_pengiriman" class="form-control" rows="3" required><?php echo htmlspecialchars($data_user['alamat'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($data_user['no_hp'] ?? ''); ?>" disabled>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3">Metode Pembayaran</h6>
                    <div class="mb-3">
                        <div class="form-check border rounded p-2 mb-2">
                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="metodeTransfer" value="Transfer Bank" checked>
                            <label class="form-check-label w-100" for="metodeTransfer">
                                <i class="bi bi-bank"></i> Transfer Bank
                            </label>
                        </div>
                        <div class="form-check border rounded p-2 mb-2">
                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="metodeCod" value="COD (Bayar di Tempat)">
                            <label class="form-check-label w-100" for="metodeCod">
                                <i class="bi bi-cash-stack"></i> COD (Bayar di Tempat)
                            </label>
                        </div>
                        <div class="form-check border rounded p-2">
                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="metodeEwallet" value="E-Wallet">
                            <label class="form-check-label w-100" for="metodeEwallet">
                                <i class="bi bi-wallet2"></i> E-Wallet
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span>
                        <span>Rp <?php echo number_format($subtotal_belanja, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 text-success">
                        <span>Diskon</span>
                        <span>- Rp <?php echo number_format($diskon, 0, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($total_akhir, 0, ',', '.'); ?></span>
                    </div>

                    <button type="submit" class="btn btn-dark-custom w-100">
                        <i class="bi bi-check2-circle"></i> Buat Pesanan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
