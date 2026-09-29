<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$halaman_aktif = isset($halaman_aktif) ? $halaman_aktif : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($judul_halaman) ? $judul_halaman . ' - Admin Toko Sepatu' : 'Admin Toko Sepatu'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar-admin">
        <div class="sidebar-brand">
            <i class="bi bi-bag-heart-fill"></i> Admin Sepatu
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $halaman_aktif === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $halaman_aktif === 'produk' ? 'active' : ''; ?>" href="produk.php">
                    <i class="bi bi-box-seam"></i> Data Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $halaman_aktif === 'tambah' ? 'active' : ''; ?>" href="tambah.php">
                    <i class="bi bi-plus-circle"></i> Tambah Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $halaman_aktif === 'pesanan' ? 'active' : ''; ?>" href="pesanan.php">
                    <i class="bi bi-receipt"></i> Data Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $halaman_aktif === 'voucher' ? 'active' : ''; ?>" href="voucher.php">
                    <i class="bi bi-ticket-perforated"></i> Voucher
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content-admin flex-grow-1">
        <nav class="navbar navbar-light bg-white border-bottom px-4 mb-4">
            <span class="fw-bold"><?php echo isset($judul_halaman) ? $judul_halaman : ''; ?></span>
            <span class="text-muted">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </span>
        </nav>
        <div class="px-4 pb-5">
