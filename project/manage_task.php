<?php
// File: manage_task.php
// Fungsi: Menangani penambahan dan pengubahan task
// Lokasi: Simpan file ini dalam folder yang sama dengan view_list.php

session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = ['success' => false, 'message' => ''];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    switch($action) {
        case 'add':
            if (isset($_POST['list_id']) && isset($_POST['description'])) {
                $list_id = $_POST['list_id'];
                $description = trim($_POST['description']);

                // Verifikasi kepemilikan list
                $sql = "SELECT * FROM todo_lists WHERE id = :list_id AND user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['list_id' => $list_id, 'user_id' => $user_id]);
                $list = $stmt->fetch();

                if ($list && !empty($description)) {
                    // Tambah task baru
                    $insert_sql = "INSERT INTO tasks (list_id, description, is_completed) 
                                 VALUES (:list_id, :description, 0)";
                    $insert_stmt = $pdo->prepare($insert_sql);
                    $result = $insert_stmt->execute([
                        'list_id' => $list_id,
                        'description' => $description
                    ]);

                    if ($result) {
                        $response = ['success' => true, 'message' => 'Task berhasil ditambahkan'];
                    } else {
                        $response = ['success' => false, 'message' => 'Gagal menambahkan task'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'List tidak ditemukan'];
                }
            }
            break;

        case 'edit':
            if (isset($_POST['task_id']) && isset($_POST['description'])) {
                $task_id = $_POST['task_id'];
                $description = trim($_POST['description']);

                // Verifikasi kepemilikan task
                $sql = "SELECT t.* FROM tasks t 
                        JOIN todo_lists l ON t.list_id = l.id 
                        WHERE t.id = :task_id AND l.user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['task_id' => $task_id, 'user_id' => $user_id]);
                $task = $stmt->fetch();

                if ($task && !empty($description)) {
                    // Update task
                    $update_sql = "UPDATE tasks SET description = :description 
                                 WHERE id = :task_id";
                    $update_stmt = $pdo->prepare($update_sql);
                    $result = $update_stmt->execute([
                        'description' => $description,
                        'task_id' => $task_id
                    ]);

                    if ($result) {
                        $response = ['success' => true, 'message' => 'Task berhasil diubah'];
                    } else {
                        $response = ['success' => false, 'message' => 'Gagal mengubah task'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Task tidak ditemukan'];
                }
            }
            break;

        default:
            $response = ['success' => false, 'message' => 'Action tidak valid'];
            break;
    }

    // Kirim response dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Redirect jika diakses langsung tanpa POST
header("Location: view_list.php");
exit();
?>