<?php
$servername = "localhost";
$username = "root";  // Ganti sesuai dengan user MySQL Anda
$password = "";      // Ganti sesuai dengan password MySQL Anda
$dbname = "todo_list_db";

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
