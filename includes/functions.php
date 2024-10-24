<?php
// Fungsi untuk memeriksa apakah user sudah login
function checkLogin() {
    // Pastikan session belum dimulai
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk mencegah XSS
function cleanInput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk melakukan prepared statements untuk mencegah SQL Injection
function executeQuery($pdo, $sql, $params) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt; // Mengembalikan objek statement untuk penggunaan selanjutnya jika dibutuhkan
    } catch (PDOException $e) {
        // Menangani kesalahan dengan logging atau menampilkan pesan suci
        error_log("Database query error: " . $e->getMessage()); // Log error untuk keamanan
        return false; // Kembalikan false jika terjadi error
    }
}

// Fungsi untuk memvalidasi email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>
