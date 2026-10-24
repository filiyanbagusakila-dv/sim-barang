
// =====================================
// 1. MENGAMBIL ELEMEN HTML
// =====================================

const formBarang = document.getElementById("formInputBarang");

const inputKode = document.getElementById("kode");
const inputNama = document.getElementById("nama");
const inputKategori = document.getElementById("kategori");
const inputJumlah = document.getElementById("jumlah");
const inputKondisi = document.getElementById("kondisi");

const notifikasi = document.getElementById("notifikasi");


// =====================================
// 2. FUNGSI MENAMPILKAN NOTIFIKASI
// =====================================

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

    }, 3000);
}


// =====================================
// 3. VALIDASI FORMULIR BARANG
// =====================================

formBarang.addEventListener("submit", function(event) {

    // Mencegah halaman melakukan reload
    event.preventDefault();

    // Mengambil nilai input
    const kode = inputKode.value.trim();
    const nama = inputNama.value.trim();
    const kategori = inputKategori.value;
    const jumlah = inputJumlah.value;
    const kondisi = inputKondisi.value;

    // Validasi format kode barang
    const polaKode = /^BRG-\d+$/;

    if (!polaKode.test(kode)) {

        tampilkanNotifikasi(
            "Kode barang harus diawali BRG- dan diikuti angka. Contoh: BRG-001.",
            "error"
        );

        inputKode.focus();
        return;
    }

    // Validasi nama barang
    if (nama === "") {

        tampilkanNotifikasi(
            "Nama barang tidak boleh kosong!",
            "error"
        );

        inputNama.focus();
        return;
    }

    // Validasi kategori
    if (kategori === "") {

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
        Number(jumlah) <= 0
    ) {

        tampilkanNotifikasi(
            "Jumlah barang harus berupa angka positif lebih dari 0!",
            "error"
        );

        inputJumlah.focus();
        return;
    }

    // Validasi kondisi barang
    if (kondisi === "") {

        tampilkanNotifikasi(
            "Silakan pilih kondisi barang!",
            "error"
        );

        inputKondisi.focus();
        return;
    }

    // Jika seluruh validasi berhasil
    tampilkanNotifikasi(
        "Formulir berhasil divalidasi! Data siap diproses.",
        "sukses"
    );

    // Mengosongkan formulir
    formBarang.reset();

});


// =====================================
// 4. PENCARIAN DATA INVENTARIS
// =====================================

const cariBarang = document.getElementById("cariBarang");

const tabelBarang = document.getElementById("tabelBarang");

const pesanKosong = document.getElementById("pesanKosong");

cariBarang.addEventListener("input", function() {

    // Mengambil kata yang dicari
    const kataKunci = cariBarang.value.toLowerCase().trim();

    // Mengambil seluruh baris tabel
    const barisBarang = tabelBarang.querySelectorAll("tr");

    let jumlahDitemukan = 0;

    barisBarang.forEach(function(baris) {

        // Mengambil isi setiap baris
        const isiBaris = baris.textContent.toLowerCase();

        // Memeriksa apakah data cocok dengan pencarian
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


// =====================================
// 5. RESET NOTIFIKASI
// =====================================

formBarang.addEventListener("reset", function() {

    notifikasi.style.display = "none";
    notifikasi.textContent = "";
    notifikasi.className = "";

});
