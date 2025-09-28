<?php
session_start();

$conn = new mysqli("db", "root", "rootpassword", "library_db");
if ($conn->connect_error) {
    die("Can't connect: " . $conn->connect_error);
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
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
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f2f5;
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
    }

    .login-container {
        background: #fff;
        padding: 30px 40px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        width: 350px;
        text-align: center;
    }

    h2 {
        margin-bottom: 20px;
        color: #333;
    }

    input[type="text"],
    input[type="password"] {
        width: 90%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #bbb;
        border-radius: 5px;
        outline: none;
        transition: 0.3s;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: #3498db;
        box-shadow: 0 0 5px #3498db;
    }

    input[type="submit"] {
        background: #3498db;
        color: white;
        border: none;
        padding: 12px;
        width: 100%;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: 0.3s;
    }

    input[type="submit"]:hover {
        background: #3498db;
    }

    .error {
        color: red;
        margin-bottom: 15px;
    }

    .show-pass {
        font-size: 14px;
        color: #555;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>

        <form method="post" action="">
            <input type="text" name="username" placeholder="Enter username" required><br>
            <input type="password" id="password" name="password" placeholder="Enter password" required><br>

            <label class="show-pass">
                <input type="checkbox" onclick="showPassword()"> Show Password
            </label><br><br>

            <input type="submit" name="login" value="Login">
        </form>
    </div>

    <script>
    function showPassword() {
        var x = document.getElementById("password");
        x.type = x.type === "password" ? "text" : "password";
    }
    </script>
</body>

</html>