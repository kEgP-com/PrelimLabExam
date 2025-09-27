<?php
session_start();
$conn = new mysqli("db", "root", "rootpassword", "library_db");
if ($conn->connect_error) {
    die("Can't connect: " . $conn->connect_error);
}

// Require login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Show all books
$all_books = $conn->query("SELECT * FROM books");

// Show borrowed books
$borrowed = $conn->query("SELECT b.id AS borrow_id, b.user_id, bk.title, bk.author, b.borrow_date, b.return_date, b.status 
                          FROM borrowed_books b 
                          JOIN books bk ON b.book_id = bk.id 
                          ORDER BY b.borrow_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Librarian Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= $_SESSION['username']; ?> (Librarian)</h2>
    <a href="logout.php">Logout</a>

    <h3>📚 All Books</h3>
    <table border="1" cellpadding="5">
        <tr><th>Title</th><th>Author</th><th>Status</th></tr>
        <?php while ($row = $all_books->fetch_assoc()): ?>
            <tr>
                <td><?= $row['title']; ?></td>
                <td><?= $row['author']; ?></td>
                <td><?= $row['available'] ? "✅ Available" : "❌ Borrowed"; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>📖 Borrowed Books Log</h3>
    <table border="1" cellpadding="5">
        <tr><th>User</th><th>Book</th><th>Borrowed</th><th>Returned</th><th>Status</th></tr>
        <?php while ($row = $borrowed->fetch_assoc()): ?>
            <tr>
                <td><?= $row['user_id']; ?></td>
                <td><?= $row['title']; ?> by <?= $row['author']; ?></td>
                <td><?= $row['borrow_date']; ?></td>
                <td><?= $row['return_date'] ?: "Not yet"; ?></td>
                <td><?= ucfirst($row['status']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
