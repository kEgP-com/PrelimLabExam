<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$mysql = new mysqli("db", "root", "rootpassword", "library_db");
if ($mysql->connect_error) {
    die("Connection failed: " . $mysql->connect_error);
}

$username = $_SESSION['username'];

// --- SEARCH FUNCTIONALITY ---
$searchResults = [];
$searchPerformed = false;

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search = trim($_GET['query']);
    $searchPerformed = true;

    $stmt = $mysql->prepare("SELECT * FROM books WHERE title_book LIKE ? OR author_book LIKE ? OR isbn_num LIKE ?");
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
    $stmt->close();
}

// --- MY BORROWED BOOKS ---
$myBorrows = [];
$borrowStmt = $mysql->prepare("
    SELECT b.title_book, b.author_book, br.borrow_date 
    FROM borrows br 
    JOIN books b ON br.book_id = b.id 
    WHERE br.username = ?
    ORDER BY br.borrow_date DESC
");
$borrowStmt->bind_param("s", $username);
$borrowStmt->execute();
$borrowRes = $borrowStmt->get_result();

while ($row = $borrowRes->fetch_assoc()) {
    $myBorrows[] = $row;
}
$borrowStmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Library</title>
</head>
<body>
    <!-- Header -->
    <table width="100%" border="0" cellpadding="15" bgcolor="#2c3e50">
        <tr>
            <td align="center" width="60%">
                <font color="white" size="6"><b>📚 Library Management System</b></font>
            </td>
            <td align="right" width="40%">
                <font color="white">
                    Welcome, <b><?php echo htmlspecialchars($username); ?></b>!<br>
                    <a href="login.php?logout=true" style="color:white;">Logout</a>
                </font>
            </td>
        </tr>
    </table>

    <!-- Search Section -->
    <h2>🔍 Search for a Book</h2>
    <form method="GET" action="user.php">
        <input type="text" name="query" size="50"
            placeholder="Enter book title, author, or ISBN..."
            value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
            style="padding:8px; width:300px;">
        <input type="submit" value="Search Books" style="padding:8px 20px;">
    </form>

    <!-- Search Results -->
    <h2>📋 Search Results</h2>
    <?php if ($searchPerformed): ?>
        <p><b><?php echo count($searchResults); ?> book(s) found</b></p>
        <?php if (count($searchResults) > 0): ?>
            <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
                <tr bgcolor="#34495e">
                    <th><font color="white">📖 Title</font></th>
                    <th><font color="white">✍️ Author</font></th>
                    <th><font color="white">📚 Copies</font></th>
                    <th><font color="white">✅ Available</font></th>
                    <th><font color="white">🏷️ ISBN</font></th>
                    <th><font color="white">Action</font></th>
                </tr>
                <?php 
                $rowCount = 0;
                foreach ($searchResults as $row): 
                    $bgColor = ($rowCount % 2 == 0) ? '#ffffff' : '#f8f9fa';
                ?>
                <tr bgcolor="<?php echo $bgColor; ?>">
                    <td><b><?php echo htmlspecialchars($row['title_book']); ?></b></td>
                    <td><?php echo htmlspecialchars($row['author_book']); ?></td>
                    <td align="center"><?php echo $row['book_copy']; ?></td>
                    <td align="center"><b><?php echo $row['avail_book']; ?></b></td>
                    <td><code><?php echo $row['isbn_num']; ?></code></td>
                    <td align="center">
                        <?php if ($row['book_copy'] > 0): ?>
                            <form method="POST" action="borrow.php">
                                <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                                <input type="submit" value="Borrow">
                            </form>
                        <?php else: ?>
                            <b>❌ Not Available</b>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    $rowCount++;
                endforeach; 
                ?>
            </table>
        <?php else: ?>
            <p>❌ No books found. Try another search.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>⭐ Enter a search term above to find books.</p>
    <?php endif; ?>

    <hr>

    <!-- My Borrowed Books -->
    <h2>📖 My Borrowed Books</h2>
    <?php if (count($myBorrows) > 0): ?>
        <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
            <tr bgcolor="#34495e">
                <th><font color="white">📖 Title</font></th>
                <th><font color="white">✍️ Author</font></th>
                <th><font color="white">📅 Borrow Date</font></th>
            </tr>
            <?php 
            $rowCount = 0;
            foreach ($myBorrows as $row): 
                $bgColor = ($rowCount % 2 == 0) ? '#ffffff' : '#f8f9fa';
            ?>
            <tr bgcolor="<?php echo $bgColor; ?>">
                <td><b><?php echo htmlspecialchars($row['title_book']); ?></b></td>
                <td><?php echo htmlspecialchars($row['author_book']); ?></td>
                <td><?php echo $row['borrow_date']; ?></td>
            </tr>
            <?php 
                $rowCount++;
            endforeach; 
            ?>
        </table>
    <?php else: ?>
        <p>📌 You have not borrowed any books yet.</p>
    <?php endif; ?>

    <br>
    <a href="login.php">← Back to Login Page</a>
</body>
</html>
