<?php
session_start();

require_once __DIR__ . '/koneksi.php';

/*
|--------------------------------------------------------------------------
| 1. FUNGSI BANTU
|--------------------------------------------------------------------------
*/

if (!function_exists('h')) {
    function h($value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

/*
|--------------------------------------------------------------------------
| 2. CEK LOGIN DAN ROLE
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['id_users']) ||
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['Admin', 'User'], true)
) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$namaPengguna = $_SESSION['nama']
    ?? $_SESSION['username']
    ?? 'Pengguna';

/*
|--------------------------------------------------------------------------
| 3. MENU ADMIN DAN USER
|--------------------------------------------------------------------------
*/

$menuAdmin = [
    'dashboard'     => 'Dashboard',
    'barang'        => 'Data Barang',
    'barang_masuk'  => 'Barang Masuk',
    'barang_keluar' => 'Barang Keluar',
    'kategori'      => 'Kategori Barang',
    'users'         => 'Data Pengguna'
];

$menuUser = [
    'barang_masuk'  => 'Barang Masuk',
    'barang_keluar' => 'Barang Keluar'
];

$menu = ($role === 'Admin')
    ? $menuAdmin
    : $menuUser;

/*
|--------------------------------------------------------------------------
| 4. TENTUKAN HALAMAN
|--------------------------------------------------------------------------
*/

$halamanDefault = ($role === 'Admin')
    ? 'dashboard'
    : 'barang_masuk';

$page = $_GET['page'] ?? $halamanDefault;

// User hanya boleh membuka halaman yang ada di menu User.
if (!array_key_exists($page, $menu)) {
    $page = $halamanDefault;
}

$judul = $menu[$page];
$fileHalaman = __DIR__ . '/pages/' . $page . '.php';

/*
|--------------------------------------------------------------------------
| 5. TOKEN CSRF DAN VARIABEL PESAN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = '';
$jenisPesan = 'success';
$ringkasanBarang = null;

/*
|--------------------------------------------------------------------------
| 6. PERHITUNGAN TOTAL ESTIMASI NILAI ASET
|--------------------------------------------------------------------------
| Rumus:
| Total Aset = SUM(Jumlah Barang x Harga Satuan)
|--------------------------------------------------------------------------
*/

$totalAset = 0;
$barangList = [];

if ($role === 'Admin' && $page === 'dashboard') {
    try {
        $stmtBarang = $pdo->query(
            'SELECT jumlah, harga_satuan FROM barang'
        );

        $barangList = $stmtBarang->fetchAll(PDO::FETCH_ASSOC);

        foreach ($barangList as $item) {
            $jumlahAset = (int) ($item['jumlah'] ?? 0);
            $hargaAset = (float) ($item['harga_satuan'] ?? 0);

            $totalAset += $jumlahAset * $hargaAset;
        }
    } catch (PDOException $e) {
        error_log(
            'Gagal menghitung estimasi nilai aset: '
                . $e->getMessage()
        );

        $totalAset = 0;
    }
}

/*
|--------------------------------------------------------------------------
| 7. PROSES TAMBAH BARANG
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'tambah_barang'
) {
    if ($role !== 'Admin' || $page !== 'barang') {
        http_response_code(403);
        exit('Akses ditolak. Hanya Admin yang dapat menambahkan barang.');
    }

    $tokenForm = (string) ($_POST['csrf_token'] ?? '');
    $tokenSession = (string) ($_SESSION['csrf_token'] ?? '');

    if (
        $tokenSession === '' ||
        !hash_equals($tokenSession, $tokenForm)
    ) {
        $pesan = 'Token keamanan tidak valid. Muat ulang halaman dan coba lagi.';
        $jenisPesan = 'danger';
    } else {
        // Terima kode "001" maupun "BRG-001".
        $kodeInput = strtoupper(
            trim((string) ($_POST['kode_barang'] ?? ''))
        );

        if (
            preg_match('/^BRG-([0-9]+)$/', $kodeInput, $cocok)
        ) {
            $kodeBarang = 'BRG-' . $cocok[1];
        } elseif (preg_match('/^[0-9]+$/', $kodeInput)) {
            $kodeBarang = 'BRG-' . $kodeInput;
        } else {
            $kodeBarang = '';
        }

        $namaBarang = trim(
            (string) ($_POST['nama_barang'] ?? '')
        );

        $idKategoriInput = $_POST['id_kategori'] ?? '';
        $jumlahInput = $_POST['jumlah'] ?? '';
        $kondisi = (string) ($_POST['kondisi'] ?? '');
        $hargaInput = $_POST['harga_satuan'] ?? '';

        $kondisiValid = [
            'Baik',
            'Rusak Ringan',
            'Rusak Berat'
        ];

        $idKategori = filter_var(
            $idKategoriInput,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $jumlah = filter_var(
            $jumlahInput,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $hargaValid = is_numeric($hargaInput)
            && is_finite((float) $hargaInput)
            && (float) $hargaInput >= 0;

        $harga = $hargaValid
            ? (float) $hargaInput
            : 0;

        if ($kodeBarang === '') {
            $pesan = 'Kode barang tidak valid. Masukkan angka saja, contoh 001.';
            $jenisPesan = 'danger';
        } elseif ($namaBarang === '') {
            $pesan = 'Nama barang wajib diisi.';
            $jenisPesan = 'danger';
        } elseif ($idKategori === false) {
            $pesan = 'Silakan pilih kategori barang yang valid.';
            $jenisPesan = 'danger';
        } elseif ($jumlah === false) {
            $pesan = 'Jumlah barang harus berupa bilangan bulat minimal 1.';
            $jenisPesan = 'danger';
        } elseif (!in_array($kondisi, $kondisiValid, true)) {
            $pesan = 'Silakan pilih kondisi barang yang valid.';
            $jenisPesan = 'danger';
        } elseif (!$hargaValid) {
            $pesan = 'Harga satuan harus berupa angka dan tidak boleh negatif.';
            $jenisPesan = 'danger';
        } else {
            try {
                // Pastikan kategori benar-benar tersedia.
                $cekKategori = $pdo->prepare(
                    'SELECT COUNT(*)
                     FROM kategori
                     WHERE id_kategori = ?'
                );

                $cekKategori->execute([$idKategori]);

                if ((int) $cekKategori->fetchColumn() === 0) {
                    $pesan = 'Kategori tidak ditemukan. Silakan pilih kategori lain.';
                    $jenisPesan = 'danger';
                } else {
                    // Periksa kode barang duplikat.
                    $cekKode = $pdo->prepare(
                        'SELECT COUNT(*)
                         FROM barang
                         WHERE kode_barang = ?'
                    );

                    $cekKode->execute([$kodeBarang]);

                    if ((int) $cekKode->fetchColumn() > 0) {
                        $pesan = 'Kode barang sudah digunakan. Gunakan kode lain.';
                        $jenisPesan = 'danger';
                    } else {
                        $simpan = $pdo->prepare(
                            'INSERT INTO barang
                                (
                                    kode_barang,
                                    nama_barang,
                                    id_kategori,
                                    jumlah,
                                    kondisi,
                                    harga_satuan
                                )
                             VALUES
                                (
                                    :kode,
                                    :nama,
                                    :kategori,
                                    :jumlah,
                                    :kondisi,
                                    :harga
                                )'
                        );

                        $simpan->execute([
                            ':kode'     => $kodeBarang,
                            ':nama'     => $namaBarang,
                            ':kategori' => $idKategori,
                            ':jumlah'   => $jumlah,
                            ':kondisi'  => $kondisi,
                            ':harga'    => $harga
                        ]);

                        $ringkasanBarang = [
                            'kode'   => $kodeBarang,
                            'nama'   => $namaBarang,
                            'jumlah' => $jumlah,
                            'harga'  => $harga,
                            'total'  => $jumlah * $harga
                        ];

                        $pesan = 'Data barang berhasil ditambahkan.';
                        $jenisPesan = 'success';

                        // Perbarui token setelah proses berhasil.
                        $_SESSION['csrf_token'] = bin2hex(
                            random_bytes(32)
                        );
                    }
                }
            } catch (PDOException $e) {
                error_log(
                    'Gagal menyimpan barang: '
                        . $e->getMessage()
                );

                $pesan = 'Data barang gagal disimpan. Periksa struktur database.';
                $jenisPesan = 'danger';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| 8. PROSES EDIT DAN HAPUS BARANG
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    in_array($_POST['aksi'] ?? '', ['edit_barang', 'hapus_barang'], true)
) {
    if ($role !== 'Admin' || $page !== 'barang') {
        http_response_code(403);
        exit('Akses ditolak. Hanya Admin yang dapat mengelola data barang.');
    }

    $tokenForm = (string) ($_POST['csrf_token'] ?? '');
    $tokenSession = (string) ($_SESSION['csrf_token'] ?? '');

    if (
        $tokenSession === '' ||
        !hash_equals($tokenSession, $tokenForm)
    ) {
        $pesan = 'Token keamanan tidak valid. Muat ulang halaman dan coba lagi.';
        $jenisPesan = 'danger';
    } else {
        $aksiBarang = (string) $_POST['aksi'];

        $idBarang = filter_var(
            $_POST['id_barang'] ?? '',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($idBarang === false) {
            $pesan = 'ID barang tidak valid.';
            $jenisPesan = 'danger';
        } else {
            try {
                $cekBarang = $pdo->prepare(
                    'SELECT id_barang FROM barang WHERE id_barang = ?'
                );
                $cekBarang->execute([$idBarang]);

                if (!$cekBarang->fetchColumn()) {
                    $pesan = 'Data barang tidak ditemukan.';
                    $jenisPesan = 'danger';
                } elseif ($aksiBarang === 'hapus_barang') {
                    $hapus = $pdo->prepare(
                        'DELETE FROM barang WHERE id_barang = ?'
                    );
                    $hapus->execute([$idBarang]);

                    $pesan = 'Data barang berhasil dihapus.';
                    $jenisPesan = 'success';
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $kodeInput = strtoupper(
                        trim((string) ($_POST['kode_barang'] ?? ''))
                    );

                    $kodeInput = strtoupper(
                        trim((string) ($_POST['kode_barang'] ?? ''))
                    );

                    if (preg_match('/^(?:BRG-)?([0-9]+)$/i', $kodeInput, $cocok)) {
                        $kodeBarangEdit = 'BRG-' . $cocok[1];
                    } else {
                        $kodeBarangEdit = '';
                    }

                    $namaBarangEdit = trim(
                        (string) ($_POST['nama_barang'] ?? '')
                    );

                    $idKategoriEdit = filter_var(
                        $_POST['id_kategori'] ?? '',
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1]]
                    );

                    $jumlahEdit = filter_var(
                        $_POST['jumlah'] ?? '',
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1]]
                    );

                    $kondisiEdit = (string) ($_POST['kondisi'] ?? '');
                    $hargaInputEdit = $_POST['harga_satuan'] ?? '';

                    $kondisiValidEdit = [
                        'Baik',
                        'Rusak Ringan',
                        'Rusak Berat'
                    ];

                    $hargaValidEdit = is_numeric($hargaInputEdit)
                        && is_finite((float) $hargaInputEdit)
                        && (float) $hargaInputEdit >= 0;

                    if ($kodeBarangEdit === '') {
                        $pesan = 'Kode barang tidak valid.';
                        $jenisPesan = 'danger';
                    } elseif ($namaBarangEdit === '') {
                        $pesan = 'Nama barang wajib diisi.';
                        $jenisPesan = 'danger';
                    } elseif ($idKategoriEdit === false) {
                        $pesan = 'Kategori barang tidak valid.';
                        $jenisPesan = 'danger';
                    } elseif ($jumlahEdit === false) {
                        $pesan = 'Jumlah harus berupa bilangan bulat minimal 1.';
                        $jenisPesan = 'danger';
                    } elseif (!in_array($kondisiEdit, $kondisiValidEdit, true)) {
                        $pesan = 'Kondisi barang tidak valid.';
                        $jenisPesan = 'danger';
                    } elseif (!$hargaValidEdit) {
                        $pesan = 'Harga satuan tidak valid.';
                        $jenisPesan = 'danger';
                    } else {
                        $cekKategoriEdit = $pdo->prepare(
                            'SELECT COUNT(*) FROM kategori WHERE id_kategori = ?'
                        );
                        $cekKategoriEdit->execute([$idKategoriEdit]);

                        $cekKodeEdit = $pdo->prepare(
                            'SELECT COUNT(*) FROM barang
                             WHERE kode_barang = ? AND id_barang <> ?'
                        );
                        $cekKodeEdit->execute([$kodeBarangEdit, $idBarang]);

                        if ((int) $cekKategoriEdit->fetchColumn() === 0) {
                            $pesan = 'Kategori tidak ditemukan.';
                            $jenisPesan = 'danger';
                        } elseif ((int) $cekKodeEdit->fetchColumn() > 0) {
                            $pesan = 'Kode barang sudah digunakan oleh barang lain.';
                            $jenisPesan = 'danger';
                        } else {
                            $updateBarang = $pdo->prepare(
                                'UPDATE barang
                                 SET kode_barang = ?,
                                     nama_barang = ?,
                                     id_kategori = ?,
                                     jumlah = ?,
                                     kondisi = ?,
                                     harga_satuan = ?
                                 WHERE id_barang = ?'
                            );

                            $updateBarang->execute([
                                $kodeBarangEdit,
                                $namaBarangEdit,
                                $idKategoriEdit,
                                $jumlahEdit,
                                $kondisiEdit,
                                (float) $hargaInputEdit,
                                $idBarang
                            ]);

                            $pesan = 'Data barang berhasil diperbarui.';
                            $jenisPesan = 'success';
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log('Gagal mengedit/menghapus barang: ' . $e->getMessage());

                if ($aksiBarang === 'hapus_barang') {
                    $pesan = 'Barang tidak dapat dihapus karena mungkin memiliki riwayat transaksi.';
                } else {
                    $pesan = 'Data barang gagal diperbarui. Periksa database.';
                }

                $jenisPesan = 'danger';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($judul) ?> - SIM-Barang</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="styles.css">
    <script src="main.js" defer></script>
</head>

<body>

    <div class="app-layout">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">S</div>
                <div>
                    <h2>SIM-Barang</h2>
                    <small>Sistem Inventaris</small>
                </div>
            </div>

            <div class="sidebar-section-label">MENU UTAMA</div>


            <nav class="sidebar-nav">
                <?php foreach ($menu as $key => $label): ?>
                    <a
                        href="index.php?page=<?= h($key) ?>"
                        class="sidebar-link <?= $page === $key ? 'active' : '' ?>">
                        <span><?= h($label) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>


            <div class="sidebar-user">
                <div class="user-avatar">
                    <?= h(strtoupper(substr($namaPengguna, 0, 1))) ?>
                </div>

                <div class="user-info">
                    <strong><?= h($namaPengguna) ?></strong>
                    <small><?= h($role) ?></small>
                </div>

                <a
                    href="logout.php"
                    class="logout-link"
                    data-confirm-logout
                    title="Keluar">Keluar</a>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <div class="main-area">

            <header class="topbar">
                <div>
                    <h1><?= h($judul) ?></h1>
                    <p>Selamat datang di Sistem Informasi Manajemen Barang.</p>
                </div>

                <div class="topbar-user">
                    <span><?= h($namaPengguna) ?></span>
                    <span class="role-badge"><?= h($role) ?></span>
                </div>
            </header>

            <main class="main-content">

                <?php if ($pesan !== ''): ?>
                    <div
                        class="alert alert-<?= h($jenisPesan) ?> alert-dismissible fade show"
                        role="alert">
                        <?= h($pesan) ?>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>

                <?php if ($ringkasanBarang !== null): ?>
                    <section class="content-card mb-4">
                        <h2>Ringkasan Barang yang Ditambahkan</h2>

                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Kode Barang</th>
                                        <td><?= h($ringkasanBarang['kode']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <td><?= h($ringkasanBarang['nama']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Jumlah</th>
                                        <td><?= (int) $ringkasanBarang['jumlah'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Harga Satuan</th>
                                        <td>
                                            Rp <?= number_format(
                                                    $ringkasanBarang['harga'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Estimasi Nilai Total</th>
                                        <td>
                                            <strong>
                                                Rp <?= number_format(
                                                        $ringkasanBarang['total'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) ?>
                                            </strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                if (is_file($fileHalaman)) {
                    require $fileHalaman;
                } else {
                    echo '<div class="alert alert-warning">';
                    echo 'File halaman tidak ditemukan: ';
                    echo h('pages/' . $page . '.php');
                    echo '</div>';
                }
                ?>

            </main>

            <footer class="app-footer">
                <p>&copy; <?= date('Y') ?> SIM-Barang. Sistem Informasi Manajemen Barang.</p>
            </footer>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>