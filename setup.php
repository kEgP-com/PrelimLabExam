<?php
$conn = new mysqli("db", "root", "rootpassword");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS library_db";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}


$conn->select_db("library_db");

// default login table the credentials such as the user and the librarian
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('user','librarian')
)";
if (!$conn->query($sql)) {
    die("Error creating users table: " . $conn->error);
}

$conn->query("INSERT IGNORE INTO users (id, username, password, role) VALUES
    (1, 'admin', MD5('1234'), 'librarian'),
    (2, 'user1', MD5('1234'), 'user')");
?>