<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $todo_id = (int)$_GET['id'];
    $sql = "DELETE FROM todo_lists WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $todo_id, 'user_id' => $_SESSION['user_id']]);
}

header("Location: dashboard.php");
exit();
