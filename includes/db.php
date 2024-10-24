<?php
// Menggunakan variabel lingkungan untuk kredensial database
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'todo_list';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    // Membuat koneksi dengan charset yang tepat untuk keamanan
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mengatur PDO untuk mendukung karakter dan mencegah masalah dengan charset
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8mb4');
    
} catch (PDOException $e) {
    // Menghindari menampilkan rincian kesalahan ke pengguna
    error_log("Connection error: " . $e->getMessage());
    die("Could not connect to the database. Please try again later.");
}
?>
