<?php
/**
 * Halaman Utama: Daftar Stok Barang
 * STMIK IKMI Cirebon - Pemrograman Web Dasar
 */

require_once __DIR__ . '/config/koneksi.php';

// Ambil parameter filter & pencarian melalui metode GET
$keyword = trim($_GET['keyword'] ?? '');
$filterKategori = trim($_GET['kategori'] ?? '');

// Bangun query pencarian menggunakan Prepared Statement
$sql = "SELECT * FROM `barang` WHERE 1=1";
$params = [];
$types = "";

if ($keyword !== '') {
    $sql .= " AND `nama_barang` LIKE ?";
    $params[] = "%" . $keyword . "%";
    $types .= "s";
}

if ($filterKategori !== '' && in_array($filterKategori, $kategoriDiizinkan, true)) {
    $sql .= " AND `kategori` = ?";
    $params[] = $filterKategori;
    $types .= "s";
}

$sql .= " ORDER BY `id` DESC";

$stmt = mysqli_prepare($koneksi, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$barangList = [];
$totalUnit = 0;
$totalNilai = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $barangList[] = $row;
    $totalUnit += (int)$row['jumlah'];
    $totalNilai += ((int)$row['jumlah'] * (float)$row['harga']);
}

$totalJenis = count($barangList);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Manajemen Stok Barang</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <!-- Header Aplikasi -->
    <header class="app-header">
        <div class="app-title">
            <h1>📦 Sistem Manajemen Stok Barang</h1>
            <p>Praktikum Pemrograman Web Dasar — STMIK IKMI Cirebon</p>
        </div>
        <div>
            <a href="tambah.php" class="btn btn-success">➕ Tambah Barang Baru</a>
        </div>
    </header>

    <!-- Notifikasi Flash Message -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
            <strong><?= $flash['type'] === 'success' ? 'Berhasil!' : 'Perhatian!' ?></strong>
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Kartu Statistik -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Macam Barang</span>
            <span class="stat-value"><?= number_format($totalJenis, 0, ',', '.') ?> Item</span>
        </div>
        <div class="stat-card stat-green">
            <span class="stat-label">Total Stok Fisik</span>
            <span class="stat-value"><?= number_format($totalUnit, 0, ',', '.') ?> Unit</span>
        </div>
        <div class="stat-card stat-amber">
            <span class="stat-label">Total Nilai Inventaris</span>
            <span class="stat-value"><?= formatRupiah($totalNilai) ?></span>
        </div>
    </div>

    <!-- Konten Utama: Tabel Stok Barang -->
    <div class="card">
        <!-- Toolbar Pencarian & Filter (Metode GET) -->
        <div class="toolbar">
            <form action="index.php" method="GET" class="search-form">
                <input type="text" name="keyword" placeholder="Cari nama barang..." value="<?= e($keyword) ?>">
                <select name="kategori">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($kategoriDiizinkan as $kat): ?>
                        <option value="<?= e($kat) ?>" <?= $filterKategori === $kat ? 'selected' : '' ?>>
                            <?= e($kat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                <?php if ($keyword !== '' || $filterKategori !== ''): ?>
                    <a href="index.php" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>

            <div>
                <small style="color: var(--text-muted);">
                    Menampilkan <strong><?= count($barangList) ?></strong> data
                </small>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama Barang</th>
                        <th style="width: 140px;">Kategori</th>
                        <th style="width: 120px; text-align: right;">Jumlah Stok</th>
                        <th style="width: 150px; text-align: right;">Harga Satuan</th>
                        <th style="width: 170px; text-align: right;">Total Nilai</th>
                        <th style="width: 140px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barangList)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <strong>Tidak ada data barang yang ditemukan.</strong>
                                <?php if ($keyword !== '' || $filterKategori !== ''): ?>
                                    <p>Coba gunakan kata kunci atau pilihan filter yang berbeda.</p>
                                <?php else: ?>
                                    <p>Klik tombol <strong>Tambah Barang Baru</strong> untuk memulai memasukkan data stok.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($barangList as $item): 
                            $subtotal = (int)$item['jumlah'] * (float)$item['harga'];
                            
                            // Badge style berdasarkan kategori
                            $badgeClass = 'badge-default';
                            if ($item['kategori'] === 'Elektronik') $badgeClass = 'badge-elektronik';
                            elseif ($item['kategori'] === 'ATK') $badgeClass = 'badge-atk';
                            elseif ($item['kategori'] === 'Perlengkapan') $badgeClass = 'badge-perlengkapan';
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++ ?></td>
                                <td>
                                    <strong><?= e($item['nama_barang']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= e($item['kategori']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <?= number_format($item['jumlah'], 0, ',', '.') ?> unit
                                </td>
                                <td style="text-align: right;">
                                    <?= formatRupiah($item['harga']) ?>
                                </td>
                                <td style="text-align: right; font-weight: 600; color: var(--primary-dark);">
                                    <?= formatRupiah($subtotal) ?>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-links" style="justify-content: center;">
                                        <a href="edit.php?id=<?= (int)$item['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Data">✏️ Edit</a>
                                        <a href="hapus.php?id=<?= (int)$item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus barang \'<?= e($item['nama_barang']) ?>\'?')" title="Hapus Data">🗑️ Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($barangList)): ?>
                    <tfoot>
                        <tr class="table-footer">
                            <td colspan="3" style="text-align: right;">TOTAL KESELURUHAN:</td>
                            <td style="text-align: right;"><?= number_format($totalUnit, 0, ',', '.') ?> unit</td>
                            <td style="text-align: right;">-</td>
                            <td style="text-align: right; color: var(--success); font-size: 15px;"><?= formatRupiah($totalNilai) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>
