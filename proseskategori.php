<?php
// proseskategori.php
session_start();
require_once __DIR__ . '/koneksi.php';

function kembaliKategori(string $status, string $pesan): never
{
    header('Location: index.php?page=kategori&status=' . rawurlencode($status) .
        '&pesan=' . rawurlencode($pesan));
    exit;
}

if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=kategori');
    exit;
}
if (!empty($_SESSION['csrf_token'])) {
    $tokenPost = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals((string) $_SESSION['csrf_token'], $tokenPost)) {
        kembaliKategori('error', 'Permintaan tidak valid. Silakan muat ulang halaman.');
    }
}

$aksi = (string) ($_POST['aksi'] ?? '');
$role = strtolower((string) ($_SESSION['role'] ?? 'user'));

try {
    if ($aksi === 'tambah_kategori') {
        $nama = trim((string) ($_POST['nama_kategori'] ?? ''));
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));

        if ($nama === '') {
            kembaliKategori('error', 'Nama kategori wajib diisi.');
        }

        $cek = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE LOWER(nama_kategori) = LOWER(?)');
        $cek->execute([$nama]);
        if ((int) $cek->fetchColumn() > 0) {
            kembaliKategori('error', 'Nama kategori sudah tersedia.');
        }

        $stmt = $pdo->prepare('INSERT INTO kategori (nama_kategori, keterangan) VALUES (?, ?)');
        $stmt->execute([$nama, $keterangan]);
        kembaliKategori('success', 'Kategori berhasil ditambahkan.');
    }

    if ($aksi === 'edit_kategori') {
        $id = filter_input(INPUT_POST, 'id_kategori', FILTER_VALIDATE_INT);
        $nama = trim((string) ($_POST['nama_kategori'] ?? ''));
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));

        if (!$id || $nama === '') {
            kembaliKategori('error', 'ID dan nama kategori wajib diisi dengan benar.');
        }

        $cek = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE LOWER(nama_kategori) = LOWER(?) AND id_kategori <> ?');
        $cek->execute([$nama, $id]);
        if ((int) $cek->fetchColumn() > 0) {
            kembaliKategori('error', 'Nama kategori sudah digunakan kategori lain.');
        }

        $stmt = $pdo->prepare('UPDATE kategori SET nama_kategori = ?, keterangan = ? WHERE id_kategori = ?');
        $stmt->execute([$nama, $keterangan, $id]);
        if ($stmt->rowCount() === 0) {
            $cekId = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE id_kategori = ?');
            $cekId->execute([$id]);
            if ((int) $cekId->fetchColumn() === 0) {
                kembaliKategori('error', 'Kategori tidak ditemukan.');
            }
        }
        kembaliKategori('success', 'Kategori berhasil diperbarui.');
    }

    if ($aksi === 'hapus_kategori') {
        if ($role !== 'admin') {
            kembaliKategori('error', 'Hanya Admin yang dapat menghapus kategori.');
        }

        $id = filter_input(INPUT_POST, 'id_kategori', FILTER_VALIDATE_INT);
        if (!$id) {
            kembaliKategori('error', 'ID kategori tidak valid.');
        }

        $cek = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE id_kategori = ?');
        $cek->execute([$id]);
        if ((int) $cek->fetchColumn() > 0) {
            kembaliKategori('error', 'Kategori masih digunakan oleh barang. Pindahkan barang ke kategori lain terlebih dahulu.');
        }

        $hapus = $pdo->prepare('DELETE FROM kategori WHERE id_kategori = ?');
        $hapus->execute([$id]);
        if ($hapus->rowCount() === 0) {
            kembaliKategori('error', 'Kategori tidak ditemukan.');
        }
        kembaliKategori('success', 'Kategori berhasil dihapus.');
    }

    kembaliKategori('error', 'Aksi tidak dikenali.');
} catch (Throwable $e) {
    error_log('proseskategori.php: ' . $e->getMessage());
    kembaliKategori('error', 'Terjadi kesalahan saat memproses kategori. Periksa struktur tabel dan log PHP.');
}
