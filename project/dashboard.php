<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's to-do lists
$sql_lists = "SELECT * FROM todo_lists WHERE user_id = :user_id ORDER BY id DESC";
$stmt_lists = $pdo->prepare($sql_lists);
$stmt_lists->execute(['user_id' => $user_id]);
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

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 95%;
            max-width: 600px;
            text-align: center;
            margin: 10px;
        }

        h1 {
            color: #1e69de;
        }

        .todo-list {
            list-style-type: none;
            padding: 0;
            margin: 20px 0;
        }

        .todo-item {
            background-color: #f9f9f9;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }

        .logout-button {
            background-color: #ff4d4d;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .logout-button:hover {
            background-color: #ff1a1a;
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
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <h3>Your To-Do Lists</h3>
            <ul class="todo-list">
                <?php if (count($list_items) > 0): ?>
                    <?php foreach ($list_items as $list): ?>
                        <li class="todo-item">
                            <span><?php echo htmlspecialchars($list['title']); ?></span>
                            <span>
                                <a href="delete_list.php?id=<?php echo $list['id']; ?>" 
                                   style="color: #ff4d4d; text-decoration: none;" 
                                   onclick="return confirm('Are you sure you want to delete this to-do list?');">Delete</a>
                            </span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No to-do lists found!</li>
                <?php endif; ?>
            </ul>
            <form action="add_list.php" method="POST">
                <input type="text" name="title" placeholder="New To-Do List" required>
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="submit" value="Add To-Do List">
            </form>
        </div>
    </div>
</body>
</html>
