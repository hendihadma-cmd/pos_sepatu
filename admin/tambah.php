<?php
require_once 'koneksi.php';
$judul_halaman = 'Tambah Produk';
$halaman_aktif = 'tambah';
require_once 'header.php';

$error = '';

// Proses simpan jika form dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $harga       = trim($_POST['harga']);
    $deskripsi   = trim($_POST['deskripsi']);
    $stok        = trim($_POST['stok']);
    $kategori    = trim($_POST['kategori']);
    $isi_gambar  = null;
    $tipe_gambar = null;
    $kategori_valid = ['Pria', 'Wanita', 'Anak-anak'];

    if ($nama_produk === '' || $harga === '' || $stok === '') {
        $error = 'Nama produk, harga, dan stok wajib diisi.';
    } elseif (!is_numeric($harga) || $harga < 0) {
        $error = 'Harga harus berupa angka.';
    } elseif (!is_numeric($stok) || $stok < 0) {
        $error = 'Stok harus berupa angka.';
    } elseif (!in_array($kategori, $kategori_valid)) {
        $error = 'Kategori tidak valid.';
    } else {
        // Upload gambar (opsional) - disimpan langsung ke database
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
            $nama_asli = $_FILES['gambar']['name'];
            $ekstensi  = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));

            if (!in_array($ekstensi, $ekstensi_diizinkan)) {
                $error = 'Format gambar harus JPG, JPEG, PNG, atau WEBP.';
            } elseif ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 2MB.';
            } else {
                $isi_gambar  = file_get_contents($_FILES['gambar']['tmp_name']);
                $tipe_gambar = mime_content_type($_FILES['gambar']['tmp_name']);

                if ($isi_gambar === false) {
                    $error = 'Gagal membaca file gambar yang diupload.';
                }
            }
        }

        if ($error === '') {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO produk (nama_produk, harga, deskripsi, gambar_data, gambar_type, stok, kategori) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sdsssis", $nama_produk, $harga, $deskripsi, $isi_gambar, $tipe_gambar, $stok, $kategori);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['pesan_admin'] = 'Produk baru berhasil ditambahkan.';
                header('Location: produk.php');
                exit;
            } else {
                $error = 'Gagal menyimpan produk ke database.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="card-stat">
    <h5 class="fw-bold mb-4">Tambah Produk Baru</h5>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="tambah.php" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" value="<?php echo isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : ''; ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga (Rp)</label>
                <input type="number" step="0.01" name="harga" class="form-control" value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Stok</label>
                <input type="number" name="stok" class="form-control" value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kategori</label>
                <?php $kategori_terpilih = isset($_POST['kategori']) ? $_POST['kategori'] : ''; ?>
                <select name="kategori" class="form-select" required>
                    <option value="" disabled <?php echo $kategori_terpilih === '' ? 'selected' : ''; ?>>-- Pilih Kategori --</option>
                    <option value="Pria" <?php echo $kategori_terpilih === 'Pria' ? 'selected' : ''; ?>>Pria</option>
                    <option value="Wanita" <?php echo $kategori_terpilih === 'Wanita' ? 'selected' : ''; ?>>Wanita</option>
                    <option value="Anak-anak" <?php echo $kategori_terpilih === 'Anak-anak' ? 'selected' : ''; ?>>Anak-anak</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3"><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Foto Produk</label>
                <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <small class="text-muted">Format JPG/PNG/WEBP, maksimal 2MB. Foto disimpan langsung di database (aman saat pindah komputer).</small>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark-custom"><i class="bi bi-save"></i> Simpan Produk</button>
            <a href="produk.php" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
