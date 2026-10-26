<?php
$role = $_SESSION['role'] ?? ($role ?? '');
$role = trim((string) $role);
$isAdmin = strtolower($role) === 'admin';
$totalJumlah = 0;
$totalAset = 0;

if (!isset($pdo)) {
    exit('Koneksi database tidak tersedia.');
}

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

// Ambil data barang dan kategori
$dataBarang = $pdo->query(
    'SELECT b.id_barang, b.kode_barang, b.nama_barang,
            b.id_kategori, b.jumlah, b.kondisi, b.harga_satuan,
            k.nama_kategori
     FROM barang b
     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
     ORDER BY b.id_barang DESC'
)->fetchAll(PDO::FETCH_ASSOC);

// Hitung total estimasi nilai seluruh aset: jumlah x harga satuan.
$totalAset = 0;
foreach ($dataBarang as $itemAset) {
    $totalAset += (int) ($itemAset['jumlah'] ?? 0)
        * (float) ($itemAset['harga_satuan'] ?? 0);
}

$kategoriList = $pdo->query(
    'SELECT id_kategori, nama_kategori
     FROM kategori
     ORDER BY nama_kategori'
)->fetchAll(PDO::FETCH_ASSOC);

// Ambil data barang yang akan diedit
$barangEdit = null;

if ($isAdmin && isset($_GET['edit'])) {
    $idEdit = filter_var($_GET['edit'], FILTER_VALIDATE_INT);

    if ($idEdit && $idEdit > 0) {
        $stmt = $pdo->prepare(
            'SELECT * FROM barang WHERE id_barang = ?'
        );

        $stmt->execute([$idEdit]);
        $barangEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
$totalJumlah = 0;
$totalAset = 0;

foreach ($dataBarang as $barang) {
    $jumlah = (int) $barang['jumlah'];
    $harga = (float) $barang['harga_satuan'];

    $totalJumlah += $jumlah;
    $totalAset += $jumlah * $harga;
}
?>

<section class="content-card">
    <div class="section-heading">
        <div>
            <h2>Tabel Inventaris Barang</h2>
            <p>Kelola data inventaris yang tersimpan di database.</p>
        </div>

        <?php if ($isAdmin): ?>
            <button
                class="btn btn-primary"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#modalBarang">
                + Tambah Barang
            </button>
        <?php endif; ?>
    </div>

    <div class="table-toolbar">
        <input
            type="search"
            class="form-control search-input"
            placeholder="Cari kode atau nama barang..."
            data-table-search="tabel-barang">
    </div>

    <div class="table-responsive">
        <table class="table align-middle" id="tabel-barang">
            <thead>
                <tr>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Kondisi</th>
                    <th>Harga Satuan</th>
                    <th>Estimasi Nilai</th>

                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($dataBarang)): ?>

                    <?php foreach ($dataBarang as $barang): ?>
                        <tr>
                            <td>
                                <?= h($barang['kode_barang']) ?>
                            </td>

                            <td>
                                <?= h($barang['nama_barang']) ?>
                            </td>

                            <td>
                                <?= h($barang['nama_kategori'] ?? '-') ?>
                            </td>

                            <td>
                                <?= number_format(
                                    (int) $barang['jumlah'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>

                            <td>
                                <span class="status-badge">
                                    <?= h($barang['kondisi']) ?>
                                </span>
                            </td>

                            <td>
                                Rp <?= number_format(
                                        (float) $barang['harga_satuan'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                            </td>

                            <td>
                                Rp <?= number_format(
                                        (float) $barang['jumlah']
                                            * (float) $barang['harga_satuan'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                            </td>

                            <td>
                                <div class="action-buttons">

                                    <a
                                        class="btn btn-sm btn-edit"
                                        href="index.php?page=barang&amp;edit=<?= (int) $barang['id_barang'] ?>">
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="index.php?page=barang"
                                        onsubmit="return confirm('Yakin ingin menghapus barang ini?');">
                                        <input
                                            type="hidden"
                                            name="aksi"
                                            value="hapus_barang">

                                        <input
                                            type="hidden"
                                            name="id_barang"
                                            value="<?= (int) $barang['id_barang'] ?>">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= h($_SESSION['csrf_token'] ?? '') ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-delete">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-total">
                        <td colspan="3" class="text-end">
                            <strong>TOTAL KESELURUHAN</strong>
                        </td>

                        <td>
                            <strong>
                                <?= number_format($totalJumlah, 0, ',', '.') ?>
                            </strong>
                        </td>

                        <td colspan="2"></td>

                        <td>
                            <strong>
                                Rp <?= number_format($totalAset, 0, ',', '.') ?>
                            </strong>
                        </td>

                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td
                            colspan="8"
                            class="empty-state">
                            Belum ada data barang.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($isAdmin): ?>

    <!-- MODAL TAMBAH BARANG -->
    <div
        class="modal fade"
        id="modalBarang"
        tabindex="-1"
        aria-labelledby="judulModalBarang"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" action="index.php?page=barang">
                    <input type="hidden" name="aksi" value="tambah_barang">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= h($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="modal-header">
                        <h5 class="modal-title" id="judulModalBarang">
                            Tambah Barang
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body form-grid">
                        <div>
                            <label class="form-label" for="kode_barang">
                                Kode Barang
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">BRG-</span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="kode_barang"
                                    name="kode_barang"
                                    placeholder="001"
                                    pattern="[0-9]+"
                                    inputmode="numeric"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="nama_barang">
                                Nama Barang
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nama_barang"
                                name="nama_barang"
                                required>
                        </div>

                        <div>
                            <label class="form-label" for="id_kategori">
                                Kategori
                            </label>

                            <select
                                class="form-select"
                                id="id_kategori"
                                name="id_kategori"
                                required>
                                <option value="">Pilih kategori</option>

                                <?php foreach ($kategoriList as $kategori): ?>
                                    <option value="<?= (int) $kategori['id_kategori'] ?>">
                                        <?= h($kategori['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="form-label" for="jumlah">
                                Jumlah
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="jumlah"
                                name="jumlah"
                                min="1"
                                step="1"
                                required>
                        </div>

                        <div>
                            <label class="form-label" for="kondisi">
                                Kondisi
                            </label>

                            <select
                                class="form-select"
                                id="kondisi"
                                name="kondisi"
                                required>
                                <option value="">Pilih kondisi</option>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label" for="harga_satuan">
                                Harga Satuan (Rp)
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="harga_satuan"
                                name="harga_satuan"
                                min="0"
                                step="0.01"
                                required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Simpan Barang
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- MODAL EDIT BARANG -->
    <?php if ($barangEdit): ?>
        <div
            class="modal fade"
            id="modalEditBarang"
            tabindex="-1"
            aria-labelledby="judulModalEdit"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <form method="POST" action="index.php?page=barang">
                        <input type="hidden" name="aksi" value="edit_barang">

                        <input
                            type="hidden"
                            name="id_barang"
                            value="<?= (int) $barangEdit['id_barang'] ?>">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= h($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="modal-header">
                            <h5 class="modal-title" id="judulModalEdit">
                                Edit Barang
                            </h5>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Tutup"></button>
                        </div>

                        <div class="modal-body form-grid">
                            <div>
                                <label class="form-label" for="edit_kode">
                                    Kode Barang
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="edit_kode"
                                    name="kode_barang"
                                    value="<?= h($barangEdit['kode_barang']) ?>"
                                    required>
                            </div>

                            <div>
                                <label class="form-label" for="edit_nama">
                                    Nama Barang
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="edit_nama"
                                    name="nama_barang"
                                    value="<?= h($barangEdit['nama_barang']) ?>"
                                    required>
                            </div>

                            <div>
                                <label class="form-label" for="edit_kategori">
                                    Kategori
                                </label>

                                <select
                                    class="form-select"
                                    id="edit_kategori"
                                    name="id_kategori"
                                    required>
                                    <option value="">Pilih kategori</option>

                                    <?php foreach ($kategoriList as $kategori): ?>
                                        <option
                                            value="<?= (int) $kategori['id_kategori'] ?>"
                                            <?= (int) $barangEdit['id_kategori'] === (int) $kategori['id_kategori'] ? 'selected' : '' ?>>
                                            <?= h($kategori['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="edit_jumlah">
                                    Jumlah
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="edit_jumlah"
                                    name="jumlah"
                                    min="1"
                                    step="1"
                                    value="<?= (int) $barangEdit['jumlah'] ?>"
                                    required>
                            </div>

                            <div>
                                <label class="form-label" for="edit_kondisi">
                                    Kondisi
                                </label>

                                <select
                                    class="form-select"
                                    id="edit_kondisi"
                                    name="kondisi"
                                    required>
                                    <?php
                                    $pilihanKondisi = [
                                        'Baik',
                                        'Rusak Ringan',
                                        'Rusak Berat'
                                    ];
                                    ?>

                                    <?php foreach ($pilihanKondisi as $pilihan): ?>
                                        <option
                                            value="<?= h($pilihan) ?>"
                                            <?= $barangEdit['kondisi'] === $pilihan ? 'selected' : '' ?>>
                                            <?= h($pilihan) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="edit_harga">
                                    Harga Satuan (Rp)
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="edit_harga"
                                    name="harga_satuan"
                                    min="0"
                                    step="0.01"
                                    value="<?= h($barangEdit['harga_satuan']) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <a
                                href="index.php?page=barang"
                                class="btn btn-light">
                                Batal
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('modalEditBarang');

                if (modal && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    <?php endif; ?>

<?php endif; ?>