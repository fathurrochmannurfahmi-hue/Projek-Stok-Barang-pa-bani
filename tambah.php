<?php
/**
 * Form Tambah Stok Barang
 * STMIK IKMI Cirebon - Pemrograman Web Dasar
 */

require_once __DIR__ . '/config/koneksi.php';

// Ambil error & data lama jika ada pengalihan validasi gagal dari simpan.php
$errors = $_SESSION['form_errors'] ?? [];
$oldData = $_SESSION['form_old_data'] ?? [];

unset($_SESSION['form_errors'], $_SESSION['form_old_data']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Barang - Manajemen Stok</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container" style="max-width: 680px;">
    <!-- Header Form -->
    <div style="margin-bottom: 20px;">
        <a href="index.php" class="btn btn-outline btn-sm">⬅️ Kembali ke Daftar Stok</a>
    </div>

    <div class="card">
        <div class="form-header">
            <h2>➕ Form Input Penerimaan Stok Barang</h2>
            <p>Masukkan data barang baru yang akan diterima ke dalam gudang toko.</p>
        </div>

        <!-- Tampilkan Ringkasan Error Server-side (jika ada) -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Data belum dapat disimpan:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form Input Utama menggunakan Metode POST -->
        <form action="simpan.php" method="POST" id="formBarang">
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
                    placeholder="Contoh: Mouse Wireless Logitech M170" 
                    value="<?= e($oldData['nama_barang'] ?? '') ?>" 
                    required 
                    autofocus
                >
                <div class="form-hint">Maksimal 100 karakter. Wajib diisi tanpa hanya spasi.</div>
            </div>

            <!-- 2. Kategori -->
            <div class="form-group">
                <label for="kategori">
                    Kategori Barang <span class="req">*</span>
                </label>
                <select id="kategori" name="kategori" class="form-control" required>
                    <option value="">-- Pilih kategori --</option>
                    <?php 
                    $selectedKategori = $oldData['kategori'] ?? '';
                    foreach ($kategoriDiizinkan as $kat): 
                    ?>
                        <option value="<?= e($kat) ?>" <?= $selectedKategori === $kat ? 'selected' : '' ?>>
                            <?= e($kat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Pilih salah satu kategori resmi dari daftar.</div>
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
                    placeholder="Contoh: 10" 
                    value="<?= e($oldData['jumlah'] ?? '') ?>" 
                    required
                >
                <div class="form-hint">Harus berupa bilangan bulat positif (minimal 1 unit).</div>
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
                    placeholder="Contoh: 125000" 
                    value="<?= e($oldData['harga'] ?? '') ?>" 
                    required
                >
                <div class="form-hint">Harga per unit dalam Rupiah (angka tidak boleh negatif).</div>
            </div>

            <!-- Live Preview Subtotal Nilai Stok -->
            <div class="subtotal-preview">
                <span class="subtotal-label">Estimasi Subtotal Nilai Stok:</span>
                <span class="subtotal-amount" id="previewSubtotal">Rp 0</span>
            </div>

            <!-- Tombol Aksi -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Simpan Data Barang</button>
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
