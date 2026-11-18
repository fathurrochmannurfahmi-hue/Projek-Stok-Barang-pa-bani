<?php
/**
 * Pemroses Hapus Data Barang
 * STMIK IKMI Cirebon - Pemrograman Web Dasar
 */

require_once __DIR__ . '/config/koneksi.php';

// Ambil ID dari GET (atau POST)
$id = filter_var($_GET['id'] ?? ($_POST['id'] ?? null), FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    setFlash('danger', 'ID barang tidak valid.');
    header('Location: index.php');
    exit;
}

// 1. Cek keberadaan barang terlebih dahulu untuk mendapatkan nama barang
$stmtCheck = mysqli_prepare($koneksi, "SELECT `nama_barang` FROM `barang` WHERE `id` = ?");
mysqli_stmt_bind_param($stmtCheck, "i", $id);
mysqli_stmt_execute($stmtCheck);
$resCheck = mysqli_stmt_get_result($stmtCheck);
$barang = mysqli_fetch_assoc($resCheck);
mysqli_stmt_close($stmtCheck);

if (!$barang) {
    setFlash('danger', 'Data barang tidak ditemukan atau telah dihapus sebelumnya.');
    header('Location: index.php');
    exit;
}

$namaBarang = $barang['nama_barang'];

// 2. Eksekusi Penghapusan Data Menggunakan Prepared Statement
$stmtDelete = mysqli_prepare($koneksi, "DELETE FROM `barang` WHERE `id` = ?");
mysqli_stmt_bind_param($stmtDelete, "i", $id);

if (mysqli_stmt_execute($stmtDelete)) {
    mysqli_stmt_close($stmtDelete);
    setFlash('success', 'Barang "' . $namaBarang . '" (ID #' . $id . ') berhasil dihapus dari sistem!');
} else {
    $dbError = mysqli_stmt_error($stmtDelete);
    mysqli_stmt_close($stmtDelete);
    setFlash('danger', 'Gagal menghapus barang: ' . $dbError);
}

header('Location: index.php');
exit;
