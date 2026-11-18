<?php
/**
 * Pemroses Pembaruan Data Barang (Metode POST)
 * STMIK IKMI Cirebon - Pemrograman Web Dasar
 */

require_once __DIR__ . '/config/koneksi.php';

// 1. Validasi Metode Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode request tidak diizinkan. Silakan kirim data melalui form.');
}

// 2. Pembacaan & Sanitasi Input
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$nama = trim($_POST['nama_barang'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$jumlahInput = $_POST['jumlah'] ?? '';
$hargaInput = $_POST['harga'] ?? '';

// Validasi Keberadaan ID
if (!$id || $id < 1) {
    setFlash('danger', 'ID barang tidak valid.');
    header('Location: index.php');
    exit;
}

$errors = [];

// 3. Validasi Server-Side (Sesuai Modul Pertemuan 11)
if ($nama === '') {
    $errors[] = 'Nama barang wajib diisi.';
} elseif (mb_strlen($nama) > 100) {
    $errors[] = 'Nama barang maksimal 100 karakter.';
}

if (!in_array($kategori, $kategoriDiizinkan, true)) {
    $errors[] = 'Kategori tidak valid. Silakan pilih dari daftar kategori yang disediakan.';
}

$jumlah = filter_var($jumlahInput, FILTER_VALIDATE_INT);
if ($jumlah === false || $jumlah < 1) {
    $errors[] = 'Jumlah stok harus berupa bilangan bulat minimal 1.';
}

if ($hargaInput === '' || !is_numeric($hargaInput) || (float)$hargaInput < 0) {
    $errors[] = 'Harga satuan harus berupa angka dan tidak boleh bernilai negatif.';
} else {
    $harga = (float)$hargaInput;
}

// 4. Jika Ada Validasi yang Gagal, Kembalikan ke Form Edit
if (!empty($errors)) {
    $_SESSION['edit_errors'] = $errors;
    $_SESSION['edit_old_data'] = [
        'nama_barang' => $nama,
        'kategori' => $kategori,
        'jumlah' => $jumlahInput,
        'harga' => $hargaInput,
    ];
    header('Location: edit.php?id=' . $id);
    exit;
}

// 5. Update Database Menggunakan Prepared Statement
$sql = "UPDATE `barang` SET `nama_barang` = ?, `kategori` = ?, `jumlah` = ?, `harga` = ? WHERE `id` = ?";
$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssidi", $nama, $kategori, $jumlah, $harga, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        setFlash('success', 'Data barang #' . $id . ' ("' . $nama . '") berhasil diperbarui!');
        header('Location: index.php');
        exit;
    } else {
        $dbError = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['edit_errors'] = ['Gagal memperbarui data: ' . $dbError];
        header('Location: edit.php?id=' . $id);
        exit;
    }
} else {
    $_SESSION['edit_errors'] = ['Gagal menyiapkan query update: ' . mysqli_error($koneksi)];
    header('Location: edit.php?id=' . $id);
    exit;
}
