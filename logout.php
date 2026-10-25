
<?php

// Memulai session
session_start();

// Menghapus seluruh data session
$_SESSION = [];

// Menghapus cookie session jika digunakan
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );

}

// Mengakhiri session
session_destroy();

// Kembali ke halaman login
header("Location: login.php");
exit;

?>
