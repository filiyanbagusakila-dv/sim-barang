<?php
// prosesusers.php
session_start();
require_once __DIR__ . '/koneksi.php';

function kembaliUsers(string $status, string $pesan): never
{
    header('Location: index.php?page=users&status=' . rawurlencode($status) .
        '&pesan=' . rawurlencode($pesan));
    exit;
}

if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}
if (strtolower((string) $_SESSION['role']) !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak. Hanya Admin yang dapat mengelola pengguna.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=users');
    exit;
}
if (!empty($_SESSION['csrf_token'])) {
    $tokenPost = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals((string) $_SESSION['csrf_token'], $tokenPost)) {
        kembaliUsers('error', 'Permintaan tidak valid. Silakan muat ulang halaman.');
    }
}

$aksi = (string) ($_POST['aksi'] ?? '');
$idLogin = (int) ($_SESSION['id_users'] ?? 0);

try {
    if ($aksi === 'tambah_user') {
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $roleBaru = (string) ($_POST['role'] ?? 'User');

        if ($nama === '' || $username === '' || $password === '') {
            kembaliUsers('error', 'Nama, username, dan password wajib diisi.');
        }
        if (strlen($password) < 8) {
            kembaliUsers('error', 'Password minimal 8 karakter.');
        }
        if (!in_array($roleBaru, ['Admin', 'User'], true)) {
            kembaliUsers('error', 'Role yang dipilih tidak valid.');
        }

        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $cek->execute([$username]);
        if ((int) $cek->fetchColumn() > 0) {
            kembaliUsers('error', 'Username sudah digunakan.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nama, $username, $hash, $roleBaru]);
        kembaliUsers('success', 'Pengguna berhasil ditambahkan.');
    }

    if ($aksi === 'edit_user') {
        $id = filter_input(INPUT_POST, 'id_users', FILTER_VALIDATE_INT);
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $roleBaru = (string) ($_POST['role'] ?? 'User');

        if (!$id || $nama === '' || $username === '') {
            kembaliUsers('error', 'ID, nama, dan username wajib diisi.');
        }
        if (!in_array($roleBaru, ['Admin', 'User'], true)) {
            kembaliUsers('error', 'Role yang dipilih tidak valid.');
        }
        if ($password !== '' && strlen($password) < 8) {
            kembaliUsers('error', 'Password baru minimal 8 karakter.');
        }

        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id_users <> ?');
        $cek->execute([$username, $id]);
        if ((int) $cek->fetchColumn() > 0) {
            kembaliUsers('error', 'Username sudah digunakan akun lain.');
        }

        // Cegah admin menurunkan role akun yang sedang dipakai sehingga sesi aktif tetap konsisten.
        if ($id === $idLogin && $roleBaru !== 'Admin') {
            kembaliUsers('error', 'Role akun Admin yang sedang digunakan tidak dapat diubah menjadi User.');
        }

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET nama = ?, username = ?, password = ?, role = ? WHERE id_users = ?');
            $stmt->execute([$nama, $username, $hash, $roleBaru, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET nama = ?, username = ?, role = ? WHERE id_users = ?');
            $stmt->execute([$nama, $username, $roleBaru, $id]);
        }

        if ($stmt->rowCount() === 0) {
            $cekId = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id_users = ?');
            $cekId->execute([$id]);
            if ((int) $cekId->fetchColumn() === 0) {
                kembaliUsers('error', 'Pengguna tidak ditemukan.');
            }
        }
        kembaliUsers('success', 'Data pengguna berhasil diperbarui.');
    }

    if ($aksi === 'hapus_user') {
        $id = filter_input(INPUT_POST, 'id_users', FILTER_VALIDATE_INT);
        if (!$id) {
            kembaliUsers('error', 'ID pengguna tidak valid.');
        }
        if ($id === $idLogin) {
            kembaliUsers('error', 'Kamu tidak bisa menghapus akun yang sedang digunakan.');
        }

        // Transaksi menyimpan referensi user; jangan menghapus akun yang masih punya riwayat.
        $cekMasuk = $pdo->prepare('SELECT COUNT(*) FROM barang_masuk WHERE id_user = ?');
        $cekMasuk->execute([$id]);
        $cekKeluar = $pdo->prepare('SELECT COUNT(*) FROM barang_keluar WHERE id_user = ?');
        $cekKeluar->execute([$id]);
        if ((int) $cekMasuk->fetchColumn() > 0 || (int) $cekKeluar->fetchColumn() > 0) {
            kembaliUsers('error', 'Pengguna memiliki riwayat transaksi. Ubah role atau pertahankan akun agar riwayat tetap terjaga.');
        }

        $stmt = $pdo->prepare('DELETE FROM users WHERE id_users = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            kembaliUsers('error', 'Pengguna tidak ditemukan.');
        }
        kembaliUsers('success', 'Pengguna berhasil dihapus.');
    }

    kembaliUsers('error', 'Aksi tidak dikenali.');
} catch (Throwable $e) {
    error_log('prosesusers.php: ' . $e->getMessage());
    kembaliUsers('error', 'Terjadi kesalahan saat memproses pengguna. Periksa struktur tabel dan log PHP.');
}
