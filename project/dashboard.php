<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Jika user belum login, redirect ke halaman login
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

// Mendapatkan to-do list dari database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM todo_lists WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
    <h3>Your To-Do Lists</h3>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <ul>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <li><?php echo $row['title']; ?> - <a href="todo_list.php?id=<?php echo $row['id']; ?>">View</a> | <a href="delete_list.php?id=<?php echo $row['id']; ?>">Delete</a></li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>You have no to-do lists. <a href="create_list.php">Create one now</a>.</p>
    <?php endif; ?>

    <a href="profile.php">View Profile</a> | <a href="logout.php">Logout</a>
</body>
</html>
