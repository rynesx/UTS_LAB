<?php
include('../includes/db.php');
include('../includes/functions.php');

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    
    if (strpos($email, '@gmail.com') === false) {
        $error_message = "Email harus menggunakan domain @gmail.com.";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $stmtCheck = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmtCheck->execute(['email' => $email]);
        if ($stmtCheck->rowCount() > 0) {
            $error_message = "Email sudah terdaftar.";
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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

        .register-container {
            flex: 1;
            padding: 40px;
            text-align: center;
        }

        .register-container h2 {
            color: white;
            margin-bottom: 20px;
        }

        .register-container input[type="text"], 
        .register-container input[type="email"], 
        .register-container input[type="password"] {
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

        .register-container input[type="text"]::placeholder,
        .register-container input[type="email"]::placeholder,
        .register-container input[type="password"]::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .register-container input[type="submit"] {
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

        .register-container input[type="submit"]:hover {
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
    <div class="register-container">
            <h2>Register Here!</h2>
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Register">
            </form>
            <p>Already have an account? <a href="login.php" style="color: white;">Login here!</a></p>
        </div>
    </div>
</body>
</html>
