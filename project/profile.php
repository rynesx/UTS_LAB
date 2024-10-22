<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Jika user belum login, redirect ke halaman login
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h2>Your Profile</h2>
    <p>Username: <?php echo $user['username']; ?></p>
    <p>Email: <?php echo $user['email']; ?></p>
    <a href="edit_profile.php">Edit Profile</a>
    <br><br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
