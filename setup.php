<?php
$conn = new mysqli("db", "root", "rootpassword");
if ($conn->connect_error) {
    die("Can't connect: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS library_db");
$conn->select_db("library_db");
// default login table the credentials such as the user and the librarian
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('user','librarian')
)");

// default users        
$conn->query("INSERT IGNORE INTO users (username, password, role) VALUES
    ('admin', '1234', 'librarian'),
    ('user1', '1234', 'user')
"); 


$conn->query("CREATE TABLE IF NOT EXISTS books (
    isbn VARCHAR(20) PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    copies INT DEFAULT 1,
    available INT DEFAULT 1,
    date_added DATE
)");


$conn->query("INSERT IGNORE INTO books (isbn, title, author, copies, available, date_added) VALUES
    ('B001', 'The Great Gatsby', 'F. Scott Fitzgerald', 3, 3, '2025-01-10'),
    ('B002', '1984', 'George Orwell', 15, 5, '2025-02-15'),
    ('B003', 'To Kill a Mockingbird', 'Harper Lee', 4, 4, '2025-03-20'),
    ('B004', 'Pride and Prejudice', 'Jane Austen', 2, 2, '2025-04-25'),
    ('B005', 'Salimsim', 'Binibining Mia', 13, 3, '2025-05-30')
"); 

echo "Setup done. Users and books added.";
?>