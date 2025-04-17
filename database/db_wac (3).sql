-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 17, 2025 at 08:00 PM
-- Server version: 8.0.30
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_wac`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `kode_barang` varchar(50) COLLATE utf8mb3_swedish_ci NOT NULL,
  `nama_barang` varchar(100) COLLATE utf8mb3_swedish_ci NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb3_swedish_ci DEFAULT NULL,
  `satuan` varchar(20) COLLATE utf8mb3_swedish_ci DEFAULT NULL,
  `harga_beli` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(12,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `nama_barang`, `kategori`, `satuan`, `harga_beli`, `harga_jual`) VALUES
(8, 'BRG001', 'Monitor', 'Elektronik', 'Pcs', '20000.00', '30000.00'),
(9, 'BRG002', 'Printer', 'Elektronik', 'Pcs', '40000.00', '80000.00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id_inventory` int NOT NULL,
  `id_barang` int NOT NULL,
  `kode_transaksi` varchar(20) COLLATE utf8mb3_swedish_ci NOT NULL,
  `jenis_transaksi` enum('masuk','keluar') COLLATE utf8mb3_swedish_ci NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `jumlah` int NOT NULL,
  `sisa_stok` int NOT NULL,
  `keterangan` text COLLATE utf8mb3_swedish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id_inventory`, `id_barang`, `kode_transaksi`, `jenis_transaksi`, `tanggal`, `jumlah`, `sisa_stok`, `keterangan`) VALUES
(2, 8, 'INV001', 'masuk', '2025-04-17 17:43:00', 100, 100, 'Barang Masuk'),
(3, 8, 'PJL001', 'keluar', '2025-04-18 00:57:58', 2, 98, 'Penjualan produk'),
(4, 8, 'PJL002', 'keluar', '2025-04-18 01:00:28', 2, 96, 'Penjualan produk'),
(5, 9, 'INV003', 'masuk', '2025-04-17 18:01:00', 100, 100, 'Barang Masuk'),
(6, 8, 'PJL003', 'keluar', '2025-04-18 01:02:18', 1, 95, 'Penjualan produk'),
(7, 9, 'PJL003', 'keluar', '2025-04-18 01:02:18', 1, 99, 'Penjualan produk'),
(8, 8, 'PJL001', 'masuk', '2025-04-18 01:10:32', 2, 97, 'Pembatalan penjualan'),
(9, 8, 'PJL002', 'masuk', '2025-04-18 01:11:48', 2, 99, 'Pembatalan penjualan');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int NOT NULL,
  `kode_penjualan` varchar(20) COLLATE utf8mb3_swedish_ci NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_harga` decimal(12,2) NOT NULL DEFAULT '0.00',
  `nominal_bayar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(12,2) NOT NULL DEFAULT '0.00',
  `id_users` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `kode_penjualan`, `tanggal`, `subtotal`, `total_harga`, `nominal_bayar`, `kembalian`, `id_users`) VALUES
(12, 'PJL003', '2025-04-18 01:02:18', '0.00', '110000.00', '200000.00', '90000.00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `penjualan_detail`
--

CREATE TABLE `penjualan_detail` (
  `id_penjualan_detail` int NOT NULL,
  `id_penjualan` int NOT NULL,
  `id_barang` int NOT NULL,
  `jumlah` int NOT NULL DEFAULT '0',
  `harga_satuan` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_harga` decimal(12,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `penjualan_detail`
--

INSERT INTO `penjualan_detail` (`id_penjualan_detail`, `id_penjualan`, `id_barang`, `jumlah`, `harga_satuan`, `total_harga`) VALUES
(5, 12, 8, 1, '30000.00', '30000.00'),
(6, 12, 9, 1, '80000.00', '80000.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_users` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb3_swedish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb3_swedish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb3_swedish_ci NOT NULL,
  `level` enum('admin','pimpinan','gudang','kasir') COLLATE utf8mb3_swedish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_users`, `username`, `email`, `password`, `level`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin', 'admin', '2025-04-17 08:43:43'),
(2, 'hartono', 'pimpinan@gmail.com', 'pimpinan', 'pimpinan', '2025-04-17 08:43:43'),
(3, 'kasir', 'kasir@gmail.com', 'kasir', 'kasir', '2025-04-17 08:43:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id_inventory`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`);

--
-- Indexes for table `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD PRIMARY KEY (`id_penjualan_detail`),
  ADD KEY `id_penjualan` (`id_penjualan`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id_inventory` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  MODIFY `id_penjualan_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD CONSTRAINT `penjualan_detail_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`),
  ADD CONSTRAINT `penjualan_detail_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
