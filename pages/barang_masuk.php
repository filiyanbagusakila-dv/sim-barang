<?php
// pages/barang_masuk.php
// File ini dipanggil melalui index.php?page=barang_masuk

$dataMasuk = $pdo->query(
    'SELECT bm.id_masuk, bm.id_barang, bm.id_user, bm.jumlah, bm.tanggal,
            b.kode_barang, b.nama_barang, u.nama AS nama_petugas
     FROM barang_masuk bm
     LEFT JOIN barang b ON bm.id_barang = b.id_barang
     LEFT JOIN users u ON bm.id_user = u.id_users
     ORDER BY bm.tanggal DESC, bm.id_masuk DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$daftarBarang = $pdo->query(
    'SELECT id_barang, kode_barang, nama_barang, jumlah
     FROM barang
     ORDER BY nama_barang ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$role = $_SESSION['role'] ?? 'User';
$isAdmin = strtolower((string) $role) === 'admin';
$pesan = $_GET['pesan'] ?? '';
$status = $_GET['status'] ?? '';
?>

<section class="content-card">
    <div class="section-heading">
        <div>
            <h2>Riwayat Barang Masuk</h2>
            <p>Daftar penerimaan barang inventaris.</p>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMasuk">
            <i class="bi bi-plus-circle me-1"></i> Masukkan Barang
        </button>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= h($pesan) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <div class="table-toolbar">
        <input type="search" class="form-control search-input"
               placeholder="Cari barang masuk..."
               data-table-search="tabel-masuk">
    </div>

    <div class="table-responsive">
        <table class="table align-middle" id="tabel-masuk">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Masuk</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataMasuk as $row): ?>
                    <tr>
                        <td><?= (int) $row['id_masuk'] ?></td>
                        <td><?= h($row['kode_barang'] ?? '-') ?></td>
                        <td><?= h($row['nama_barang'] ?? '-') ?></td>
                        <td><?= (int) $row['jumlah'] ?></td>
                        <td><?= h($row['tanggal']) ?></td>
                        <td><?= h($row['nama_petugas'] ?? '-') ?></td>
                        <td>
                            <button type="button"
                                    class="btn btn-sm btn-edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditMasuk"
                                    data-id="<?= (int) $row['id_masuk'] ?>"
                                    data-barang="<?= (int) $row['id_barang'] ?>"
                                    data-jumlah="<?= (int) $row['jumlah'] ?>"
                                    data-tanggal="<?= h(date('Y-m-d\TH:i', strtotime($row['tanggal']))) ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <?php if ($isAdmin): ?>
                                <form method="post" action="prosesmasuk.php" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus transaksi barang masuk ini? Stok akan dikurangi kembali.');">
                                    <input type="hidden" name="aksi" value="hapus_masuk">
                                    <input type="hidden" name="id_masuk" value="<?= (int) $row['id_masuk'] ?>">
                                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-sm btn-delete">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$dataMasuk): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Belum ada transaksi barang masuk.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal Tambah Barang Masuk -->
<div class="modal fade" id="modalMasuk" tabindex="-1" aria-labelledby="judulModalMasuk" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="prosesmasuk.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulModalMasuk">Masukkan Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah_masuk">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="barang_masuk_id_barang" class="form-label">Pilih Barang</label>
                        <select name="id_barang" id="barang_masuk_id_barang" class="form-select" required>
                            <option value="">-- Pilih barang --</option>
                            <?php foreach ($daftarBarang as $barang): ?>
                                <option value="<?= (int) $barang['id_barang'] ?>">
                                    <?= h($barang['kode_barang']) ?> - <?= h($barang['nama_barang']) ?>
                                    (Stok: <?= (int) $barang['jumlah'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="barang_masuk_jumlah" class="form-label">Jumlah Masuk</label>
                        <input type="number" name="jumlah" id="barang_masuk_jumlah"
                               class="form-control" min="1" step="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="barang_masuk_tanggal" class="form-label">Tanggal Masuk</label>
                        <input type="datetime-local" name="tanggal" id="barang_masuk_tanggal"
                               class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Barang Masuk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Barang Masuk -->
<div class="modal fade" id="modalEditMasuk" tabindex="-1" aria-labelledby="judulModalEditMasuk" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="prosesmasuk.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulModalEditMasuk">Edit Barang Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="aksi" value="edit_masuk">
                    <input type="hidden" name="id_masuk" id="edit_masuk_id">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="edit_masuk_barang" class="form-label">Pilih Barang</label>
                        <select name="id_barang" id="edit_masuk_barang" class="form-select" required>
                            <option value="">-- Pilih barang --</option>
                            <?php foreach ($daftarBarang as $barang): ?>
                                <option value="<?= (int) $barang['id_barang'] ?>">
                                    <?= h($barang['kode_barang']) ?> - <?= h($barang['nama_barang']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_masuk_jumlah" class="form-label">Jumlah Masuk</label>
                        <input type="number" name="jumlah" id="edit_masuk_jumlah"
                               class="form-control" min="1" step="1" required>
                        <small class="text-muted">Stok akan disesuaikan berdasarkan perubahan transaksi.</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_masuk_tanggal" class="form-label">Tanggal Masuk</label>
                        <input type="datetime-local" name="tanggal" id="edit_masuk_tanggal"
                               class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../main.js"></script>

