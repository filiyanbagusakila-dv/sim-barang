<?php
// pages/kategori.php
$kategoriList = $pdo->query(
    'SELECT id_kategori, nama_kategori, keterangan
     FROM kategori
     ORDER BY id_kategori DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$role = strtolower((string) ($_SESSION['role'] ?? 'user'));
$isAdmin = $role === 'admin';
$pesan = $_GET['pesan'] ?? '';
$status = $_GET['status'] ?? '';
?>

<section class="content-card">
    <div class="section-heading">
        <div>
            <h2>Kategori Barang</h2>
            <p>Daftar kategori inventaris yang tersedia.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
        </button>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= h($pesan) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table align-middle" id="tabel-kategori">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kategoriList as $row): ?>
                    <tr>
                        <td><?= (int) $row['id_kategori'] ?></td>
                        <td><?= h($row['nama_kategori']) ?></td>
                        <td><?= h($row['keterangan'] ?? '') ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-edit"
                                    data-bs-toggle="modal" data-bs-target="#modalEditKategori"
                                    data-id="<?= (int) $row['id_kategori'] ?>"
                                    data-nama="<?= h($row['nama_kategori']) ?>"
                                    data-keterangan="<?= h($row['keterangan'] ?? '') ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <?php if ($isAdmin): ?>
                                <form method="post" action="proseskategori.php" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus kategori ini? Kategori yang masih dipakai barang tidak bisa dihapus.');">
                                    <input type="hidden" name="aksi" value="hapus_kategori">
                                    <input type="hidden" name="id_kategori" value="<?= (int) $row['id_kategori'] ?>">
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

                <?php if (!$kategoriList): ?>
                    <tr>
                        <td colspan="4" class="empty-state">Belum ada kategori barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="judulTambahKategori" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="proseskategori.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulTambahKategori">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah_kategori">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan_kategori" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="keterangan_kategori" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="judulEditKategori" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="proseskategori.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulEditKategori">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="edit_kategori">
                    <input type="hidden" name="id_kategori" id="edit_id_kategori">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="edit_nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="edit_nama_kategori" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keterangan_kategori" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan_kategori" class="form-control" rows="3"></textarea>
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
    const modal = document.getElementById('modalEditKategori');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;
        document.getElementById('edit_id_kategori').value = button.getAttribute('data-id') || '';
        document.getElementById('edit_nama_kategori').value = button.getAttribute('data-nama') || '';
        document.getElementById('edit_keterangan_kategori').value = button.getAttribute('data-keterangan') || '';
    });
});
</script>
