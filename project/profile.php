<?php
include('../includes/db.php');
include('../includes/functions.php');
checkLogin();

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to an external CSS file -->
    <style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #F08686, #9CC495);
    margin: 0;
    padding: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-size: 400% 400%;
    animation: gradientAnimation 15s ease infinite;
}

@keyframes gradientAnimation {
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

.container {
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    padding: 90px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 60px;
}

p {
    font-size: 20px;
    color: #555;
}

a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 15px;
    background-color: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
}

a:hover {
    background-color: #0056b3;
}

    </style>
</head>
<body>

<div class="container">
    <h2>Profile</h2>
    <p><strong>Username:</strong> <?php echo cleanInput($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo cleanInput($user['email']); ?></p>
    <a href="dashboard.php">Back to Dashboard</a>
    <a href="edit_profile.php">Edit Profile</a>
</div>

</body>
</html>