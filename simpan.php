<?php
/**
 * Pemroses Simpan Data Barang (Metode POST)
 * STMIK IKMI Cirebon - Pemrograman Web Dasar
 */

require_once __DIR__ . '/config/koneksi.php';

// 1. Validasi Metode Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode request tidak diizinkan. Silakan kirim data melalui form.');
}

// 2. Pembacaan & Normalisasi Data
$errors = [];
$nama = trim($_POST['nama_barang'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$jumlahInput = $_POST['jumlah'] ?? '';
$hargaInput = $_POST['harga'] ?? '';

// 3. Validasi Server-Side (Sesuai Standar Modul Pertemuan 11)
// Validasi Nama Barang
if ($nama === '') {
    $errors[] = 'Nama barang wajib diisi.';
} elseif (mb_strlen($nama) > 100) {
    $errors[] = 'Nama barang maksimal 100 karakter.';
}

// Validasi Kategori
if (!in_array($kategori, $kategoriDiizinkan, true)) {
    $errors[] = 'Kategori tidak valid. Silakan pilih kategori dari daftar yang tersedia.';
}

// Validasi Jumlah Stok
$jumlah = filter_var($jumlahInput, FILTER_VALIDATE_INT);
if ($jumlah === false || $jumlah < 1) {
    $errors[] = 'Jumlah stok harus berupa bilangan bulat minimal 1.';
}

// Validasi Harga Satuan
if ($hargaInput === '' || !is_numeric($hargaInput) || (float)$hargaInput < 0) {
    $errors[] = 'Harga satuan harus berupa angka dan tidak boleh negatif.';
} else {
    $harga = (float)$hargaInput;
}

// 4. Jika Ada Error Validasi, Kembalikan ke Form Tambah
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old_data'] = [
        'nama_barang' => $nama,
        'kategori' => $kategori,
        'jumlah' => $jumlahInput,
        'harga' => $hargaInput,
    ];
    header('Location: tambah.php');
    exit;
}

// 5. Eksekusi Penyimpanan ke Database Menggunakan Prepared Statement
$sql = "INSERT INTO `barang` (`nama_barang`, `kategori`, `jumlah`, `harga`) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($koneksi, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssid", $nama, $kategori, $jumlah, $harga);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        setFlash('success', 'Barang "' . $nama . '" berhasil disimpan ke dalam stok!');
        header('Location: index.php');
        exit;
    } else {
        $dbError = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['form_errors'] = ['Gagal menyimpan ke database: ' . $dbError];
        $_SESSION['form_old_data'] = $_POST;
        header('Location: tambah.php');
        exit;
    }
} else {
    $_SESSION['form_errors'] = ['Gagal menyiapkan query database: ' . mysqli_error($koneksi)];
    $_SESSION['form_old_data'] = $_POST;
    header('Location: tambah.php');
    exit;
}
