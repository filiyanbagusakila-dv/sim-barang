
<?php

// =====================================
// CLASS BARANG
// =====================================

class Barang {

    // Property
    public $kode;
    public $nama;
    public $jumlah;

    // Constructor
    public function __construct($kode, $nama, $jumlah) {

        $this->kode = $kode;
        $this->nama = $nama;
        $this->jumlah = $jumlah;

    }

    // Method menampilkan informasi barang
    public function tampilkanInformasi() {

        return "
            <div class='info-barang'>
                <h3>Informasi Barang</h3>

                <p><strong>Kode:</strong> " .
                    htmlspecialchars($this->kode) . "
                </p>

                <p><strong>Nama:</strong> " .
                    htmlspecialchars($this->nama) . "
                </p>

                <p><strong>Jumlah:</strong> " .
                    htmlspecialchars((string)$this->jumlah) . "
                </p>
            </div>
        ";

    }

}
?>
