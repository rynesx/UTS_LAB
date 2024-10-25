<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['title'])) {
        $user_id = $_SESSION['user_id'];
        
        $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');

        if (strlen($title) > 255) {
            echo "Judul tidak boleh lebih dari 255 karakter.";
            exit();
        }

        $sql = "INSERT INTO todo_lists (user_id, title) VALUES (:user_id, :title)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute(['user_id' => $user_id, 'title' => $title]);
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    } else {
        echo "Judul tidak boleh kosong.";
        exit();
    }
}
