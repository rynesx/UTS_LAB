<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Jika user belum login, redirect ke halaman login
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $user_id = $_SESSION['user_id'];

    // Menyimpan to-do list baru ke database
    $sql = "INSERT INTO todo_lists (title, user_id) VALUES ('$title', '$user_id')";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New To-Do List</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h2>Create New To-Do List</h2>
    <form method="POST" action="create_list.php">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>
        <button type="submit">Create</button>
    </form>
</body>
</html>
