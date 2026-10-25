<?php
// =============================================
// SIM-BARANG
// PERTEMUAN 5 + 6
// PHP, SESSION, DAN OOP
// =============================================

session_start();

// Melindungi halaman agar hanya bisa dibuka setelah login
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

// Memanggil class Barang
require_once "Barang.php";

// Membuat objek Barang
$barangOOP = new Barang("BRG-001", "Laptop", 10);

// Variabel hasil pemrosesan
$hasilInput = null;
$pesanError = "";

// =============================================
// 1. MEMPROSES FORMULIR
// =============================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $kode = strtoupper(trim($_POST["kode"] ?? ""));
    $nama = trim($_POST["nama"] ?? "");
    $kategori = trim($_POST["kategori"] ?? "");
    $jumlah = $_POST["jumlah"] ?? "";
    $harga = $_POST["harga"] ?? "";
    $kondisi = trim($_POST["kondisi"] ?? "");

    // =============================================
    // 2. VALIDASI DATA
    // =============================================

    if (!preg_match('/^BRG-\d+$/', $kode)) {

        $pesanError = "Kode barang harus menggunakan format BRG-001.";

    } elseif ($nama === "") {

        $pesanError = "Nama barang wajib diisi.";

    } elseif (!in_array($kategori, [
        "Elektronik",
        "Perabot",
        "Alat Tulis"
    ], true)) {

        $pesanError = "Kategori barang tidak valid.";

    } elseif (
        $jumlah === "" ||
        !is_numeric($jumlah) ||
        !is_finite((float)$jumlah) ||
        (float)$jumlah <= 0 ||
        floor((float)$jumlah) != (float)$jumlah
    ) {

        $pesanError = "Jumlah barang harus berupa bilangan bulat positif.";

    } elseif (
        $harga === "" ||
        !is_numeric($harga) ||
        !is_finite((float)$harga) ||
        (float)$harga < 0
    ) {

        $pesanError = "Harga satuan harus berupa angka nol atau lebih.";

    } elseif (!in_array($kondisi, [
        "Baik",
        "Rusak Ringan",
        "Rusak Berat"
    ], true)) {

        $pesanError = "Kondisi barang tidak valid.";

    } else {

        // =============================================
        // 3. PERHITUNGAN NILAI ASET
        // =============================================

        $jumlahBarang = (int)$jumlah;
        $hargaSatuan = (float)$harga;

        $totalNilai = $jumlahBarang * $hargaSatuan;

        // Menyimpan hasil sementara
        $hasilInput = [
            "kode" => $kode,
            "nama" => $nama,
            "kategori" => $kategori,
            "jumlah" => $jumlahBarang,
            "harga" => $hargaSatuan,
            "kondisi" => $kondisi,
            "total" => $totalNilai
        ];
    }
}

// =============================================
// 4. FUNGSI MENGAMANKAN OUTPUT
// =============================================

function aman($data) {
    return htmlspecialchars(
        (string)$data,
        ENT_QUOTES,
        "UTF-8"
    );
}

// Fungsi format rupiah
function rupiah($angka) {
    return "Rp " . number_format(
        (float)$angka,
        0,
        ",",
        "."
    );
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SIM-Barang | Sistem Informasi Barang</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- HEADER -->
    <header>
        <div class="header-content">

            <div>
                <h1>SIM-Barang</h1>
                <p>Sistem Informasi Manajemen Barang</p>
            </div>

            <!-- INFORMASI PENGGUNA -->
            <div class="user-info">

                <div class="user-detail">
                    <strong>
                        <?= aman($_SESSION["nama"] ?? $_SESSION["username"] ?? "Pengguna") ?>
                    </strong>

                    <span>
                        <?= aman($_SESSION["role"] ?? "User") ?>
                    </span>
                </div>

                <a href="logout.php" class="btn-logout">
                    Logout
                </a>

            </div>

        </div>
    </header>

    <!-- NAVIGASI -->
    <nav>
        <a href="#dashboard">Dashboard</a>
        <a href="#inventaris">Data Inventaris</a>
        <a href="#oop-barang">OOP Barang</a>
        <a href="#form-barang">Input Barang</a>
        <a href="#hasil-pemrosesan">Hasil Pemrosesan</a>
    </nav>

    <!-- KONTEN UTAMA -->
    <main>

        <!-- DASHBOARD -->
        <section id="dashboard">

            <h2>Dashboard Inventaris</h2>

            <p>
                Selamat datang di Sistem Informasi Manajemen Barang.
                Aplikasi ini digunakan untuk mengelola data inventaris
                barang laboratorium dan jurusan.
            </p>

        </section>

        <!-- PENERAPAN OOP -->
        <section id="oop-barang">

            <h2>Penerapan OOP - Class Barang</h2>

            <p>
                Bagian ini menunjukkan penggunaan class dan object
                pada sistem informasi inventaris SIM-Barang.
            </p>

            <?= $barangOOP->tampilkanInformasi(); ?>

        </section>

        <!-- TABEL INVENTARIS -->
        <section id="inventaris">

            <h2>Tabel Inventaris Barang</h2>

            <!-- PENCARIAN -->
            <div class="search-box">

                <input
                    type="text"
                    id="cariBarang"
                    placeholder="Cari kode atau nama barang..."
                >

            </div>

            <!-- TABEL -->
            <div class="table-container">

                <table>

                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Kondisi</th>
                        </tr>
                    </thead>

                    <tbody id="tabelBarang">

                        <tr>
                            <td>BRG-001</td>
                            <td>Laptop</td>
                            <td>Elektronik</td>
                            <td>10</td>
                            <td>
                                <span class="status baik">Baik</span>
                            </td>
                        </tr>

                        <tr>
                            <td>BRG-002</td>
                            <td>Proyektor</td>
                            <td>Elektronik</td>
                            <td>5</td>
                            <td>
                                <span class="status baik">Baik</span>
                            </td>
                        </tr>

                        <tr>
                            <td>BRG-003</td>
                            <td>Meja</td>
                            <td>Perabot</td>
                            <td>15</td>
                            <td>
                                <span class="status baik">Baik</span>
                            </td>
                        </tr>

                    </tbody>

                </table>

                <p id="pesanKosong">
                    Data barang tidak ditemukan.
                </p>

            </div>

        </section>

        <!-- FORM INPUT BARANG -->
        <section id="form-barang">

            <h2>Formulir Input Barang</h2>

            <!-- NOTIFIKASI JAVASCRIPT -->
            <div
                id="notifikasi"
                role="status"
                aria-live="polite"
            ></div>

            <!-- NOTIFIKASI ERROR PHP -->
            <?php if ($pesanError !== ""): ?>

                <div class="pesan-error" role="alert">
                    <?= aman($pesanError) ?>
                </div>

            <?php endif; ?>

            <!-- FORMULIR -->
            <form
                id="formInputBarang"
                action="index.php#hasil-pemrosesan"
                method="POST"
            >

                <div class="form-group">

                    <label for="kode">Kode Barang</label>

                    <input
                        type="text"
                        id="kode"
                        name="kode"
                        placeholder="Contoh: BRG-004"
                        pattern="BRG-[0-9]+"
                        title="Gunakan format BRG-001"
                        value="<?= isset($kode) ? aman($kode) : '' ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="nama">Nama Barang</label>

                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        placeholder="Masukkan nama barang"
                        value="<?= isset($nama) ? aman($nama) : '' ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="kategori">Kategori</label>

                    <select id="kategori" name="kategori" required>

                        <option value="">-- Pilih Kategori --</option>

                        <option value="Elektronik"
                            <?= (isset($kategori) && $kategori === "Elektronik") ? "selected" : "" ?>>
                            Elektronik
                        </option>

                        <option value="Perabot"
                            <?= (isset($kategori) && $kategori === "Perabot") ? "selected" : "" ?>>
                            Perabot
                        </option>

                        <option value="Alat Tulis"
                            <?= (isset($kategori) && $kategori === "Alat Tulis") ? "selected" : "" ?>>
                            Alat Tulis
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label for="jumlah">Jumlah Barang</label>

                    <input
                        type="number"
                        id="jumlah"
                        name="jumlah"
                        min="1"
                        step="1"
                        placeholder="Masukkan jumlah barang"
                        value="<?= isset($jumlah) ? aman($jumlah) : '' ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="harga">Harga Satuan (Rp)</label>

                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        min="0"
                        step="any"
                        placeholder="Contoh: 150000"
                        value="<?= isset($harga) ? aman($harga) : '' ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="kondisi">Kondisi Barang</label>

                    <select id="kondisi" name="kondisi" required>

                        <option value="">-- Pilih Kondisi --</option>

                        <option value="Baik"
                            <?= (isset($kondisi) && $kondisi === "Baik") ? "selected" : "" ?>>
                            Baik
                        </option>

                        <option value="Rusak Ringan"
                            <?= (isset($kondisi) && $kondisi === "Rusak Ringan") ? "selected" : "" ?>>
                            Rusak Ringan
                        </option>

                        <option value="Rusak Berat"
                            <?= (isset($kondisi) && $kondisi === "Rusak Berat") ? "selected" : "" ?>>
                            Rusak Berat
                        </option>

                    </select>

                </div>

                <!-- TOMBOL -->
                <div class="form-button">

                    <button type="submit" class="btn-simpan">
                        Simpan Barang
                    </button>

                    <button type="reset" class="btn-reset">
                        Reset
                    </button>

                </div>

            </form>

        </section>

        <!-- HASIL PEMROSESAN PHP -->
        <section id="hasil-pemrosesan">

            <h2>Ringkasan Pemrosesan Barang</h2>

            <?php if ($hasilInput !== null): ?>

                <div class="pesan-sukses">
                    Data barang berhasil diproses oleh PHP!
                </div>

                <div class="table-container">

                    <table>

                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Kondisi</th>
                                <th>Total Nilai</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>
                                <td><?= aman($hasilInput["kode"]) ?></td>
                                <td><?= aman($hasilInput["nama"]) ?></td>
                                <td><?= aman($hasilInput["kategori"]) ?></td>
                                <td><?= aman($hasilInput["jumlah"]) ?></td>
                                <td><?= rupiah($hasilInput["harga"]) ?></td>
                                <td><?= aman($hasilInput["kondisi"]) ?></td>
                                <td>
                                    <strong>
                                        <?= rupiah($hasilInput["total"]) ?>
                                    </strong>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- TOTAL NILAI ASET -->
                <div class="total-aset">

                    <p>Total Estimasi Nilai Aset</p>

                    <h3>
                        <?= rupiah($hasilInput["total"]) ?>
                    </h3>

                </div>

            <?php else: ?>

                <p class="belum-ada-data">
                    Belum ada data barang yang diproses.
                    Silakan isi formulir input barang terlebih dahulu.
                </p>

            <?php endif; ?>

        </section>

    </main>

    <!-- FOOTER -->
    <footer>

        <p>
            &copy; 2026 SIM-Barang |
            Sistem Informasi Inventaris
        </p>

    </footer>

    <!-- JAVASCRIPT EKSTERNAL -->
    <script src="main.js"></script>

</body>
</html>