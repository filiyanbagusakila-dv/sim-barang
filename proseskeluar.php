<?php
// proseskeluar.php
// Menangani tambah, edit, dan hapus transaksi barang keluar.

session_start();
require_once __DIR__ . '/koneksi.php';

function kembaliKeluar(string $status, string $pesan): never
{
    header('Location: index.php?page=barang_keluar&status=' . rawurlencode($status) .
        '&pesan=' . rawurlencode($pesan));
    exit;
}

if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=barang_keluar');
    exit;
}

if (!empty($_SESSION['csrf_token'])) {
    $tokenPost = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals((string) $_SESSION['csrf_token'], $tokenPost)) {
        kembaliKeluar('error', 'Permintaan tidak valid. Silakan muat ulang halaman.');
    }
}

$aksi = (string) ($_POST['aksi'] ?? '');
$idUser = (int) ($_SESSION['id_users'] ?? 0);
$role = strtolower((string) ($_SESSION['role'] ?? 'user'));

if ($idUser < 1) {
    kembaliKeluar('error', 'ID pengguna tidak ditemukan di sesi login. Periksa proses login.php.');
}

try {
    if ($aksi === 'tambah_keluar') {
        $idBarang = filter_input(INPUT_POST, 'id_barang', FILTER_VALIDATE_INT);
        $jumlah = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
        $tanggalInput = trim((string) ($_POST['tanggal'] ?? ''));
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));

        if (!$idBarang || !$jumlah || $jumlah < 1 || $tanggalInput === '' || $keterangan === '') {
            kembaliKeluar('error', 'Barang, jumlah positif, tanggal, dan keterangan wajib diisi.');
        }
        $timestamp = strtotime($tanggalInput);
        if ($timestamp === false) {
            kembaliKeluar('error', 'Format tanggal tidak valid.');
        }
        $tanggal = date('Y-m-d H:i:s', $timestamp);

        $pdo->beginTransaction();

        $cek = $pdo->prepare('SELECT jumlah FROM barang WHERE id_barang = ? FOR UPDATE');
        $cek->execute([$idBarang]);
        $stok = $cek->fetchColumn();

        if ($stok === false) {
            $pdo->rollBack();
            kembaliKeluar('error', 'Barang yang dipilih tidak ditemukan.');
        }
        if ((int) $stok < $jumlah) {
            $pdo->rollBack();
            kembaliKeluar('error', 'Stok tidak mencukupi. Stok tersedia: ' . (int) $stok . '.');
        }

        $insert = $pdo->prepare(
            'INSERT INTO barang_keluar (id_barang, id_user, jumlah, tanggal, keterangan)
             VALUES (:id_barang, :id_user, :jumlah, :tanggal, :keterangan)'
        );
        $insert->execute([
            ':id_barang' => $idBarang,
            ':id_user' => $idUser,
            ':jumlah' => $jumlah,
            ':tanggal' => $tanggal,
            ':keterangan' => $keterangan,
        ]);

        $updateStok = $pdo->prepare('UPDATE barang SET jumlah = jumlah - ? WHERE id_barang = ?');
        $updateStok->execute([$jumlah, $idBarang]);

        $pdo->commit();
        kembaliKeluar('success', 'Transaksi barang keluar berhasil disimpan dan stok berkurang.');
    }

    if ($aksi === 'edit_keluar') {
        $idKeluar = filter_input(INPUT_POST, 'id_keluar', FILTER_VALIDATE_INT);
        $idBarangBaru = filter_input(INPUT_POST, 'id_barang', FILTER_VALIDATE_INT);
        $jumlahBaru = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT);
        $tanggalInput = trim((string) ($_POST['tanggal'] ?? ''));
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));

        if (!$idKeluar || !$idBarangBaru || !$jumlahBaru || $jumlahBaru < 1 || $tanggalInput === '' || $keterangan === '') {
            kembaliKeluar('error', 'Data edit tidak lengkap atau tidak valid.');
        }
        $timestamp = strtotime($tanggalInput);
        if ($timestamp === false) {
            kembaliKeluar('error', 'Format tanggal tidak valid.');
        }
        $tanggalBaru = date('Y-m-d H:i:s', $timestamp);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id_keluar, id_barang, jumlah FROM barang_keluar WHERE id_keluar = ? FOR UPDATE');
        $stmt->execute([$idKeluar]);
        $lama = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$lama) {
            $pdo->rollBack();
            kembaliKeluar('error', 'Transaksi barang keluar tidak ditemukan.');
        }

        $idBarangLama = (int) $lama['id_barang'];
        $ids = array_values(array_unique([$idBarangLama, (int) $idBarangBaru]));
        sort($ids);
        $stokMap = [];
        $kunci = $pdo->prepare('SELECT id_barang, jumlah FROM barang WHERE id_barang = ? FOR UPDATE');
        foreach ($ids as $id) {
            $kunci->execute([$id]);
            $r = $kunci->fetch(PDO::FETCH_ASSOC);
            if (!$r) {
                $pdo->rollBack();
                kembaliKeluar('error', 'Barang yang dipilih tidak ditemukan.');
            }
            $stokMap[(int) $r['id_barang']] = (int) $r['jumlah'];
        }

        $jumlahLama = (int) $lama['jumlah'];

        if ($idBarangLama === (int) $idBarangBaru) {
            // Pulihkan kuantitas transaksi lama, kemudian terapkan jumlah baru.
            $stokSetelahPulih = $stokMap[$idBarangLama] + $jumlahLama;
            if ($stokSetelahPulih < $jumlahBaru) {
                $pdo->rollBack();
                kembaliKeluar('error', 'Stok tidak cukup untuk jumlah keluar baru. Stok yang tersedia setelah pemulihan transaksi lama: ' . $stokSetelahPulih . '.');
            }
            $updateStok = $pdo->prepare('UPDATE barang SET jumlah = ? WHERE id_barang = ?');
            $updateStok->execute([$stokSetelahPulih - $jumlahBaru, $idBarangLama]);
        } else {
            // Pulihkan stok barang lama, lalu kurangi stok barang yang baru dipilih.
            if ($stokMap[(int) $idBarangBaru] < $jumlahBaru) {
                $pdo->rollBack();
                kembaliKeluar('error', 'Stok barang baru tidak mencukupi. Stok tersedia: ' . $stokMap[(int) $idBarangBaru] . '.');
            }
            $pulihkan = $pdo->prepare('UPDATE barang SET jumlah = jumlah + ? WHERE id_barang = ?');
            $pulihkan->execute([$jumlahLama, $idBarangLama]);

            $kurangi = $pdo->prepare('UPDATE barang SET jumlah = jumlah - ? WHERE id_barang = ?');
            $kurangi->execute([$jumlahBaru, $idBarangBaru]);
        }

        $update = $pdo->prepare(
            'UPDATE barang_keluar
             SET id_barang = :id_barang, jumlah = :jumlah, tanggal = :tanggal, keterangan = :keterangan
             WHERE id_keluar = :id_keluar'
        );
        $update->execute([
            ':id_barang' => $idBarangBaru,
            ':jumlah' => $jumlahBaru,
            ':tanggal' => $tanggalBaru,
            ':keterangan' => $keterangan,
            ':id_keluar' => $idKeluar,
        ]);

        $pdo->commit();
        kembaliKeluar('success', 'Transaksi barang keluar berhasil diperbarui dan stok disesuaikan.');
    }

    if ($aksi === 'hapus_keluar') {
        if ($role !== 'admin') {
            kembaliKeluar('error', 'Hanya Admin yang dapat menghapus transaksi barang keluar.');
        }

        $idKeluar = filter_input(INPUT_POST, 'id_keluar', FILTER_VALIDATE_INT);
        if (!$idKeluar) {
            kembaliKeluar('error', 'ID transaksi tidak valid.');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT id_keluar, id_barang, jumlah FROM barang_keluar WHERE id_keluar = ? FOR UPDATE');
        $stmt->execute([$idKeluar]);
        $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$transaksi) {
            $pdo->rollBack();
            kembaliKeluar('error', 'Transaksi barang keluar tidak ditemukan.');
        }

        $idBarang = (int) $transaksi['id_barang'];
        $jumlah = (int) $transaksi['jumlah'];

        $updateStok = $pdo->prepare('UPDATE barang SET jumlah = jumlah + ? WHERE id_barang = ?');
        $updateStok->execute([$jumlah, $idBarang]);

        $hapus = $pdo->prepare('DELETE FROM barang_keluar WHERE id_keluar = ?');
        $hapus->execute([$idKeluar]);

        $pdo->commit();
        kembaliKeluar('success', 'Transaksi dihapus dan stok barang dikembalikan.');
    }

    kembaliKeluar('error', 'Aksi tidak dikenali.');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('proseskeluar.php: ' . $e->getMessage());
    kembaliKeluar('error', 'Terjadi kesalahan saat memproses barang keluar. Periksa struktur tabel dan log PHP.');
}
