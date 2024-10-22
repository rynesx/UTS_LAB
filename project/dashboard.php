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
    SUM(CASE WHEN tasks.is_completed THEN 1 ELSE 0 END) as completed_tasks
    FROM todo_lists 
    LEFT JOIN tasks ON todo_lists.id = tasks.list_id
    WHERE todo_lists.user_id = :user_id";

// Add search condition in WHERE clause
if (!empty($search_query)) {
    $sql_lists .= " AND (todo_lists.title LIKE :search OR tasks.description LIKE :search)";
}

// Group by after all WHERE conditions
$sql_lists .= " GROUP BY todo_lists.id, todo_lists.title, todo_lists.user_id";

// Add HAVING conditions for status filtering
if ($status_filter === 'completed') {
    $sql_lists .= " HAVING total_tasks > 0 AND total_tasks = completed_tasks";
} elseif ($status_filter === 'pending') {
    $sql_lists .= " HAVING total_tasks > 0 AND completed_tasks < total_tasks";
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
    /* Animated gradient styles */
body {
    background: linear-gradient(135deg, #6D53DC 0%, #DC5356 100%);
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    margin: 0;
    padding: 0;
    color: #2d3748;
    line-height: 1.6;
    min-height: 100vh;
    
    /* Tambahkan properti untuk animasi gradient */
    background-size: 400% 400%;
    animation: gradientBody 15s ease infinite;
}

.navbar {
    background: linear-gradient(135deg, #181D72 0%, #B491C5 100%);
    padding: 1rem;
    color: white;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    
    /* Tambahkan properti untuk animasi gradient */
    background-size: 400% 400%;
    animation: gradientNav 12s ease infinite;
}

/* Animasi untuk body */
@keyframes gradientBody {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Animasi untuk navbar */
@keyframes gradientNav {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.navbar h2 {
    margin: 0 0 1rem 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.navbar a {
    color: white;
    text-decoration: none;
    margin: 0 15px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.navbar a:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

.card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card:hover {
    transform: translateY(-2px);
}

.card h1 {
    color: #2d3748;
    font-size: 2rem;
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.filters {
    margin: 1.5rem 0;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.search-bar {
    flex: 1;
    min-width: 250px;
    padding: 0.8rem 1.2rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-bar:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
}

.filter-button {
    padding: 0.8rem 1.2rem;
    border: none;
    border-radius: 12px;
    background-color: #edf2f7;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    text-decoration: none;
}

.filter-button:hover {
    background-color: #e2e8f0;
    transform: translateY(-1px);
}

.filter-button.active {
    background-color: #4299e1;
    color: white;
}

.todo-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    margin: 1rem 0;
    background-color: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.todo-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border-color: #4299e1;
}

.todo-info {
    flex: 1;
    margin-right: 1rem;
}

.todo-title {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: #2d3748;
}

.todo-stats {
    font-size: 0.9rem;
    color: #718096;
}

.todo-actions {
    display: flex;
    gap: 0.8rem;
}

.btn {
    padding: 0.8rem 1.2rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-view {
    background-color: #4299e1;
    color: white;
}

.btn-view:hover {
    background-color: #3182ce;
}

.btn-delete {
    background-color: #fc8181;
    color: white;
}

.btn-delete:hover {
    background-color: #f56565;
}

.add-form {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e2e8f0;
}

.add-input {
    flex: 1;
    padding: 0.8rem 1.2rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.add-input:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: #edf2f7;
    border-radius: 4px;
    margin-top: 0.8rem;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4299e1 0%, #63b3ed 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

@media (max-width: 768px) {
    .container {
        padding: 0 1rem;
    }
    
    .todo-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .todo-actions {
        margin-top: 1rem;
        width: 100%;
    }
    
    .btn {
        flex: 1;
        text-align: center;
    }
    
    .filters {
        flex-direction: column;
    }
    
    .filter-button {
        width: 100%;
        text-align: center;
    }
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