<?php
    
    $mysql = new mysqli("db", "root", "rootpassword", "library_db");

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["name"];
        $password = $_POST["pass"];

        if($username == "student" && $password == "studpassword"){
            header("Location:user.php");
        }elseif($username == "admins" && $password == "adminpass"){
            header("Location:librarian.php");
        }
        
    }

    $mysql->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ONLINE LIBRARY</title>
    </head>
    <body>
        <form action="login.php" method="post">
            <input type="text" name="name" require placeholder="Enter Username"> <br>
            <input type="password" name="pass" require placeholder="Enter Password"> <br>
            <input type="submit" value="LOG IN">
        </form>
    </body> 
</html>