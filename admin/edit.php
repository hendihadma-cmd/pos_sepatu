<?php
require_once 'koneksi.php';
$judul_halaman = 'Edit Produk';
$halaman_aktif = 'produk';
require_once 'header.php';

$produk_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = '';

// Ambil data produk (tanpa ikut mengambil isi blob gambar_data yang berat,
// cukup cek apakah fotonya ada atau tidak lewat LENGTH())
$stmt = mysqli_prepare($koneksi, "SELECT id, nama_produk, harga, deskripsi, stok, kategori, LENGTH(gambar_data) AS ada_foto FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $produk_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$produk) {
    header('Location: produk.php');
    exit;
}

// Proses update jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $harga       = trim($_POST['harga']);
    $deskripsi   = trim($_POST['deskripsi']);
    $stok        = trim($_POST['stok']);
    $kategori    = trim($_POST['kategori']);
    $kategori_valid = ['Pria', 'Wanita', 'Anak-anak'];
    $ada_gambar_baru = false;
    $isi_gambar  = null;
    $tipe_gambar = null;

    if ($nama_produk === '' || $harga === '' || $stok === '') {
        $error = 'Nama produk, harga, dan stok wajib diisi.';
    } elseif (!is_numeric($harga) || $harga < 0) {
        $error = 'Harga harus berupa angka.';
    } elseif (!is_numeric($stok) || $stok < 0) {
        $error = 'Stok harus berupa angka.';
    } elseif (!in_array($kategori, $kategori_valid)) {
        $error = 'Kategori tidak valid.';
    } else {
        // Jika ada file gambar baru diupload, simpan ke database (menggantikan yang lama)
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
                    $error = 'Gagal membaca file gambar baru.';
                } else {
                    $ada_gambar_baru = true;
                }
            }
        }

        if ($error === '') {
            if ($ada_gambar_baru) {
                $stmt = mysqli_prepare($koneksi, "UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ?, gambar_data = ?, gambar_type = ?, stok = ?, kategori = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "sdsssisi", $nama_produk, $harga, $deskripsi, $isi_gambar, $tipe_gambar, $stok, $kategori, $produk_id);
            } else {
                // Tidak ganti foto - biarkan gambar_data/gambar_type tetap seperti sebelumnya
                $stmt = mysqli_prepare($koneksi, "UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ?, stok = ?, kategori = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "sdsisi", $nama_produk, $harga, $deskripsi, $stok, $kategori, $produk_id);
            }

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['pesan_admin'] = 'Produk berhasil diperbarui.';
                header('Location: produk.php');
                exit;
            } else {
                $error = 'Gagal memperbarui produk.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Supaya form tetap menampilkan data terbaru yang diinput jika ada error
    $produk['nama_produk'] = $nama_produk;
    $produk['harga']       = $harga;
    $produk['deskripsi']   = $deskripsi;
    $produk['stok']        = $stok;
    $produk['kategori']    = $kategori;
}
?>

<div class="card-stat">
    <h5 class="fw-bold mb-4">Edit Produk</h5>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $produk_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga (Rp)</label>
                <input type="number" step="0.01" name="harga" class="form-control" value="<?php echo htmlspecialchars($produk['harga']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Stok</label>
                <input type="number" name="stok" class="form-control" value="<?php echo htmlspecialchars($produk['stok']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-select" required>
                    <option value="Pria" <?php echo $produk['kategori'] === 'Pria' ? 'selected' : ''; ?>>Pria</option>
                    <option value="Wanita" <?php echo $produk['kategori'] === 'Wanita' ? 'selected' : ''; ?>>Wanita</option>
                    <option value="Anak-anak" <?php echo $produk['kategori'] === 'Anak-anak' ? 'selected' : ''; ?>>Anak-anak</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($produk['deskripsi'] ?? ''); ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Foto Produk Saat Ini</label><br>
                <?php if (!empty($produk['ada_foto'])): ?>
                    <img src="../gambar.php?id=<?php echo $produk['id']; ?>" class="mb-2" style="width:120px;height:120px;object-fit:cover;border-radius:10px;">
                <?php else: ?>
                    <p class="text-muted">Belum ada foto.</p>
                <?php endif; ?>
                <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti foto. Foto disimpan langsung di database.</small>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-dark-custom"><i class="bi bi-save"></i> Simpan Perubahan</button>
            <a href="produk.php" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
