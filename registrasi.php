<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$judul_halaman = 'Registrasi';
require_once 'header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h3>Buat Akun Baru</h3>
        <p class="subtitle">Daftar untuk mulai berbelanja sepatu favoritmu.</p>

        <?php if (isset($_SESSION['error_registrasi'])): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($_SESSION['error_registrasi']); unset($_SESSION['error_registrasi']); ?></div>
        <?php endif; ?>

        <form action="proses_registrasi.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" minlength="6" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="konfirmasi_password" class="form-control" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-dark-custom w-100">Daftar</button>
        </form>

        <p class="text-center mt-3 mb-0" style="font-size:0.9rem;">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </p>
    </div>
</div>

<?php require_once 'footer.php'; ?>
