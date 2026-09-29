<?php
require_once 'koneksi.php';
$judul_halaman = 'Tambah Voucher';
$halaman_aktif = 'voucher';
require_once 'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode        = strtoupper(trim($_POST['kode']));
    $jenis       = $_POST['jenis'];
    $nilai       = trim($_POST['nilai']);
    $min_belanja = trim($_POST['min_belanja']);
    $status      = $_POST['status'];

    if ($kode === '' || $nilai === '' || $min_belanja === '') {
        $error = 'Kode, nilai, dan minimal belanja wajib diisi.';
    } elseif (!in_array($jenis, ['persen', 'nominal'])) {
        $error = 'Jenis voucher tidak valid.';
    } elseif (!is_numeric($nilai) || $nilai <= 0) {
        $error = 'Nilai voucher harus berupa angka lebih dari 0.';
    } elseif ($jenis === 'persen' && $nilai > 100) {
        $error = 'Nilai persentase tidak boleh lebih dari 100.';
    } elseif (!is_numeric($min_belanja) || $min_belanja < 0) {
        $error = 'Minimal belanja harus berupa angka.';
    } elseif (!in_array($status, ['Aktif', 'Nonaktif'])) {
        $error = 'Status tidak valid.';
    } else {
        // Cek kode voucher belum dipakai
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM voucher WHERE kode = ?");
        mysqli_stmt_bind_param($stmt, "s", $kode);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Kode voucher ini sudah digunakan.';
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($koneksi, "INSERT INTO voucher (kode, jenis, nilai, min_belanja, status) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssdds", $kode, $jenis, $nilai, $min_belanja, $status);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['pesan_admin'] = 'Voucher baru berhasil ditambahkan.';
                header('Location: voucher.php');
                exit;
            } else {
                $error = 'Gagal menyimpan voucher.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="card-stat">
    <h5 class="fw-bold mb-4">Tambah Voucher Baru</h5>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="tambah_voucher.php" method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Kode Voucher</label>
                <input type="text" name="kode" class="form-control text-uppercase" placeholder="Contoh: DISKON10" value="<?php echo isset($_POST['kode']) ? htmlspecialchars($_POST['kode']) : ''; ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jenis Diskon</label>
                <select name="jenis" class="form-select" required>
                    <option value="persen" <?php echo (isset($_POST['jenis']) && $_POST['jenis'] === 'persen') ? 'selected' : ''; ?>>Persentase (%)</option>
                    <option value="nominal" <?php echo (isset($_POST['jenis']) && $_POST['jenis'] === 'nominal') ? 'selected' : ''; ?>>Nominal (Rp)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="Aktif" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Nonaktif" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nilai Diskon</label>
                <input type="number" step="0.01" name="nilai" class="form-control" placeholder="Contoh: 10 (untuk 10% atau Rp 10)" value="<?php echo isset($_POST['nilai']) ? htmlspecialchars($_POST['nilai']) : ''; ?>" required>
                <small class="text-muted">Jika jenis Persentase, isi angka 1-100. Jika Nominal, isi jumlah rupiah.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Minimal Belanja (Rp)</label>
                <input type="number" step="0.01" name="min_belanja" class="form-control" placeholder="Contoh: 100000" value="<?php echo isset($_POST['min_belanja']) ? htmlspecialchars($_POST['min_belanja']) : '0'; ?>" required>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark-custom"><i class="bi bi-save"></i> Simpan Voucher</button>
            <a href="voucher.php" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
