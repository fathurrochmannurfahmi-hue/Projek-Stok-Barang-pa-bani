<?php
/**
 * Form Edit Data Barang
 * STMIK IKMI Cirebon - Pemrograman Web Dasar
 */

require_once __DIR__ . '/config/koneksi.php';

// Validasi Parameter ID dari URL (Metode GET)
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    setFlash('danger', 'ID barang tidak valid atau tidak disertakan.');
    header('Location: index.php');
    exit;
}

// Ambil data barang dari database menggunakan Prepared Statement
$stmt = mysqli_prepare($koneksi, "SELECT * FROM `barang` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$barang = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Jika data tidak ditemukan
if (!$barang) {
    setFlash('danger', 'Data barang dengan ID #' . $id . ' tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Cek apakah ada error dari percobaan update sebelumnya
$errors = $_SESSION['edit_errors'] ?? [];
$oldData = $_SESSION['edit_old_data'] ?? [];
unset($_SESSION['edit_errors'], $_SESSION['edit_old_data']);

// Nilai default untuk form (gunakan old data jika ada error, atau data dari database)
$namaVal = $oldData['nama_barang'] ?? $barang['nama_barang'];
$kategoriVal = $oldData['kategori'] ?? $barang['kategori'];
$jumlahVal = $oldData['jumlah'] ?? $barang['jumlah'];
$hargaVal = $oldData['harga'] ?? (int)$barang['harga'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Barang #<?= (int)$barang['id'] ?> - Manajemen Stok</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container" style="max-width: 680px;">
    <!-- Tombol Kembali -->
    <div style="margin-bottom: 20px;">
        <a href="index.php" class="btn btn-outline btn-sm">⬅️ Kembali ke Daftar Stok</a>
    </div>

    <div class="card">
        <div class="form-header">
            <h2>✏️ Edit Data Stok Barang</h2>
            <p>Perbarui informasi barang <strong>#<?= (int)$barang['id'] ?> - <?= e($barang['nama_barang']) ?></strong>.</p>
        </div>

        <!-- Tampilkan Pesan Error Server-side jika ada -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Gagal memperbarui data:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form Edit menggunakan Metode POST -->
        <form action="update.php" method="POST" id="formEdit">
            <!-- Hidden Input ID -->
            <input type="hidden" name="id" value="<?= (int)$barang['id'] ?>">

            <!-- 1. Nama Barang -->
            <div class="form-group">
                <label for="nama_barang">
                    Nama Barang <span class="req">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama_barang" 
                    name="nama_barang" 
                    class="form-control" 
                    maxlength="100" 
                    value="<?= e($namaVal) ?>" 
                    required 
                    autofocus
                >
                <div class="form-hint">Maksimal 100 karakter.</div>
            </div>

            <!-- 2. Kategori -->
            <div class="form-group">
                <label for="kategori">
                    Kategori Barang <span class="req">*</span>
                </label>
                <select id="kategori" name="kategori" class="form-control" required>
                    <option value="">-- Pilih kategori --</option>
                    <?php foreach ($kategoriDiizinkan as $kat): ?>
                        <option value="<?= e($kat) ?>" <?= $kategoriVal === $kat ? 'selected' : '' ?>>
                            <?= e($kat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Pilih salah satu kategori resmi.</div>
            </div>

            <!-- 3. Jumlah Stok -->
            <div class="form-group">
                <label for="jumlah">
                    Jumlah Stok (Unit) <span class="req">*</span>
                </label>
                <input 
                    type="number" 
                    id="jumlah" 
                    name="jumlah" 
                    class="form-control" 
                    min="1" 
                    step="1" 
                    value="<?= e($jumlahVal) ?>" 
                    required
                >
                <div class="form-hint">Harus berupa bilangan bulat minimal 1 unit.</div>
            </div>

            <!-- 4. Harga Satuan -->
            <div class="form-group">
                <label for="harga">
                    Harga Satuan (Rupiah) <span class="req">*</span>
                </label>
                <input 
                    type="number" 
                    id="harga" 
                    name="harga" 
                    class="form-control" 
                    min="0" 
                    step="1" 
                    value="<?= e($hargaVal) ?>" 
                    required
                >
                <div class="form-hint">Harga per unit dalam Rupiah (tidak boleh negatif).</div>
            </div>

            <!-- Live Preview Subtotal Nilai Stok -->
            <div class="subtotal-preview">
                <span class="subtotal-label">Estimasi Subtotal Nilai Stok:</span>
                <span class="subtotal-amount" id="previewSubtotal">Rp 0</span>
            </div>

            <!-- Tombol Aksi -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Live Kalkulator Subtotal Nilai Stok
    const inputJumlah = document.getElementById('jumlah');
    const inputHarga = document.getElementById('harga');
    const previewSubtotal = document.getElementById('previewSubtotal');

    function hitungSubtotal() {
        const jumlah = parseInt(inputJumlah.value) || 0;
        const harga = parseFloat(inputHarga.value) || 0;
        const total = Math.max(0, jumlah * harga);
        previewSubtotal.textContent = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(total);
    }

    inputJumlah.addEventListener('input', hitungSubtotal);
    inputHarga.addEventListener('input', hitungSubtotal);
    hitungSubtotal();
</script>

</body>
</html>
