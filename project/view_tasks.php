<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['list_id'])) {
    header("Location: dashboard.php");
    exit();
}

$list_id = $_GET['list_id'];

$stmt = $pdo->prepare("SELECT * FROM todo_lists WHERE id = ? AND user_id = ?");
$stmt->execute([$list_id, $user_id]);
$list = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$list) {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ?");
$stmt->execute([$list_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = trim($_POST['description']);

    if (!empty($description)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (list_id, description) VALUES (?, ?)");
        $stmt->execute([$list_id, $description]);
        header("Location: view_tasks.php?list_id=$list_id");
        exit();
    } else {
        $error_message = "Task description cannot be empty.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_tasks'])) {
    $completed_tasks = $_POST['complete_tasks'];

    foreach ($completed_tasks as $task_id) {
        $stmt = $pdo->prepare("UPDATE tasks SET is_completed = 1 WHERE id = ? AND list_id = ?");
        $stmt->execute([$task_id, $list_id]);
    }

    $all_task_ids = array_column($tasks, 'id');
    $unchecked_tasks = array_diff($all_task_ids, $completed_tasks);

    foreach ($unchecked_tasks as $task_id) {
        $stmt = $pdo->prepare("UPDATE tasks SET is_completed = 0 WHERE id = ? AND list_id = ?");
        $stmt->execute([$task_id, $list_id]);
    }

    header("Location: view_tasks.php?list_id=$list_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks for <?php echo htmlspecialchars($list['title']); ?></title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
:root {
    --primary-color: #2196F3;
    --secondary-color: #1976D2;
    --success-color: #4CAF50;
    --success-hover: #388E3C;
    --text-color: #333;
    --background-light: #f8f9fa;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    --transition-speed: 0.3s;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #8D60BE  0%, #F2D49E 100%);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    
    margin: 0;
    padding: 30px;
    line-height: 1.6;
    color: var(--text-color);
    min-height: 100vh;
}

@keyframes gradientBG {
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

h1, h2 {
    color: var(--text-color);
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 10px;
}

h1::after, h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 4px;
    background: var(--primary-color);
    border-radius: 2px;
}

a {
    display: inline-block;
    margin: 12px 0;
    padding: 12px 24px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: all var(--transition-speed) ease;
    box-shadow: var(--shadow-sm);
}

a:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

form {
    background: rgba(255, 255, 255, 0.9);
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    margin-bottom: 30px;
    transition: transform var(--transition-speed) ease;
    backdrop-filter: blur(10px);
}

form:hover {
    transform: translateY(-5px);
}

input[type="text"] {
    width: calc(100% - 24px);
    padding: 12px;
    margin-bottom: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    transition: all var(--transition-speed) ease;
    font-size: 16px;
}

input[type="text"]:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

input[type="submit"] {
    padding: 12px 24px;
    background: var(--success-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all var(--transition-speed) ease;
    font-size: 16px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

input[type="submit"]:hover {
    background: var(--success-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

ul {
    list-style-type: none;
    padding: 0;
    margin: 20px 0;
}

li {
    background: rgba(255, 255, 255, 0.9);
    padding: 15px 20px;
    margin: 10px 0;
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-speed) ease;
    border-left: 4px solid var(--primary-color);
    backdrop-filter: blur(10px);
}

li:hover {
    transform: translateX(5px);
    box-shadow: var(--shadow-md);
}

@media (max-width: 768px) {
    body {
        padding: 15px;
    }
    
    form {
        padding: 20px;
    }
    
    input[type="submit"] {
        width: 100%;
    }
}
    </style>
</head>
<body>
    <h1>Tasks for <?php echo htmlspecialchars($list['title']); ?></h1>
    <a href="dashboard.php">Back to Dashboard</a>

    <h2>Add New Task</h2>
    <form action="" method="POST">
        <input type="text" name="description" placeholder="Task description..." required>
        <input type="submit" value="Add Task">
    </form>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <h2>Existing Tasks</h2>
    <form action="" method="POST">
        <ul>
            <?php if (count($tasks) > 0): ?>
                <?php foreach ($tasks as $task): ?>
                    <li>
                        <input type="checkbox" name="complete_tasks[]" value="<?php echo $task['id']; ?>" <?php echo $task['is_completed'] ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($task['description']); ?>
                        <?php if ($task['is_completed']): ?>
                            (Completed)
                        <?php else: ?>
                            (Pending)
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tasks found.</p>
            <?php endif; ?>
        </ul>

        <input type="submit" value="Mark as Completed">
    </form>

</body>
</html>