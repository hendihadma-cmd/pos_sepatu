<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Toko Sepatu Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="auth-wrapper-admin">
    <div class="auth-card-admin">
        <div class="text-center mb-3">
            <i class="bi bi-shield-lock" style="font-size:2.2rem;"></i>
            <h4 class="fw-bold mt-2 mb-0">Login Admin</h4>
            <small class="text-muted">Toko Sepatu Online</small>
        </div>

        <?php if (isset($_SESSION['error_login_admin'])): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($_SESSION['error_login_admin']); unset($_SESSION['error_login_admin']); ?></div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark-custom w-100">Login</button>
        </form>

        <p class="text-center mt-3 mb-0">
            <a href="../index.php" class="text-muted" style="font-size:0.85rem;"><i class="bi bi-arrow-left"></i> Kembali ke Toko</a>
        </p>
    </div>
</div>

</body>
</html>
