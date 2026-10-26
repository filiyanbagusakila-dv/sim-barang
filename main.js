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
