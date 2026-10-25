
<?php

session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION["login"]) && $_SESSION["login"] === true) {
    header("Location: index.php");
    exit;
}

// Data pengguna dalam array asosiatif
$pengguna = [
    "admin" => [
        "password" => "password123",
        "nama" => "Administrator",
        "role" => "Admin"
    ]
];

$pesanError = "";

// Proses login
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if (
        isset($pengguna[$username]) &&
        hash_equals($pengguna[$username]["password"], $password)
    ) {

        // Membuat session baru
        session_regenerate_id(true);

        $_SESSION["login"] = true;
        $_SESSION["username"] = $username;
        $_SESSION["nama"] = $pengguna[$username]["nama"];
        $_SESSION["role"] = $pengguna[$username]["role"];

        header("Location: index.php");
        exit;

    } else {

        $pesanError = "Username atau password salah!";

    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIM-Barang</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

    <div class="login-container">

        <div class="login-header">
            <div class="login-icon">📦</div>
            <h1>SIM-Barang</h1>
            <p>Sistem Informasi Manajemen Barang</p>
        </div>

        <div class="login-card">

            <h2>Selamat Datang</h2>
            <p class="login-subtitle">
                Silakan login untuk mengakses sistem.
            </p>

            <?php if ($pesanError !== ""): ?>
                <div class="pesan-error">
                    <?= htmlspecialchars($pesanError) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">

                <div class="form-group">
                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-login">
                    Login
                </button>

            </form>

            <div class="login-demo">
                <strong>Akun pengujian:</strong>
                <p>Username: admin</p>
                <p>Password: password123</p>
            </div>

        </div>

        <p class="login-footer">
            &copy; 2026 SIM-Barang
        </p>

    </div>

</body>
</html>
