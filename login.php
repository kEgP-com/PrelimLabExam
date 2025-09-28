<?php
session_start();
include 'config.php';

// Handle login form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Get user from DB
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'librarian') {
                header("Location: catalog.php");
                exit;
            } else {
                header("Location: catalog.php");
                exit;
            }
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "⚠️ User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 50px; }
        .form-container { background: #fff; padding: 20px; border-radius: 8px; width: 320px; margin: auto; text-align: center; }
        input { width: 100%; padding: 8px; margin: 8px 0; }
        button { width: 100%; padding: 10px; background: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .register-btn { background: #28a745; margin-top: 10px; }
        .register-btn:hover { background: #218838; }
        .error { color: red; margin-bottom: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <!-- Register Button -->
        <form action="register.php" method="get">
            <button type="submit" class="register-btn">📝 Register</button>
        </form>
    </div>
</body>
</html>
