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
    (2, 'user1', MD5('1234'), 'user')
)");


$sql = "CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    copies INT DEFAULT 1,
    available INT DEFAULT 1,
    date_added DATE
)";
if (!$conn->query($sql)) {
    die("Error creating books table: " . $conn->error);
}


$conn->query("INSERT IGNORE INTO books (id, title, author, copies, available, date_added) VALUES
    (1, 'The Great Gatsby', 'F. Scott Fitzgerald', 3, 3, '2025-01-10'),
    (2, '1984', 'George Orwell', 5, 5, '2025-02-15'),
    (3, 'To Kill a Mockingbird', 'Harper Lee', 4, 4, '2025-03-20'),
    (4, 'Pride and Prejudice', 'Jane Austen', 2, 2, '2025-04-25'),
    (5, 'The Catcher in the Rye', 'J.D. Salinger', 3, 3, '2025-05-30')
)");

echo "Database and tables created successfully, default users and books added.";
?>