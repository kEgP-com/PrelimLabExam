<?php
$servername = "db";  // service name from docker-compose
$username   = "root";
$password   = "rootpassword";
$dbname     = "library_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
