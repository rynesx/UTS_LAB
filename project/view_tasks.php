<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if list_id is provided
if (!isset($_GET['list_id'])) {
    header("Location: dashboard.php");
    exit();
}

$list_id = $_GET['list_id'];
$user_id = $_SESSION['user_id'];

// Fetch list details and verify ownership
$sql = "SELECT * FROM todo_lists WHERE id = :list_id AND user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['list_id' => $list_id, 'user_id' => $user_id]);
$list = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$list) {
    header("Location: dashboard.php");
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch tasks with filters
$sql_tasks = "SELECT * FROM tasks WHERE list_id = :list_id";

if ($status_filter === 'completed') {
    $sql_tasks .= " AND is_completed = 1";
} elseif ($status_filter === 'incomplete') {
    $sql_tasks .= " AND is_completed = 0";
}

if (!empty($search_query)) {
    $sql_tasks .= " AND description LIKE :search";
}

$sql_tasks .= " ORDER BY id DESC";

$stmt_tasks = $pdo->prepare($sql_tasks);
$params = ['list_id' => $list_id];

if (!empty($search_query)) {
    $params['search'] = "%$search_query%";
}

$stmt_tasks->execute($params);
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($list['title']); ?> - Tasks</title>
    <style>
        /* Menggunakan style yang sama dengan dashboard */
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #1e69de;
            padding: 15px;
            color: white;
            text-align: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .filters {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-bar {
            flex: 1;
            min-width: 200px;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
        }

        .filter-button {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            background-color: #f0f0f0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .filter-button.active {
            background-color: #1e69de;
            color: white;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin: 10px 0;
            background-color: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .task-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .task-item.completed {
            background-color: #e8f5e9;
        }

        .task-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .task-description {
            flex: 1;
            font-size: 1em;
        }

        .task-description.completed {
            text-decoration: line-through;
            color: #666;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .add-form {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .add-input {
            flex: 1;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2><?php echo htmlspecialchars($list['title']); ?>