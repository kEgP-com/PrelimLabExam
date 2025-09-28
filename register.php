<?php
session_start();
include 'config.php';

// Handle registration form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = trim($_POST['role']);

    // Prevent duplicate usernames
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "⚠️ Username already exists!";
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $role);

        if ($stmt->execute()) {
            $message = "✅ User registered successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register User</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 50px; }
        .form-container { background: #fff; padding: 20px; border-radius: 8px; width: 320px; margin: auto; text-align: center; }
        input, select { width: 100%; padding: 8px; margin: 8px 0; }
        button { width: 100%; padding: 10px; background: #28a745; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #218838; }
        .back-btn { background: #007bff; margin-top: 10px; }
        .back-btn:hover { background: #0056b3; }
        .message { margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Role:</label>
            <select name="role" required>
                <option value="librarian">Librarian</option>
                <option value="user">User</option>
            </select>

            <button type="submit">Register</button>
        </form>

        <!-- Back to Login Button -->
        <form action="login.php" method="get">
            <button type="submit" class="back-btn">⬅️ Back to Login</button>
        </form>
    </div>
</body>
</html>
