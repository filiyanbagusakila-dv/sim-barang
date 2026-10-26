
<?php
session_start();

require_once __DIR__ . '/koneksi.php';

// Jika sudah login, arahkan ke dashboard.
if (isset($_SESSION['id_users'], $_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$usernameInput = '';

// Token keamanan form login.
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = trim((string) ($_POST['username'] ?? ''));
    $passwordInput = (string) ($_POST['password'] ?? '');
    $tokenInput = (string) ($_POST['csrf_token'] ?? '');

    if (
        empty($_SESSION['csrf_login']) ||
        !hash_equals($_SESSION['csrf_login'], $tokenInput)
    ) {
        $error = 'Form sudah kedaluwarsa. Silakan muat ulang halaman.';
    } elseif ($usernameInput === '' || $passwordInput === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id_users, nama, username, password, role
                 FROM users
                 WHERE username = :username
                 LIMIT 1'
            );

            $stmt->execute([
                ':username' => $usernameInput
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $user &&
                password_verify($passwordInput, $user['password']) &&
                in_array($user['role'], ['Admin', 'User'], true)
            ) {
                session_regenerate_id(true);

                $_SESSION['id_users'] = (int) $user['id_users'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Buat token baru setelah login berhasil.
                unset($_SESSION['csrf_login']);

                header('Location: index.php');
                exit;
            }

            $error = 'Username atau password salah.';
        } catch (PDOException $e) {
            error_log('Login gagal: ' . $e->getMessage());
            $error = 'Terjadi kesalahan saat mengakses database.';
        }
    }
}

function login_h($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | SIM-Barang</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="styles.css">

    <script src="main.js" defer></script>
</head>

<body class="login-page">

    <main class="login-layout">

        <!-- BAGIAN INFORMASI -->
        <section class="login-intro">
            <div class="login-logo">
                SB
            </div>

            <span class="login-eyebrow">
                SISTEM INFORMASI INVENTARIS
            </span>

            <h1>
                SIM-<span>Barang</span>
            </h1>

            <p class="login-description">
                Kelola inventaris dengan lebih mudah,
                rapi, dan terorganisasi dalam satu sistem.
            </p>

            <div class="login-feature-list">

                <div class="login-feature">
                    <span class="feature-check">&#10003;</span>
                    <span>Pencatatan data inventaris</span>
                </div>

                <div class="login-feature">
                    <span class="feature-check">&#10003;</span>
                    <span>Pemantauan jumlah stok barang</span>
                </div>

                <div class="login-feature">
                    <span class="feature-check">&#10003;</span>
                    <span>Pencatatan barang masuk dan keluar</span>
                </div>

            </div>

            <div class="login-intro-footer">
                Sistem Informasi Manajemen Barang
            </div>
        </section>

        <!-- BAGIAN FORM LOGIN -->
        <section class="login-form-section">

            <div class="login-card">

                <div class="login-card-heading">
                    <div class="login-card-icon">
                        <span>&#128274;</span>
                    </div>

                    <h2>Selamat Datang!</h2>

                    <p>
                        Silakan masuk menggunakan akun kamu.
                    </p>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger login-alert" role="alert">
                        <?= login_h($error) ?>
                    </div>
                <?php endif; ?>

                <form
                    id="login-form"
                    method="POST"
                    action="login.php"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= login_h($_SESSION['csrf_login']) ?>"
                    >

                    <div class="login-field">
                        <label for="username" class="form-label">
                            Username
                        </label>

                        <div class="login-input-wrapper">
                            <span class="login-input-icon">@</span>

                            <input
                                type="text"
                                class="form-control login-input"
                                id="username"
                                name="username"
                                placeholder="Masukkan username"
                                autocomplete="username"
                                value="<?= login_h($usernameInput) ?>"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="password" class="form-label">
                            Password
                        </label>

                        <div class="login-input-wrapper">
                            <span class="login-input-icon">&#9679;</span>

                            <input
                                type="password"
                                class="form-control login-input"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="togglePassword"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                            >
                                Lihat
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary login-submit"
                    >
                        Masuk ke Sistem
                        <span aria-hidden="true">&rarr;</span>
                    </button>

                </form>

                <div class="login-divider">
                    <span></span>
                    <small>SIM-BARANG</small>
                    <span></span>
                </div>

                <p class="login-security">
                    Akses sistem sesuai akun dan hak pengguna kamu.
                </p>

            </div>

            <footer class="login-page-footer">
                &copy; <?= date('Y') ?> SIM-Barang. All rights reserved.
            </footer>

        </section>

    </main>

    <script>
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
    </script>

</body>
</html>
