<?php
// Fungsi untuk memeriksa apakah user sudah login
function checkLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk mencegah XSS
function cleanInput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
