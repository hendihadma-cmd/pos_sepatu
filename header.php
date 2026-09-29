<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$sudah_login = isset($_SESSION['user_id']);
$jumlah_keranjang = 0;
if ($sudah_login) {
    require_once __DIR__ . '/koneksi.php';
    $stmt_kr = mysqli_prepare($koneksi, "SELECT COALESCE(SUM(jumlah), 0) AS total FROM keranjang WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt_kr, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_kr);
    $jumlah_keranjang = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_kr))['total'];
    mysqli_stmt_close($stmt_kr);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($judul_halaman) ? $judul_halaman . ' - TOKO SEPATU ONLINE' : 'TOKO SEPATU ONLINE'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-toko sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-bag-heart-fill"></i> TOKO SEPATU ONLINE</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToko">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarToko">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Beranda</a>
                </li>
                <?php if ($sudah_login): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="keranjang.php">
                            <i class="bi bi-cart3"></i> Keranjang
                            <?php if ($jumlah_keranjang > 0): ?>
                                <span class="badge rounded-pill bg-danger ms-1"><?php echo $jumlah_keranjang; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat_pesanan.php">Riwayat Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-light-emphasis">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_nama']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-lg-2" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-lg-2" href="registrasi.php">Registrasi</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
