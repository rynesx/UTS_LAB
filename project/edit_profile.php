<?php
include('../includes/db.php');
include('../includes/functions.php');
checkLogin();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    
    $sql = "UPDATE users SET username = :username, email = :email WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username, 'email' => $email, 'id' => $user_id]);
    
    echo "Profile updated!";
}

// Fetch user data
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();
?>

<form action="edit_profile.php" method="POST">
    <input type="text" name="username" value="<?php echo cleanInput($user['username']); ?>" required>
    <input type="email" name="email" value="<?php echo cleanInput($user['email']); ?>" required>
    <button type="submit">Update Profile</button>
</form>
