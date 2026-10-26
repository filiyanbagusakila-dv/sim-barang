<?php
// prosesmasuk.php
// Menangani tambah, edit, dan hapus transaksi barang masuk.

session_start();
require_once __DIR__ . '/koneksi.php';

function kembaliMasuk(string $status, string $pesan): never
{
    header('Location: index.php?page=barang_masuk&status=' . rawurlencode($status) .
        '&pesan=' . rawurlencode($pesan));
    exit;
}

if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=barang_masuk');
    exit;
}

// Verifikasi CSRF jika aplikasi memakai token sesi.
if (!empty($_SESSION['csrf_token'])) {
    $tokenPost = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals((string) $_SESSION['csrf_token'], $tokenPost)) {
        kembaliMasuk('error', 'Permintaan tidak valid. Silakan muat ulang halaman.');
    }
}

$aksi = (string) ($_POST['aksi'] ?? '');
$idUser = (int) ($_SESSION['id_users'] ?? 0);
$role = strtolower((string) ($_SESSION['role'] ?? 'user'));

if ($idUser < 1) {
    kembaliMasuk('error', 'ID pengguna tidak ditemukan di sesi login. Periksa proses login.php.');
}

try {
    if ($aksi === 'tambah_masuk') {
        $idBarang = filter_input(INPUT_POST, 'id_barang', FILTER_VALIDATE_INT);
        $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
        $tanggalInput = trim((string) ($_POST['tanggal'] ?? ''));

        if (!$idBarang || !$jumlah || $jumlah < 1 || $tanggalInput === '') {
            kembaliMasuk('error', 'Barang, jumlah positif, dan tanggal wajib diisi.');
        }

        $tanggal = date('Y-m-d H:i:s', strtotime($tanggalInput));
        if ($tanggal === '1970-01-01 00:00:00' || strtotime($tanggalInput) === false) {
            kembaliMasuk('error', 'Format tanggal tidak valid.');
        }

        $pdo->beginTransaction();

        $cekBarang = $pdo->prepare('SELECT id_barang FROM barang WHERE id_barang = ? FOR UPDATE');
        $cekBarang->execute([$idBarang]);
        if (!$cekBarang->fetchColumn()) {
            $pdo->rollBack();
            kembaliMasuk('error', 'Barang yang dipilih tidak ditemukan.');
        }

        $insert = $pdo->prepare(
            'INSERT INTO barang_masuk (id_barang, id_user, jumlah, tanggal)
             VALUES (:id_barang, :id_user, :jumlah, :tanggal)'
        );
        $insert->execute([
            ':id_barang' => $idBarang,
            ':id_user' => $idUser,
            ':jumlah' => $jumlah,
            ':tanggal' => $tanggal,
        ]);

        $updateStok = $pdo->prepare('UPDATE barang SET jumlah = jumlah + ? WHERE id_barang = ?');
        $updateStok->execute([$jumlah, $idBarang]);

        $pdo->commit();
        kembaliMasuk('success', 'Transaksi barang masuk berhasil disimpan dan stok bertambah.');
    }

    if ($aksi === 'edit_masuk') {
        $idMasuk = filter_input(INPUT_POST, 'id_masuk', FILTER_VALIDATE_INT);
        $idBarangBaru = filter_input(INPUT_POST, 'id_barang', FILTER_VALIDATE_INT);
        $jumlahBaru = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
        $tanggalInput = trim((string) ($_POST['tanggal'] ?? ''));

        if (!$idMasuk || !$idBarangBaru || !$jumlahBaru || $jumlahBaru < 1 || $tanggalInput === '') {
            kembaliMasuk('error', 'Data edit tidak lengkap atau jumlah tidak valid.');
        }

        $timestamp = strtotime($tanggalInput);
        if ($timestamp === false) {
            kembaliMasuk('error', 'Format tanggal tidak valid.');
        }
        $tanggalBaru = date('Y-m-d H:i:s', $timestamp);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id_masuk, id_barang, jumlah FROM barang_masuk WHERE id_masuk = ? FOR UPDATE');
        $stmt->execute([$idMasuk]);
        $lama = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lama) {
            $pdo->rollBack();
            kembaliMasuk('error', 'Transaksi barang masuk tidak ditemukan.');
        }

        // Kunci baris barang lama dan barang baru secara konsisten.
        $idBarangLama = (int) $lama['id_barang'];
        $idBarangKunci = array_values(array_unique([$idBarangLama, (int) $idBarangBaru]));
        sort($idBarangKunci);
        $kunciBarang = $pdo->prepare('SELECT id_barang, jumlah FROM barang WHERE id_barang = ? FOR UPDATE');
        $stokBarang = [];
        foreach ($idBarangKunci as $id) {
            $kunciBarang->execute([$id]);
            $hasil = $kunciBarang->fetch(PDO::FETCH_ASSOC);
            if (!$hasil) {
                $pdo->rollBack();
                kembaliMasuk('error', 'Barang yang dipilih tidak ditemukan.');
            }
            $stokBarang[(int) $hasil['id_barang']] = (int) $hasil['jumlah'];
        }

        $jumlahLama = (int) $lama['jumlah'];

        if ($idBarangLama === (int) $idBarangBaru) {
            $selisih = $jumlahBaru - $jumlahLama;
            if ($selisih !== 0) {
                $update = $pdo->prepare('UPDATE barang SET jumlah = jumlah + ? WHERE id_barang = ?');
                $update->execute([$selisih, $idBarangLama]);
            }
        } else {
            // Kembalikan stok dari transaksi lama, lalu tambahkan ke barang baru.
            if ($stokBarang[$idBarangLama] < $jumlahLama) {
                $pdo->rollBack();
                kembaliMasuk('error', 'Stok barang lama tidak cukup untuk membatalkan transaksi ini.');
            }

            $kurangiLama = $pdo->prepare('UPDATE barang SET jumlah = jumlah - ? WHERE id_barang = ?');
            $kurangiLama->execute([$jumlahLama, $idBarangLama]);

            $tambahBaru = $pdo->prepare('UPDATE barang SET jumlah = jumlah + ? WHERE id_barang = ?');
            $tambahBaru->execute([$jumlahBaru, $idBarangBaru]);
        }

        $updateTransaksi = $pdo->prepare(
            'UPDATE barang_masuk
             SET id_barang = :id_barang, jumlah = :jumlah, tanggal = :tanggal
             WHERE id_masuk = :id_masuk'
        );
        $updateTransaksi->execute([
            ':id_barang' => $idBarangBaru,
            ':jumlah' => $jumlahBaru,
            ':tanggal' => $tanggalBaru,
            ':id_masuk' => $idMasuk,
        ]);

        $pdo->commit();
        kembaliMasuk('success', 'Transaksi berhasil diperbarui dan stok sudah disesuaikan.');
    }

    if ($aksi === 'hapus_masuk') {
        // Penghapusan dibatasi untuk Admin.
        if ($role !== 'admin') {
            kembaliMasuk('error', 'Hanya Admin yang dapat menghapus transaksi barang masuk.');
        }

        $idMasuk = filter_input(INPUT_POST, 'id_masuk', FILTER_VALIDATE_INT);
        if (!$idMasuk) {
            kembaliMasuk('error', 'ID transaksi tidak valid.');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id_masuk, id_barang, jumlah FROM barang_masuk WHERE id_masuk = ? FOR UPDATE');
        $stmt->execute([$idMasuk]);
        $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaksi) {
            $pdo->rollBack();
            kembaliMasuk('error', 'Transaksi barang masuk tidak ditemukan.');
        }

        $idBarang = (int) $transaksi['id_barang'];
        $jumlah = (int) $transaksi['jumlah'];

        $cekStok = $pdo->prepare('SELECT jumlah FROM barang WHERE id_barang = ? FOR UPDATE');
        $cekStok->execute([$idBarang]);
        $stokSekarang = $cekStok->fetchColumn();

        if ($stokSekarang === false || (int) $stokSekarang < $jumlah) {
            $pdo->rollBack();
            kembaliMasuk('error', 'Stok saat ini tidak cukup untuk menghapus transaksi ini dengan aman.');
        }

        $updateStok = $pdo->prepare('UPDATE barang SET jumlah = jumlah - ? WHERE id_barang = ?');
        $updateStok->execute([$jumlah, $idBarang]);

        $hapus = $pdo->prepare('DELETE FROM barang_masuk WHERE id_masuk = ?');
        $hapus->execute([$idMasuk]);

        $pdo->commit();
        kembaliMasuk('success', 'Transaksi dihapus dan stok barang sudah dikurangi kembali.');
    }

    kembaliMasuk('error', 'Aksi tidak dikenali.');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Detail error dicatat di log server, bukan ditampilkan ke pengguna.
    error_log('prosesmasuk.php: ' . $e->getMessage());
    kembaliMasuk('error', 'Terjadi kesalahan saat memproses barang masuk. Periksa struktur tabel dan log PHP.');
}
