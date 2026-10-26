<?php
// pages/users.php
// Halaman ini sebaiknya hanya dapat dibuka Admin; akses juga dicek di index.php.
$role = strtolower((string) ($_SESSION['role'] ?? 'user'));
if ($role !== 'admin') {
    echo '<section class="content-card"><div class="alert alert-danger mb-0">Akses ditolak. Halaman ini hanya untuk Admin.</div></section>';
    return;
}

$userList = $pdo->query(
    'SELECT id_users, nama, username, role
     FROM users
     ORDER BY id_users DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$pesan = $_GET['pesan'] ?? '';
$status = $_GET['status'] ?? '';
?>

<section class="content-card">
    <div class="section-heading">
        <div>
            <h2>Data Pengguna</h2>
            <p>Daftar akun yang terdaftar pada sistem.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
        </button>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert <?= $status === 'success' ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
            <?= h($pesan) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table align-middle" id="tabel-users">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userList as $row): ?>
                    <tr>
                        <td><?= (int) $row['id_users'] ?></td>
                        <td><?= h($row['nama']) ?></td>
                        <td><?= h($row['username']) ?></td>
                        <td>
                            <span class="status-badge">
                                <?= h($row['role']) ?>
                            </span>
                        </td>

                        <td>
                            <!-- Tombol Edit: tetap memakai modal lama -->
                            <button type="button"
                                class="btn btn-sm btn-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditUser"
                                data-id="<?= (int) $row['id_users'] ?>"
                                data-nama="<?= h($row['nama']) ?>"
                                data-username="<?= h($row['username']) ?>"
                                data-role="<?= h($row['role']) ?>">

                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <form method="post"
                                action="prosesusers.php"
                                class="d-inline"
                                onsubmit="return confirm('Yakin ingin menghapus akun <?= h($row['username']) ?>?');">

                                <input type="hidden" name="aksi" value="hapus_user">
                                <input type="hidden" name="id_users"
                                    value="<?= (int) $row['id_users'] ?>">

                                <?php if (!empty($_SESSION['csrf_token'])): ?>
                                    <input type="hidden" name="csrf_token"
                                        value="<?= h($_SESSION['csrf_token']) ?>">
                                <?php endif; ?>

                                <button type="submit" class="btn btn-sm btn-delete">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$userList): ?>
                    <tr>
                        <td colspan="5" class="empty-state">Belum ada pengguna.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="judulTambahUser" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="prosesusers.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulTambahUser">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah_user">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="user_nama" class="form-label">Nama</label>
                        <input type="text" name="nama" id="user_nama" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_username" class="form-label">Username</label>
                        <input type="text" name="username" id="user_username" class="form-control" maxlength="50" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_password" class="form-label">Password</label>
                        <input type="password" name="password" id="user_password" class="form-control" minlength="8" autocomplete="new-password" required>
                        <small class="text-muted">Minimal 8 karakter.</small>
                    </div>
                    <div class="mb-3">
                        <label for="user_role" class="form-label">Role</label>
                        <select name="role" id="user_role" class="form-select" required>
                            <option value="User">User / Petugas</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="judulEditUser" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="prosesusers.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="judulEditUser">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="edit_user">
                    <input type="hidden" name="id_users" id="edit_user_id">
                    <?php if (!empty($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="edit_user_nama" class="form-label">Nama</label>
                        <input type="text" name="nama" id="edit_user_nama" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_username" class="form-label">Username</label>
                        <input type="text" name="username" id="edit_user_username" class="form-control" maxlength="50" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_password" class="form-label">Password Baru (opsional)</label>
                        <input type="password" name="password" id="edit_user_password" class="form-control" minlength="8" autocomplete="new-password">
                        <small class="text-muted">Kosongkan jika password tidak ingin diubah.</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_role" class="form-label">Role</label>
                        <select name="role" id="edit_user_role" class="form-select" required>
                            <option value="User">User / Petugas</option>
                            <option value="Admin">Admin</option>
                        </select>
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
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalEditUser');
        if (!modal) return;
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            document.getElementById('edit_user_id').value = button.getAttribute('data-id') || '';
            document.getElementById('edit_user_nama').value = button.getAttribute('data-nama') || '';
            document.getElementById('edit_user_username').value = button.getAttribute('data-username') || '';
            document.getElementById('edit_user_role').value = button.getAttribute('data-role') || 'User';
            document.getElementById('edit_user_password').value = '';
        });
    });
</script>