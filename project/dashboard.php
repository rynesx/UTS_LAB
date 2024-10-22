<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch todo lists with search and filter functionality
$sql_lists = "SELECT todo_lists.*, 
    COUNT(tasks.id) as total_tasks,
    SUM(tasks.is_completed) as completed_tasks
    FROM todo_lists 
    LEFT JOIN tasks ON todo_lists.id = tasks.list_id
    WHERE todo_lists.user_id = :user_id";

// Use GROUP BY before HAVING
$sql_lists .= " GROUP BY todo_lists.id ";

if ($status_filter === 'completed') {
    $sql_lists .= " HAVING total_tasks > 0 AND total_tasks = completed_tasks";
} elseif ($status_filter === 'pending') {
    $sql_lists .= " HAVING total_tasks > 0 AND completed_tasks < total_tasks";
}

if (!empty($search_query)) {
    $sql_lists .= " AND (todo_lists.title LIKE :search OR tasks.description LIKE :search)";
}

$sql_lists .= " ORDER BY todo_lists.id DESC";

$stmt_lists = $pdo->prepare($sql_lists);
$params = ['user_id' => $user_id];
if (!empty($search_query)) {
    $params['search'] = "%$search_query%";
}
$stmt_lists->execute($params);
$list_items = $stmt_lists->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
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
            margin-bottom: 20px;
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

        .todo-list {
            list-style: none;
            padding: 0;
        }

        .todo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background-color: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .todo-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .todo-info {
            flex: 1;
        }

        .todo-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .todo-stats {
            font-size: 0.9em;
            color: #666;
        }

        .todo-actions {
            display: flex;
            gap: 10px;
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

        .btn-view {
            background-color: #1e69de;
            color: white;
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

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-top: 8px;
        }

        .progress-fill {
            height: 100%;
            background-color: #1e69de;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>User Dashboard</h2>
        <a href="dashboard.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <div class="card">
            <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            
            <div class="filters">
                <form action="" method="GET" style="width: 100%; display: flex; gap: 10px;">
                    <input type="text" 
                           name="search" 
                           class="search-bar" 
                           placeholder="Search lists and tasks..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-view">Search</button>
                </form>
                <div>
                    <a href="?status=all" class="filter-button <?php echo $status_filter === 'all' ? 'active' : ''; ?>">All Tasks</a>
                    <a href="?status=completed" class="filter-button <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">Completed Tasks</a>
                    <a href="?status=pending" class="filter-button <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending Tasks</a>
                </div>
            </div>

            <div class="todo-list">
                <?php if (count($list_items) > 0): ?>
                    <?php foreach ($list_items as $list): ?>
                        <div class="todo-item">
                            <div class="todo-info">
                                <div class="todo-title">
                                    <?php echo htmlspecialchars($list['title']); ?>
                                </div>
                                <div class="todo-stats">
                                    <?php 
                                    $total_tasks = $list['total_tasks'] ?: 0;
                                    $completed_tasks = $list['completed_tasks'] ?: 0;
                                    $progress = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0;
                                    ?>
                                    <?php echo $completed_tasks; ?>/<?php echo $total_tasks; ?> tasks completed
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                            </div>
                            <div class="todo-actions">
                                <a href="view_tasks.php?list_id=<?php echo $list['id']; ?>" 
                                   class="btn btn-view">View Tasks</a>
                                <a href="delete_list.php?id=<?php echo $list['id']; ?>" 
                                   class="btn btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this list and all its tasks?');">
                                    Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No to-do lists found!</p>
                <?php endif; ?>
            </div>

            <form action="add_list.php" method="POST" class="add-form">
                <input type="text" 
                       name="title" 
                       class="add-input"
                       placeholder="New To-Do List" 
                       required>
                <button type="submit" class="btn btn-view">Add List</button>
            </form>
        </div>
    </div>
</body>
</html>