<?php
session_start();

// Melindungi halaman agar hanya bisa dibuka setelah login
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require_once "koneksi.php";
require_once "Barang.php";

// Fungsi untuk mengamankan output
function aman($data) {
    return htmlspecialchars((string)$data, ENT_QUOTES, "UTF-8");
}

// Fungsi format rupiah
function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}

// Mengambil data barang dari database
$query = $koneksi->prepare("
    SELECT 
        barang.id_barang,
        barang.kode_barang,
        barang.nama_barang,
        barang.jumlah,
        barang.kondisi,
        barang.harga_satuan,
        kategori.nama_kategori
    FROM barang
    LEFT JOIN kategori 
        ON barang.id_kategori = kategori.id_kategori
    ORDER BY barang.id_barang ASC
");

$query->execute();
$dataBarang = $query->fetchAll();

// Menghitung total data
$totalJenis = count($dataBarang);
$totalStok = 0;
$totalAset = 0;
$barangBaik = 0;

foreach ($dataBarang as $barang) {
    $totalStok += (int)$barang["jumlah"];
    $totalAset += (int)$barang["jumlah"] * (float)$barang["harga_satuan"];

    if ($barang["kondisi"] === "Baik") {
        $barangBaik++;
    }
}

// Membuat objek dari class Barang untuk contoh OOP
$objekBarang = new Barang("BRG-001", "Contoh Barang", 10);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SIM-Barang | Dashboard</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>SIM-Barang</h1>
    <p>Sistem Informasi Manajemen Barang</p>
</header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="#data-barang">Data Barang</a>
    <a href="logout.php">Logout</a>
</nav>

<main>

    <section class="welcome">
        <h2>Selamat Datang, <?= aman($_SESSION["nama"] ?? $_SESSION["username"] ?? "Pengguna"); ?>!</h2>
        <p>Anda login sebagai: <?= aman($_SESSION["role"] ?? "User"); ?></p>
    </section>

    <section class="dashboard">
        <h2>Dashboard Inventaris</h2>

        <div class="dashboard-container">

            <div class="dashboard-card">
                <h3>Total Jenis Barang</h3>
                <p><?= $totalJenis; ?></p>
            </div>

            <div class="dashboard-card">
                <h3>Total Stok Barang</h3>
                <p><?= $totalStok; ?></p>
            </div>

            <div class="dashboard-card">
                <h3>Barang Kondisi Baik</h3>
                <p><?= $barangBaik; ?></p>
            </div>

            <div class="dashboard-card">
                <h3>Estimasi Nilai Aset</h3>
                <p><?= rupiah($totalAset); ?></p>
            </div>

        </div>
    </section>

    <section id="data-barang">
        <h2>Data Inventaris Barang</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($dataBarang) > 0): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($dataBarang as $barang): ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= aman($barang["kode_barang"]); ?>
                                </td>

                                <td>
                                    <?= aman($barang["nama_barang"]); ?>
                                </td>

                                <td>
                                    <?= aman($barang["nama_kategori"] ?? "Kategori belum tersedia"); ?>
                                </td>

                                <td>
                                    <?= aman($barang["jumlah"]); ?>
                                </td>

                                <td>
                                    <?= aman($barang["kondisi"]); ?>
                                </td>

                                <td>
                                    <?= rupiah($barang["harga_satuan"]); ?>
                                </td>

                                <td>
                                    <?= rupiah($barang["jumlah"] * $barang["harga_satuan"]); ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8">Belum ada data barang.</td>
                        </tr>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2>Contoh Penerapan OOP PHP</h2>

        <?= $objekBarang->tampilkanInformasi(); ?>
    </section>

</main>

<footer>
    <p>&copy; <?= date("Y"); ?> SIM-Barang | Sistem Informasi Manajemen Barang</p>
</footer>

<script src="main.js"></script>

</body>
</html>