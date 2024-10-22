<?php
include('../includes/db.php');
include('../includes/functions.php');
checkLogin();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $password = cleanInput($_POST['password']);

    // Password update logic
    $update_query = "UPDATE users SET username = :username, email = :email" . (!empty($password) ? ", password = :password" : "") . " WHERE id = :id";
    $stmt = $pdo->prepare($update_query);
    
    // Only bind password if it's provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password, 'id' => $user_id]);
    } else {
        $stmt->execute(['username' => $username, 'email' => $email, 'id' => $user_id]);
    }

    // Redirect to profile.php after updating
    header("Location: profile.php");
    exit(); // Ensure no further code is executed
}

// Fetch user data
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
    <title>Edit Profile</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to an external CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007BFF;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>
    <form action="edit_profile.php" method="POST">
        <input type="text" name="username" value="<?php echo cleanInput($user['username']); ?>" required>
        <input type="email" name="email" value="<?php echo cleanInput($user['email']); ?>" required>
        <input type="password" name="password" placeholder="New Password (leave blank to keep current)" />
        <button type="submit">Update Profile</button>
    </form>
    <a href="profile.php">Back to Profile</a>
</div>

</body>
</html>