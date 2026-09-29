<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$judul_halaman = 'Login';
require_once 'header.php';

// Ambil pesan redirect (misal harus login dulu sebelum pesan)
$redirect_ke = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h3>Masuk Akun</h3>
        <p class="subtitle">Login untuk melanjutkan pemesanan sepatu.</p>

        <?php if (isset($_SESSION['pesan_info'])): ?>
            <div class="alert alert-warning py-2"><?php echo htmlspecialchars($_SESSION['pesan_info']); unset($_SESSION['pesan_info']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_login'])): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($_SESSION['error_login']); unset($_SESSION['error_login']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['sukses_registrasi'])): ?>
            <div class="alert alert-success py-2"><?php echo htmlspecialchars($_SESSION['sukses_registrasi']); unset($_SESSION['sukses_registrasi']); ?></div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_ke); ?>">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark-custom w-100">Login</button>
        </form>

        <p class="text-center mt-3 mb-0" style="font-size:0.9rem;">
            Belum punya akun? <a href="registrasi.php">Daftar di sini</a>
        </p>
    </div>
</div>

<?php require_once 'footer.php'; ?>
