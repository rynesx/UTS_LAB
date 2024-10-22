<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Jika user belum login, redirect ke halaman login
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Mendapatkan informasi user dari database
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);

    // Jika password di-update
    if (!empty($_POST['password'])) {
        $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "UPDATE users SET username = '$new_username', email = '$new_email', password = '$new_password' WHERE id = '$user_id'";
    } else {
        // Update tanpa mengubah password
        $sql = "UPDATE users SET username = '$new_username', email = '$new_email' WHERE id = '$user_id'";
    }

    if (mysqli_query($conn, $sql)) {
        echo "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h2>Edit Profile</h2>
    <form method="POST" action="edit_profile.php">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required><br><br>
        
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required><br><br>

        <label for="password">New Password (Leave blank if not changing):</label><br>
        <input type="password" id="password" name="password"><br><br>

        <button type="submit">Update Profile</button>
    </form>
    <br>
    <a href="profile.php">Back to Profile</a>
</body>
</html>
