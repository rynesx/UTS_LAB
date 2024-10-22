<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if list_id is provided in the URL
if (!isset($_GET['list_id'])) {
    header("Location: dashboard.php");
    exit();
}

$list_id = $_GET['list_id'];

// Fetch the list details
$stmt = $pdo->prepare("SELECT * FROM todo_lists WHERE id = ? AND user_id = ?");
$stmt->execute([$list_id, $user_id]);
$list = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$list) {
    header("Location: dashboard.php");
    exit();
}

// Fetch tasks for the list
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ?");
$stmt->execute([$list_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new task submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = trim($_POST['description']);

    // Validate the input
    if (!empty($description)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (list_id, description) VALUES (?, ?)");
        $stmt->execute([$list_id, $description]);
        // Redirect to the same page to avoid form resubmission
        header("Location: view_tasks.php?list_id=$list_id");
        exit();
    } else {
        $error_message = "Task description cannot be empty.";
    }
}

// Handle task completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_tasks'])) {
    $completed_tasks = $_POST['complete_tasks'];

    // Mark the checked tasks as completed
    foreach ($completed_tasks as $task_id) {
        $stmt = $pdo->prepare("UPDATE tasks SET is_completed = 1 WHERE id = ? AND list_id = ?");
        $stmt->execute([$task_id, $list_id]);
    }

    // Mark the unchecked tasks as pending
    $all_task_ids = array_column($tasks, 'id'); // Get all task IDs
    $unchecked_tasks = array_diff($all_task_ids, $completed_tasks); // Find unchecked task IDs

    foreach ($unchecked_tasks as $task_id) {
        $stmt = $pdo->prepare("UPDATE tasks SET is_completed = 0 WHERE id = ? AND list_id = ?");
        $stmt->execute([$task_id, $list_id]);
    }

    // Redirect to the same page to refresh the task list
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
    <link rel="stylesheet" href="../includes/styles.css"> <!-- Link to external CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        a {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        input[type="submit"] {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background: #fff;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
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