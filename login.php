<?php
session_start();

$conn = new mysqli("host", "root", "rootpassword", "library");
if ($conn->connect_error) {
    die("Can't connect: " . $conn->connect_error);
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

   
        if (md5($password) === $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['username'] === 'admin') {
                header("Location: librarian.php");
            } else {
                header("Location: user.php");
            }
            exit;
        } else {
            $error = "Wrong password!";
        }
    } else {
        $error = "User not found!";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Logisn</h2>

    <?php
    if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>

    <form method="post" action="">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="password" id="password" name="password" required>
        <input type="checkbox" onclick="showPassword()"> Show Password<br><br>
        <input type="submit" name="login" value="Login">
    </form>

    <script>
    function showPassword() {
        var x = document.getElementById("password");
        x.type = x.type === "password" ? "text" : "password";
    }
    </script>
</body>
</html>