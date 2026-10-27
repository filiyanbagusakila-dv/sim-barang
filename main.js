document.addEventListener("DOMContentLoaded", function () {
  // 1. Validasi login
  const loginForm = document.querySelector("#login-form");

  if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
      const username = loginForm
        .querySelector('[name="username"]')
        .value.trim();

      const password = loginForm.querySelector('[name="password"]').value;

      if (!username || !password) {
        event.preventDefault();
        alert("Username dan password wajib diisi.");
      }
    });
  }

  const formBarang = document.querySelector("#form-barang");

  if (formBarang) {
    formBarang.addEventListener("submit", function (event) {
      const kodeInput = formBarang.querySelector("#kode_barang");
      const jumlahInput = formBarang.querySelector("#jumlah");

      const angkaKode = kodeInput.value.trim();
      const jumlahText = jumlahInput.value.trim();
      const jumlah = Number(jumlahText);

      if (!/^[0-9]+$/.test(angkaKode)) {
        event.preventDefault();
        alert("Masukkan angka kode barang, contoh 001.");
        kodeInput.focus();
        return;
      }

      if (jumlahText === "" || !Number.isFinite(jumlah) || jumlah <= 0) {
        event.preventDefault();
        alert("Jumlah barang harus lebih dari 0.");
        jumlahInput.focus();
        return;
      }

      // Ubah nilai input menjadi kode lengkap sebelum dikirim.
      kodeInput.value = "BRG-" + angkaKode;
    });
  }

  // 3. Pencarian tabel inventaris secara real-time
  document.querySelectorAll("[data-table-search]").forEach(function (input) {
    input.addEventListener("input", function () {
      const tabel = document.getElementById(input.dataset.tableSearch);

      if (!tabel) return;

      const kataKunci = input.value.toLowerCase().trim();

      tabel.querySelectorAll("tbody tr").forEach(function (baris) {
        if (baris.querySelector(".empty-state")) return;

        baris.hidden = !baris.textContent.toLowerCase().includes(kataKunci);
      });
    });
  });

  // 4. Konfirmasi logout
  document.querySelectorAll("[data-confirm-logout]").forEach(function (tombol) {
    tombol.addEventListener("click", function (event) {
      if (!window.confirm("Yakin ingin logout?")) {
        event.preventDefault();
      }
    });
  });
});
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
document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('modalEditBarang');

                if (modal && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
document.addEventListener('DOMContentLoaded', function () {
    const modalEdit = document.getElementById('modalEditMasuk');
    if (!modalEdit) return;

    modalEdit.addEventListener('show.bs.modal', function (event) {
        const tombol = event.relatedTarget;
        if (!tombol) return;

        document.getElementById('edit_masuk_id').value = tombol.getAttribute('data-id') || '';
        document.getElementById('edit_masuk_barang').value = tombol.getAttribute('data-barang') || '';
        document.getElementById('edit_masuk_jumlah').value = tombol.getAttribute('data-jumlah') || '';
        document.getElementById('edit_masuk_tanggal').value = tombol.getAttribute('data-tanggal') || '';
    });
});
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
document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');

            if (passwordInput && toggleButton) {
                toggleButton.addEventListener('click', function () {
                    const isPassword =
                        passwordInput.type === 'password';

                    passwordInput.type = isPassword
                        ? 'text'
                        : 'password';

                    toggleButton.textContent = isPassword
                        ? 'Sembunyikan'
                        : 'Lihat';

                    toggleButton.setAttribute(
                        'aria-pressed',
                        String(isPassword)
                    );

                    toggleButton.setAttribute(
                        'aria-label',
                        isPassword
                            ? 'Sembunyikan password'
                            : 'Tampilkan password'
                    );
                });
            }
        });