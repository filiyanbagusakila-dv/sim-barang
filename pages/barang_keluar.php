<?php
// pages/barang_keluar.php
$dataKeluar = $pdo->query(
    'SELECT bk.id_keluar, bk.id_barang, bk.id_user, bk.jumlah, bk.tanggal,
            bk.keterangan, b.kode_barang, b.nama_barang, u.nama AS nama_petugas
     FROM barang_keluar bk
     LEFT JOIN barang b ON bk.id_barang = b.id_barang
     LEFT JOIN users u ON bk.id_user = u.id_users
     ORDER BY bk.tanggal DESC, bk.id_keluar DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$daftarBarangKeluar = $pdo->query(
    'SELECT id_barang, kode_barang, nama_barang, jumlah
     FROM barang
     ORDER BY nama_barang ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$role = strtolower((string) ($_SESSION['role'] ?? 'user'));
$isAdmin = $role === 'admin';
$pesan = $_GET['pesan'] ?? '';
$status = $_GET['status'] ?? '';
?>

<section class="content-card">
    <div class="section-heading">
        <div>
            <h2>Riwayat Barang Keluar</h2>
            <p>Daftar pengeluaran barang inventaris.</p>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKeluar">
            <i class="bi bi-plus-circle me-1"></i> Keluarkan Barang
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
               placeholder="Cari barang keluar..."
               data-table-search="tabel-keluar">
    </div>

    <div class="table-responsive">
        <table class="table align-middle" id="tabel-keluar">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataKeluar as $row): ?>
                    <tr>
                        <td><?= (int) $row['id_keluar'] ?></td>
                        <td><?= h($row['kode_barang'] ?? '-') ?></td>
                        <td><?= h($row['nama_barang'] ?? '-') ?></td>
                        <td><?= (int) $row['jumlah'] ?></td>
                        <td><?= h($row['tanggal']) ?></td>
                        <td><?= h($row['nama_petugas'] ?? '-') ?></td>
                        <td><?= h($row['keterangan']) ?></td>
                        <td>
                            <button type="button"  class="btn btn-sm btn-edit"
                                    data-bs-toggle="modal" data-bs-target="#modalEditKeluar"
                                    data-id="<?= (int) $row['id_keluar'] ?>"
                                    data-barang="<?= (int) $row['id_barang'] ?>"
                                    data-jumlah="<?= (int) $row['jumlah'] ?>"
                                    data-tanggal="<?= h(date('Y-m-d\TH:i', strtotime($row['tanggal']))) ?>"
                                    data-keterangan="<?= h($row['keterangan']) ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <?php if ($isAdmin): ?>
                                <form method="post" action="proseskeluar.php" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus transaksi barang keluar ini? Stok akan dikembalikan.');">
                                    <input type="hidden" name="aksi" value="hapus_keluar">
                                    <input type="hidden" name="id_keluar" value="<?= (int) $row['id_keluar'] ?>">
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

                <?php if (!$dataKeluar): ?>
                    <tr><td colspan="8" class="empty-state">Belum ada transaksi barang keluar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal Tambah Barang Keluar -->
<div class="modal fade" id="modalKeluar" tabindex="-1" aria-labelledby="judulModalKeluar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="proseskeluar.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulModalKeluar">Keluarkan Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah_keluar">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="keluar_id_barang" class="form-label">Pilih Barang</label>
                        <select name="id_barang" id="keluar_id_barang" class="form-select" required>
                            <option value="">-- Pilih barang --</option>
                            <?php foreach ($daftarBarangKeluar as $barang): ?>
                                <option value="<?= (int) $barang['id_barang'] ?>">
                                    <?= h($barang['kode_barang']) ?> - <?= h($barang['nama_barang']) ?>
                                    (Stok: <?= (int) $barang['jumlah'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="keluar_jumlah" class="form-label">Jumlah Keluar</label>
                        <input type="number" name="jumlah" id="keluar_jumlah" class="form-control" min="1" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="keluar_tanggal" class="form-label">Tanggal Keluar</label>
                        <input type="datetime-local" name="tanggal" id="keluar_tanggal"
                               class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="keluar_keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="keluar_keterangan" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Barang Keluar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Barang Keluar -->
<div class="modal fade" id="modalEditKeluar" tabindex="-1" aria-labelledby="judulModalEditKeluar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="proseskeluar.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulModalEditKeluar">Edit Barang Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="edit_keluar">
                    <input type="hidden" name="id_keluar" id="edit_keluar_id">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="edit_keluar_barang" class="form-label">Pilih Barang</label>
                        <select name="id_barang" id="edit_keluar_barang" class="form-select" required>
                            <option value="">-- Pilih barang --</option>
                            <?php foreach ($daftarBarangKeluar as $barang): ?>
                                <option value="<?= (int) $barang['id_barang'] ?>">
                                    <?= h($barang['kode_barang']) ?> - <?= h($barang['nama_barang']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keluar_jumlah" class="form-label">Jumlah Keluar</label>
                        <input type="number" name="jumlah" id="edit_keluar_jumlah" class="form-control" min="1" step="1" required>
                        <small class="text-muted">Stok disesuaikan otomatis berdasarkan perubahan transaksi.</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keluar_tanggal" class="form-label">Tanggal Keluar</label>
                        <input type="datetime-local" name="tanggal" id="edit_keluar_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keluar_keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="edit_keluar_keterangan" class="form-control" rows="3" required></textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalEditKeluar');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;
        document.getElementById('edit_keluar_id').value = button.getAttribute('data-id') || '';
        document.getElementById('edit_keluar_barang').value = button.getAttribute('data-barang') || '';
        document.getElementById('edit_keluar_jumlah').value = button.getAttribute('data-jumlah') || '';
        document.getElementById('edit_keluar_tanggal').value = button.getAttribute('data-tanggal') || '';
        document.getElementById('edit_keluar_keterangan').value = button.getAttribute('data-keterangan') || '';
    });
});
</script>
