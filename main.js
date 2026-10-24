
// =============================================
// SIM-BARANG
// PERTEMUAN 4 DAN 5 - JAVASCRIPT
// =============================================


// =============================================
// 1. MENGAMBIL ELEMEN HTML
// =============================================

const formBarang = document.getElementById("formInputBarang");

const inputKode = document.getElementById("kode");
const inputNama = document.getElementById("nama");
const inputKategori = document.getElementById("kategori");
const inputJumlah = document.getElementById("jumlah");
const inputHarga = document.getElementById("harga");
const inputKondisi = document.getElementById("kondisi");

const notifikasi = document.getElementById("notifikasi");


// =============================================
// 2. FUNGSI NOTIFIKASI
// =============================================

let waktuNotifikasi;

function tampilkanNotifikasi(pesan, jenis) {

    notifikasi.textContent = pesan;

    notifikasi.className = jenis;

    notifikasi.style.display = "block";

    clearTimeout(waktuNotifikasi);

    waktuNotifikasi = setTimeout(function() {

        notifikasi.style.display = "none";
        notifikasi.textContent = "";
        notifikasi.className = "";

    }, 4000);
}


// =============================================
// 3. VALIDASI FORMULIR
// =============================================

formBarang.addEventListener("submit", function(event) {

    const kode = inputKode.value.trim();
    const nama = inputNama.value.trim();
    const kategori = inputKategori.value;
    const jumlah = inputJumlah.value;
    const harga = inputHarga.value;
    const kondisi = inputKondisi.value;

    // Validasi kode barang
    const polaKode = /^BRG-\d+$/;

    if (!polaKode.test(kode)) {

        event.preventDefault();

        tampilkanNotifikasi(
            "Kode barang harus menggunakan format BRG-001!",
            "error"
        );

        inputKode.focus();

        return;
    }

    // Validasi nama barang
    if (nama === "") {

        event.preventDefault();

        tampilkanNotifikasi(
            "Nama barang tidak boleh kosong!",
            "error"
        );

        inputNama.focus();

        return;
    }

    // Validasi kategori
    if (kategori === "") {

        event.preventDefault();

        tampilkanNotifikasi(
            "Silakan pilih kategori barang!",
            "error"
        );

        inputKategori.focus();

        return;
    }

    // Validasi jumlah barang
    if (
        jumlah === "" ||
        !Number.isFinite(Number(jumlah)) ||
        Number(jumlah) <= 0 ||
        !Number.isInteger(Number(jumlah))
    ) {

        event.preventDefault();

        tampilkanNotifikasi(
            "Jumlah barang harus berupa bilangan bulat positif!",
            "error"
        );

        inputJumlah.focus();

        return;
    }

    // Validasi harga satuan
    if (
        harga === "" ||
        !Number.isFinite(Number(harga)) ||
        Number(harga) < 0
    ) {

        event.preventDefault();

        tampilkanNotifikasi(
            "Harga satuan harus berupa angka nol atau lebih!",
            "error"
        );

        inputHarga.focus();

        return;
    }

    // Validasi kondisi
    if (kondisi === "") {

        event.preventDefault();

        tampilkanNotifikasi(
            "Silakan pilih kondisi barang!",
            "error"
        );

        inputKondisi.focus();

        return;
    }

    // Jika validasi berhasil, formulir diteruskan ke PHP.
    // Tidak menggunakan preventDefault() di sini.

});


// =============================================
// 4. PENCARIAN DATA INVENTARIS
// =============================================

const cariBarang = document.getElementById("cariBarang");

const tabelBarang = document.getElementById("tabelBarang");

const pesanKosong = document.getElementById("pesanKosong");

cariBarang.addEventListener("input", function() {

    const kataKunci = cariBarang.value.toLowerCase().trim();

    const barisBarang = tabelBarang.querySelectorAll("tr");

    let jumlahDitemukan = 0;

    barisBarang.forEach(function(baris) {

        const isiBaris = baris.textContent.toLowerCase();

        if (isiBaris.includes(kataKunci)) {

            baris.style.display = "";
            jumlahDitemukan++;

        } else {

            baris.style.display = "none";

        }

    });

    // Menampilkan pesan jika data tidak ditemukan
    if (jumlahDitemukan === 0) {

        pesanKosong.style.display = "block";

    } else {

        pesanKosong.style.display = "none";

    }

});


// =============================================
// 5. RESET FORMULIR
// =============================================

formBarang.addEventListener("reset", function() {

    clearTimeout(waktuNotifikasi);

    notifikasi.style.display = "none";

    notifikasi.textContent = "";

    notifikasi.className = "";

});
