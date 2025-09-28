<?php
session_start();


if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}


$conn = new mysqli("db", "root", "rootpassword", "library_db");
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ffffffff;
        }

      .header {
            background: #024baaff;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
      }
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 150vh;
            padding-top: 100px;
        }
        .login-box {
            background: #ffffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 320px;
            text-align: center;
        }
        .login-box h2 {
            margin-bottom: 20px;
        }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #024baaff;
        }
        .login-box input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            background: #024baaff;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .login-box input[type="submit"]:hover {
            background: #024baaff;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        📚 Library Management System
    </div>
    <div class="container">
        <div class="login-box">
            <h2>Login</h2>

            <?php
            if (isset($error)) {
                echo "<p class='error'>$error</p>";
            }
            ?>

            <form method="post" action="">
                <input type="text" name="username" placeholder="Enter Username" required><br>
                <input type="password" id="password" name="password" placeholder="Enter Password" required><br>
                <label><input type="checkbox" onclick="showPassword()"> Show Password</label><br><br>
                <input type="submit" name="login" value="Login">
            </form>
        </div>
    </div>

    <script>
    function showPassword() {
        var x = document.getElementById("password");
        x.type = x.type === "password" ? "text" : "password";
    }
    </script>
</body>
</html>
