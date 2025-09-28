<?php

// Default setup script to create database and tables so that we have the same  working environment - Kin Orudente
$conn = new mysqli("db", "root", "rootpassword");
if ($conn->connect_error) {
    die("Can't connect: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS library_db");
$conn->select_db("library_db");


$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('user','librarian')
)");

     
$conn->query("INSERT IGNORE INTO users (username, password, role) VALUES
    ('admin', '1234', 'librarian'),
    ('user1', '1234', 'user')
");

$conn->query("DROP TABLE IF EXISTS books");

$conn->query("CREATE TABLE books (
    isbn_num VARCHAR(20) PRIMARY KEY,
    title_book VARCHAR(100) NOT NULL,
    author_book VARCHAR(100) NOT NULL,
    book_copy INT DEFAULT 1,
    avail_book INT DEFAULT 1,
    date_added DATE
)");

$conn->query("INSERT IGNORE INTO books (isbn_num, title_book, author_book, book_copy, avail_book, date_added) VALUES
    ('B001', 'The Great Gatsby', 'F. Scott Fitzgerald', 3, 3, '2025-01-10'),
    ('B002', '1984', 'George Orwell', 15, 5, '2025-02-15'),
    ('B003', 'To Kill a Mockingbird', 'Harper Lee', 4, 4, '2025-03-20'),
    ('B004', 'Pride and Prejudice', 'Jane Austen', 13, 2, '2025-04-25'),
    ('B005', 'Salimsim', 'Binibining Mia', 3, 3, '2025-05-30')
");


$conn->query("CREATE TABLE IF NOT EXISTS borrowed_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    borrower_name VARCHAR(100) NOT NULL,
    book_isbn VARCHAR(20) NOT NULL,
    borrow_date DATE,
    return_date DATE,
    status VARCHAR(20) DEFAULT 'pending'
)");


$conn->query("INSERT IGNORE INTO borrowed_books (borrower_name, book_isbn, borrow_date, return_date, status) VALUES
    ('Rachel Ramos', 'B001', '2025-09-01', NULL, 'pending'),
    ('Niel Reyes', 'B002', '2025-09-05', '2025-09-10', 'returned'),
    ('Kin Prudente', 'B003', '2025-09-12', NULL, 'pending'),
    ('Marc Reantaso', 'B004', '2025-09-15', NULL, 'pending'),
    ('Yasmien Regidor', 'B005', '2025-09-20', '2025-09-25', 'returned')
");

echo "Setup done. Users, books, and borrowed_books added.";
?>