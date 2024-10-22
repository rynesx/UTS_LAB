<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db_connect.php';

    $title = htmlspecialchars($_POST['title']);
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO todo_lists (user_id, title) VALUES ('$user_id', '$title')";

    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
