<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // Jika user sudah login, arahkan ke dashboard
    header("Location: views/dashboard.php");
    exit();
} else {
    // Jika user belum login, arahkan ke halaman login
    header("Location: views/login.php");
    exit();
}
?>
