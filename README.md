# SIM-Barang
## Sistem Pengelolaan Inventaris Barang Jurusan

### 1. Gambaran Umum Proyek

SIM-Barang (Sistem Pengelolaan Inventaris Barang Jurusan) merupakan proyek pengembangan aplikasi berbasis web yang digunakan untuk membantu pencatatan dan pengelolaan inventaris barang di lingkungan jurusan atau laboratorium. Sistem ini dirancang agar data barang dapat dikelola secara lebih terstruktur dan mudah dipantau.

Pengembangan proyek dilakukan secara bertahap menggunakan HTML, CSS, JavaScript, PHP, dan MySQL.

### 2. Konsep HTTP Request

HTTP Request adalah permintaan yang dikirimkan oleh client, seperti browser, kepada web server untuk meminta atau mengirimkan data. Contohnya, ketika pengguna membuka halaman SIM-Barang, browser mengirimkan permintaan kepada server agar halaman tersebut dapat ditampilkan.

HTTP Request memiliki beberapa metode, antara lain:
- **GET:** Digunakan untuk meminta atau mengambil data dari server.
- **POST:** Digunakan untuk mengirimkan data ke server, misalnya ketika pengguna mengisi formulir barang.

### 3. Konsep HTTP Response

HTTP Response adalah tanggapan yang dikirimkan oleh server kepada client setelah server menerima dan memproses HTTP Request. Respons tersebut dapat berupa halaman HTML, data, atau pesan status yang menunjukkan hasil pemrosesan permintaan.

Contoh kode status HTTP:
- **200 OK:** Permintaan berhasil diproses.
- **404 Not Found:** Halaman atau sumber daya yang diminta tidak ditemukan.
- **500 Internal Server Error:** Terjadi kesalahan pada server.

### 4. Alur Kerja HTTP pada SIM-Barang

Alur kerja HTTP pada SIM-Barang dimulai ketika pengguna membuka website melalui browser. Browser mengirimkan HTTP Request ke server. Server kemudian memproses permintaan dan mengirimkan HTTP Response kembali ke browser untuk ditampilkan kepada pengguna.

Pada pengembangan selanjutnya, proses tersebut digunakan untuk menampilkan data inventaris, menerima input barang, serta mengolah data menggunakan PHP dan MySQL.

### 5. Teknologi yang Digunakan

- **HTML5:** Membuat struktur halaman website.
- **CSS3:** Mengatur tampilan dan tata letak website.
- **JavaScript:** Menambahkan interaktivitas dan validasi formulir.
- **PHP:** Memproses data pada sisi server.
- **MySQL:** Menyimpan dan mengelola data inventaris.
- **Git dan GitHub:** Mengelola versi kode program dan menyimpan repositori proyek.

### 6. Tujuan Proyek

Proyek SIM-Barang bertujuan untuk menerapkan konsep dasar pemrograman web secara bertahap, mulai dari pembuatan antarmuka, validasi formulir, pemrosesan data pada server, hingga integrasi dengan basis data.