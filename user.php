<?php
$conn = new mysqli("db", "root", "rootpassword", "library_db");

if ($conn->connect_error) {
    die("<h2>Connection Failed</h2>");
}

$sql = "SELECT isbn_num, title_book, author_book, book_copy, avail_book, date_added 
        FROM books ORDER BY date_added DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Books List</title>
    <link rel="stylesheet" href="user.css">

</head>

<body>
    <!-- Merge into develop branch from student 3: Borrow and view catalog book feauture by Rachel Ramos -->
    <div class="header">
        <h1>Welcome to Library Management System</h1>
        <div class="top-buttons">
            <a href="catalog.php" class="btn">View Catalog</a>
            <a href="login.php" class="btn">Log Out</a>
        </div>
    </div>

    <div class="book-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='book-card'>
                        <div class='book-title'>" . $row["title_book"] . "</div>
                        <div class='book-author'>by " . $row["author_book"] . "</div>
                      </div>";
            }
        } else {
            echo "<p style='text-align:center;'>No books found.</p>";
        }
        $conn->close();
        ?>
    </div>

</body>

</html>