<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

// -----------------------------------------------------------
// AKSI: TAMBAH KE KERANJANG
// -----------------------------------------------------------
if ($aksi === 'tambah') {
    $produk_id = isset($_POST['produk_id']) ? (int) $_POST['produk_id'] : 0;
    $ukuran    = isset($_POST['ukuran']) ? (int) $_POST['ukuran'] : 0;
    $jumlah    = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;

    // Wajib login. Jika belum, arahkan ke login lalu kembali ke halaman detail produk.
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['pesan_info'] = 'Silakan login atau registrasi terlebih dahulu untuk berbelanja.';
        header('Location: login.php?redirect=' . urlencode('detail.php?id=' . $produk_id));
        exit;
    }

    if ($produk_id <= 0 || $ukuran < 36 || $ukuran > 44 || $jumlah <= 0) {
        $_SESSION['keranjang_error'] = 'Data tidak valid, silakan pilih ukuran dan jumlah dengan benar.';
        header('Location: detail.php?id=' . $produk_id);
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "SELECT stok FROM produk WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $produk_id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($hasil);
    mysqli_stmt_close($stmt);

    if (!$produk) {
        header('Location: index.php');
        exit;
    }

    $user_id = (int) $_SESSION['user_id'];

    // Cek apakah item (produk + ukuran) sudah ada di keranjang user ini
    $stmt = mysqli_prepare($koneksi, "SELECT id, jumlah FROM keranjang WHERE user_id = ? AND produk_id = ? AND ukuran = ?");
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $produk_id, $ukuran);
    mysqli_stmt_execute($stmt);
    $hasil_item = mysqli_stmt_get_result($stmt);
    $item_ada = mysqli_fetch_assoc($hasil_item);
    mysqli_stmt_close($stmt);

    $jumlah_baru = $jumlah + ($item_ada ? (int) $item_ada['jumlah'] : 0);

    if ($jumlah_baru > (int) $produk['stok']) {
        $_SESSION['keranjang_error'] = 'Jumlah melebihi stok yang tersedia (stok: ' . (int) $produk['stok'] . ').';
        header('Location: detail.php?id=' . $produk_id);
        exit;
    }

    if ($item_ada) {
        $stmt = mysqli_prepare($koneksi, "UPDATE keranjang SET jumlah = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $jumlah_baru, $item_ada['id']);
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO keranjang (user_id, produk_id, ukuran, jumlah) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiii", $user_id, $produk_id, $ukuran, $jumlah);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['keranjang_info'] = 'Produk berhasil ditambahkan ke keranjang.';
    header('Location: detail.php?id=' . $produk_id);
    exit;
}

// Aksi di bawah ini wajib login (diakses dari halaman keranjang.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = (int) $_SESSION['user_id'];

// -----------------------------------------------------------
// AKSI: UPDATE JUMLAH ITEM
// -----------------------------------------------------------
if ($aksi === 'update') {
    $keranjang_id = isset($_POST['keranjang_id']) ? (int) $_POST['keranjang_id'] : 0;
    $jumlah       = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;

    if ($keranjang_id > 0 && $jumlah > 0) {
        // Pastikan item ini milik user yang sedang login, dan cek stok
        $stmt = mysqli_prepare($koneksi, "SELECT k.id, pr.stok FROM keranjang k JOIN produk pr ON k.produk_id = pr.id WHERE k.id = ? AND k.user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $keranjang_id, $user_id);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        if ($item) {
            if ($jumlah > (int) $item['stok']) {
                $_SESSION['keranjang_error'] = 'Jumlah melebihi stok yang tersedia (stok: ' . (int) $item['stok'] . ').';
            } else {
                $stmt = mysqli_prepare($koneksi, "UPDATE keranjang SET jumlah = ? WHERE id = ? AND user_id = ?");
                mysqli_stmt_bind_param($stmt, "iii", $jumlah, $keranjang_id, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    header('Location: keranjang.php');
    exit;
}

// -----------------------------------------------------------
// AKSI: HAPUS ITEM
// -----------------------------------------------------------
if ($aksi === 'hapus') {
    $keranjang_id = isset($_POST['keranjang_id']) ? (int) $_POST['keranjang_id'] : 0;

    if ($keranjang_id > 0) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM keranjang WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $keranjang_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['keranjang_info'] = 'Item berhasil dihapus dari keranjang.';
    }

    header('Location: keranjang.php');
    exit;
}

header('Location: keranjang.php');
exit;
