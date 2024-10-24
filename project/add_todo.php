<?php
session_start();
include('../includes/db.php');

// Cek apakah pengguna sudah masuk
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Memanfaatkan parameterisasi dan sanitasi yang tepat saat menangani input
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Memastikan bahwa input tidak kosong dan melakukan sanitasi
    if (!empty($_POST['title'])) {
        $user_id = $_SESSION['user_id'];
        
        // Menggunakan htmlspecialchars untuk menghindari XSS pada input title
        $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');

        // Validasi panjang judul, misalnya maksimum 255 karakter
        if (strlen($title) > 255) {
            // Menangani kesalahan jika judul terlalu panjang
            echo "Judul tidak boleh lebih dari 255 karakter.";
            exit();
        }

        // Menyimpan data ke dalam database dengan prepared statement
        $sql = "INSERT INTO todo_lists (user_id, title) VALUES (:user_id, :title)";
        $stmt = $pdo->prepare($sql);
        
        // Menggunakan try-catch untuk menangani kesalahan eksekusi
        try {
            $stmt->execute(['user_id' => $user_id, 'title' => $title]);
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            // Menangani kesalahan jika terjadi
            echo "Error: " . $e->getMessage();
            exit();
        }
    } else {
        // Menangani kesalahan jika input kosong
        echo "Judul tidak boleh kosong.";
        exit();
    }
}
