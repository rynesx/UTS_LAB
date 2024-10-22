<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['title'])) {
    $user_id = $_SESSION['user_id'];
    $title = htmlspecialchars($_POST['title']);

    $sql = "INSERT INTO todo_lists (user_id, title) VALUES (:user_id, :title)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'title' => $title]);

    header("Location: dashboard.php");
    exit();
}
