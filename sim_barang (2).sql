-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20261019.24ede9da2d
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2026 at 08:29 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sim_barang`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--
CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `id_kategori` int NOT NULL,
  `jumlah` int NOT NULL,
  `kondisi` enum('Baik','Rusak Ringan','Rusak Berat') NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `nama_barang`, `id_kategori`, `jumlah`, `kondisi`, `harga_satuan`) VALUES
(1, 'BRG001', 'Pensil', 1, 11, 'Baik', 2000.00),
(2, 'BRG002', 'Pena', 1, 10, 'Rusak Ringan', 3000.00),
(3, 'BRG005', 'TV', 1, 3, 'Baik', 10000.00),
(4, 'BRG-009', 'Penagus', 1, 29, 'Baik', 9000.00),
(5, 'BRG-007', 'Pensiliy', 1, 99, 'Baik', 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--
CREATE TABLE `barang_keluar` (
  `id_keluar` int NOT NULL,
  `id_barang` int NOT NULL,
  `id_user` int NOT NULL,
  `jumlah` int NOT NULL,
  `tanggal` datetime NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_masuk`
--
CREATE TABLE `barang_masuk` (
  `id_masuk` int NOT NULL,
  `id_barang` int NOT NULL,
  `id_user` int NOT NULL,
  `jumlah` int NOT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--
CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `keterangan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `keterangan`) VALUES
(2, 'ATK', 'Alat tulis kantor'),
(3, 'Elektronik', 'Peralatan elektronik'),
(4, 'Furniture', 'Meja, kursi, dan lemari'),
(5, 'Peralatan Laboratorium', 'Peralatan laboratorium'),
(6, 'Peralatan Komputer', 'Komputer dan perlengkapannya'),
(7, 'Perlengkapan Kebersihan', 'Alat kebersihan'),
(8, 'Peralatan Kantor', 'Perlengkapan kantor'),
(9, 'Peralatan Jaringan', 'Perangkat jaringan komputer'),
(10, 'Peralatan Praktikum', 'Peralatan untuk praktikum'),
(11, 'Lainnya', 'Kategori barang lainnya');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id_users` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('User','Admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_users`, `nama`, `username`, `password`, `role`) VALUES
(2, 'filiyan', 'admin', '$2y$10$tIS3TttMSSWweDvq6ddCoOwLY.88IRg2EEWj1.bZBGnRiJrEGVxja', 'Admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
