<?php
session_start();
include "conn.php";

function loginUser($login, $password) {
    global $conn;
    $sql = "SELECT id, username, email, password FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    return false;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];
    if (loginUser($login, $password)) {
        header("Location: transaksi.php"); // Redirect to dashboard or home page
        exit();
    } else {
        $error = "Invalid username/email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="./style/login.css">
</head>
<body>
    <div class="container">
        <form method="POST" class="login-form">
            <h2>Log in</h2>
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="login">Username or Email</label>
                <input type="text" id="login" name="login" placeholder="Enter your username or email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-login">Log in</button>
            <div class="or-separator">OR</div>
            <button type="button" class="btn-github">
                <img src="github-icon.png" alt="GitHub Icon"> Continue with GitHub
            </button>
            <div class="signup-link">
                Don't have an account? <a href="register.php">Sign up</a>
            </div>
        </form>
    </div>
</body>
</html>