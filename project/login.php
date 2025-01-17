<?php
include('../includes/db.php');
include('../includes/functions.php');

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];

    if (strpos($email, '@gmail.com') === false) {
        $error_message = "Email must use the @gmail.com domain.";
    } else {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(
                -45deg,
                #6DB3F2,
                #1E69DE,
                #003CBE,
                #0066FF,
                #6DB3F2
            );
            background-size: 300% 300%;
            animation: gradientAnimation 8s ease-in-out infinite alternate;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 0%;
            }
            25% {
                background-position: 100% 0%;
            }
            50% {
                background-position: 100% 100%;
            }
            75% {
                background-position: 0% 100%;
            }
            100% {
                background-position: 0% 0%;
            }
        }

        .container {
            display: flex;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            width: 700px;
            height: auto;
        }

        .login-container {
            flex: 1;
            padding: 40px;
            text-align: center;
        }

        .login-container h2 {
            color: white;
            margin-bottom: 20px;
        }

        .login-container input[type="email"], 
        .login-container input[type="password"] {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border-radius: 25px;
            border: none;
            outline: none;
            box-sizing: border-box;
            font-size: 16px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .login-container input[type="email"]::placeholder,
        .login-container input[type="password"]::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-container input[type="submit"] {
            width: 100%;
            padding: 15px;
            margin-top: 10px;
            background-color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: #1E69DE;
        }

        .login-container input[type="submit"]:hover {
            background-color: #f1f1f1;
        }

        .signup-container {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            color: white;
        }

        .signup-container h3 {
            margin-bottom: 20px;
        }

        .signup-container p {
            font-size: 16px;
            margin-bottom: 30px;
        }

        .signup-container .signup-btn {
            width: 100%;
            padding: 15px;
            background-color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: #1E69DE;
        }

        .signup-container .signup-btn:hover {
            background-color: #f1f1f1;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2>Login</h2>
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="name@gmail.com" required>
                <input type="password" name="password" placeholder="********" required>
                <input type="submit" value="Log In">
            </form>
        </div>

        <div class="signup-container">
            <h3>Belum bergabung?</h3>
            <p>Gabung sekarang untuk mendapatkan akses penuh.</p>
            <a href="register.php"><button class="signup-btn">Sign Up</button></a>
        </div>
    </div>

</body>
</html>