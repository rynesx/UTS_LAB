<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $completed = $_POST['completed'];
    
    $sql = "UPDATE tasks SET completed = :completed WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        'completed' => $completed,
        'id' => $task_id
    ]);
    
    echo json_encode(['success' => $success]);
}