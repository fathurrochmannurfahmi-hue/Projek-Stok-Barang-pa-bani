-- Database: `db_stok_barang`
-- Praktikum Pemrograman Web Dasar - STMIK IKMI Cirebon
-- Modul: Pengolahan Form & Manajemen Stok Barang

CREATE DATABASE IF NOT EXISTS `db_stok_barang` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_stok_barang`;

-- Struktur Tabel `barang`
DROP TABLE IF EXISTS `barang`;
CREATE TABLE `barang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal Contoh Pengujian
INSERT INTO `barang` (`id`, `nama_barang`, `kategori`, `jumlah`, `harga`, `created_at`) VALUES
(1, 'Mouse Wireless', 'Elektronik', 5, 125000.00, NOW()),
(2, 'Buku Tulis Sinar Dunia 58 Lembar', 'ATK', 50, 6500.00, NOW()),
(3, 'Stop Kontak Uticon 4 Lubang', 'Perlengkapan', 12, 45000.00, NOW()),
(4, 'Keyboard Mekanikal TKL', 'Elektronik', 8, 350000.00, NOW()),
(5, 'Pulpen Gel Hitam 0.5mm', 'ATK', 100, 3500.00, NOW());
