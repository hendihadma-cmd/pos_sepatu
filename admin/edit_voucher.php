<?php
require_once 'koneksi.php';
$judul_halaman = 'Edit Voucher';
$halaman_aktif = 'voucher';
require_once 'header.php';

$voucher_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = '';

$stmt = mysqli_prepare($koneksi, "SELECT * FROM voucher WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $voucher_id);
mysqli_stmt_execute($stmt);
$voucher = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$voucher) {
    header('Location: voucher.php');
    exit;
}

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
    } else {
        // Cek kode tidak dipakai voucher lain
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM voucher WHERE kode = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, "si", $kode, $voucher_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Kode voucher ini sudah digunakan voucher lain.';
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($koneksi, "UPDATE voucher SET kode = ?, jenis = ?, nilai = ?, min_belanja = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssddsi", $kode, $jenis, $nilai, $min_belanja, $status, $voucher_id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['pesan_admin'] = 'Voucher berhasil diperbarui.';
                header('Location: voucher.php');
                exit;
            } else {
                $error = 'Gagal memperbarui voucher.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    $voucher['kode']        = $kode;
    $voucher['jenis']       = $jenis;
    $voucher['nilai']       = $nilai;
    $voucher['min_belanja'] = $min_belanja;
    $voucher['status']      = $status;
}
?>

<div class="card-stat">
    <h5 class="fw-bold mb-4">Edit Voucher</h5>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit_voucher.php?id=<?php echo $voucher_id; ?>" method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Kode Voucher</label>
                <input type="text" name="kode" class="form-control text-uppercase" value="<?php echo htmlspecialchars($voucher['kode']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jenis Diskon</label>
                <select name="jenis" class="form-select" required>
                    <option value="persen" <?php echo $voucher['jenis'] === 'persen' ? 'selected' : ''; ?>>Persentase (%)</option>
                    <option value="nominal" <?php echo $voucher['jenis'] === 'nominal' ? 'selected' : ''; ?>>Nominal (Rp)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="Aktif" <?php echo $voucher['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Nonaktif" <?php echo $voucher['status'] === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nilai Diskon</label>
                <input type="number" step="0.01" name="nilai" class="form-control" value="<?php echo htmlspecialchars($voucher['nilai']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Minimal Belanja (Rp)</label>
                <input type="number" step="0.01" name="min_belanja" class="form-control" value="<?php echo htmlspecialchars($voucher['min_belanja']); ?>" required>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark-custom"><i class="bi bi-save"></i> Simpan Perubahan</button>
            <a href="voucher.php" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
