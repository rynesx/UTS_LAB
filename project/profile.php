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

<h2>Profile</h2>
<p>Username: <?php echo cleanInput($user['username']); ?></p>
<p>Email: <?php echo cleanInput($user['email']); ?></p>
<a href="edit_profile.php">Edit Profile</a>
