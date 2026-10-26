
<?php
// =====================================================
// DASHBOARD SIM-BARANG
// File: pages/dashboard.php
// =====================================================

// Pastikan koneksi PDO tersedia dari index.php.
if (!isset($pdo)) {
    exit('Error: koneksi database $pdo tidak tersedia.');
}

// Ambil role dari session.
$roleDashboard = $_SESSION['role'] ?? '';

// =====================================================
// 1. STATISTIK INVENTARIS
// =====================================================

$totalBarang = (int) $pdo->query(
    'SELECT COUNT(*) FROM barang'
)->fetchColumn();

$totalStok = (int) $pdo->query(
    'SELECT COALESCE(SUM(jumlah), 0) FROM barang'
)->fetchColumn();

$totalMasuk = (int) $pdo->query(
    'SELECT COUNT(*) FROM barang_masuk'
)->fetchColumn();

$totalKeluar = (int) $pdo->query(
    'SELECT COUNT(*) FROM barang_keluar'
)->fetchColumn();

// =====================================================
// 2. MENU CEPAT BERDASARKAN ROLE
// Tidak menggunakan variabel $menu dari index.php.
// =====================================================

if ($roleDashboard === 'Admin') {
    $menuDashboard = [
        'barang'        => 'Data Barang',
        'barang_masuk'  => 'Barang Masuk',
        'barang_keluar' => 'Barang Keluar',
        'kategori'      => 'Kategori Barang',
        'users'         => 'Data Pengguna'
    ];
} else {
    $menuDashboard = [
        'barang_masuk'  => 'Barang Masuk',
        'barang_keluar' => 'Barang Keluar'
    ];
}
?>

<!-- =====================================================
     3. KARTU STATISTIK
===================================================== -->

<section class="summary-grid">

    <article class="summary-card featured">
        <div class="summary-icon">B</div>
        <span class="summary-label">Jenis Barang</span>
        <strong><?= number_format($totalBarang, 0, ',', '.') ?></strong>
        <span class="summary-note">Data inventaris terdaftar</span>
    </article>

    <article class="summary-card mint">
        <div class="summary-icon">S</div>
        <span class="summary-label">Total Stok</span>
        <strong><?= number_format($totalStok, 0, ',', '.') ?></strong>
        <span class="summary-note">Jumlah unit seluruh barang</span>
    </article>

    <article class="summary-card sky">
        <div class="summary-icon">+</div>
        <span class="summary-label">Transaksi Masuk</span>
        <strong><?= number_format($totalMasuk, 0, ',', '.') ?></strong>
        <span class="summary-note">Catatan barang masuk</span>
    </article>

    <article class="summary-card lavender">
        <div class="summary-icon">−</div>
        <span class="summary-label">Transaksi Keluar</span>
        <strong><?= number_format($totalKeluar, 0, ',', '.') ?></strong>
        <span class="summary-note">Catatan barang keluar</span>
    </article>

</section>

<!-- =====================================================
     4. MENU CEPAT
===================================================== -->

<section class="content-card">

    <div class="section-heading">
        <div>
            <h2>Ringkasan Inventaris</h2>
            <p>
                Pilih menu untuk mengelola inventaris
                laboratorium dan jurusan.
            </p>
        </div>
    </div>

    <div class="quick-links">

        <?php foreach ($menuDashboard as $key => $label): ?>

            <a
                class="quick-link"
                href="index.php?page=<?= h($key) ?>"
            >
                <span><?= h($label) ?></span>
                <span aria-hidden="true">&rarr;</span>
            </a>

        <?php endforeach; ?>

    </div>

</section>

<!-- =====================================================
     5. INFORMASI SISTEM
===================================================== -->

<section class="content-card">

    <div class="section-heading">
        <div>
            <h2>Informasi Sistem</h2>
            <p>Ringkasan fungsi aplikasi inventaris.</p>
        </div>
    </div>

    <p class="text-secondary mb-0">
        SIM-Barang membantu pencatatan data inventaris,
        pemantauan jumlah stok, serta pencatatan transaksi
        barang masuk dan barang keluar.
    </p>

</section>
