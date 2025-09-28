<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// =================================================================
// DATABASE CONNECTION
// =================================================================
$db_host = "db";
$db_user = "root";
$db_pass = "rootpassword";
$db_name = "library_db";

$mysql = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysql->connect_error) {
    die("<h2>❌ Database Connection Failed: " . $mysql->connect_error . "</h2>");
}

// =================================================================
// CONFIG
// =================================================================
$book_table_primary_key = 'isbn_num';

// =================================================================
// FETCH ALL BOOKS
// =================================================================
$all_books = [];
$result = $mysql->query("SELECT * FROM books ORDER BY title_book ASC");
while ($row = $result->fetch_assoc()) {
    $all_books[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Catalog</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background-color: #f0f2f5;
}
.book-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding: 20px;
    gap: 20px;
}
.book-card {
    background: #fff;
    width: 220px;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.book-card:hover { transform: scale(1.05); }
.book-title { font-weight: bold; font-size: 1.1em; margin: 10px 0 5px; }
.book-author { color: #555; font-size: 14px; margin-bottom: 15px; }
.book-card form input[type="submit"] {
    background-color: #27ae60;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
.not-available { color: #e74c3c; font-weight: bold; }
</style>
</head>
<body>
    <table border="0" cellpadding="15" bgcolor="#2c3e50" width="100%">
        <tr>
            <td><font color="white" size="6"><b>📚 Book Catalog</b></font></td>
            <td align="right">
                <font color="white">
                    Welcome, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>!<br>
                    <a href="login.php?logout=true" style="color:white;">Logout</a> |
                    <a href="user.php" style="color:white;">My Books</a>
                </font>
            </td>
        </tr>
    </table>

    <h2 style="text-align:center;">📚 Book Catalog</h2>
    <div class="book-container">
        <?php if (count($all_books) > 0): ?>
            <?php foreach ($all_books as $book): ?>
                <div class="book-card">
                    <div>
                        <div class="book-title"><?php echo htmlspecialchars($book["title_book"]); ?></div>
                        <div class="book-author">by <?php echo htmlspecialchars($book["author_book"]); ?></div>
                    </div>
                    <div>
                        <?php if ($book['avail_book'] > 0): ?>
                            <form method="POST" action="user.php">
                                <input type="hidden" name="book_key_to_borrow" value="<?php echo htmlspecialchars($book[$book_table_primary_key]); ?>">
                                <input type="submit" name="borrow" value="Borrow (<?php echo $book['avail_book']; ?> Available)">
                            </form>
                        <?php else: ?>
                            <div class="not-available">Not Available</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No books found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
