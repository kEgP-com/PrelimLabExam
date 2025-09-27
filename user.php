<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// =================================================================
// DATABASE CONNECTION (IMPROVED)
// =================================================================
// Put your database details here.
// This makes it easier to change them in one place.
$db_host = "db";
$db_user = "root";
$db_pass = "rootpassword";
$db_name = "library_db";

$mysql = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($mysql->connect_error) {
    // Using die() will stop the script and show an error.
    die("<h2>❌ Database Connection Failed: " . $mysql->connect_error . "</h2>");
}

// Get the current user's ID from their username (important for tracking borrows)
$current_username = $_SESSION['username'];
$user_stmt = $mysql->prepare("SELECT id FROM users WHERE username = ?");
$user_stmt->bind_param("s", $current_username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_row = $user_result->fetch_assoc();
$user_id = $user_row['id'];
$user_stmt->close();

$action_message = '';

// =================================================================
// HANDLE BORROW AND RETURN ACTIONS (POST REQUESTS)
// =================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- HANDLE BORROW ACTION ---
    if (isset($_POST['borrow'])) {
        $book_id_to_borrow = $_POST['book_id'];

        // Use a transaction to ensure data integrity
        $mysql->begin_transaction();

        try {
            // 1. Check if the book is available
            $check_stmt = $mysql->prepare("SELECT avail_book FROM books WHERE id = ? FOR UPDATE");
            $check_stmt->bind_param("i", $book_id_to_borrow);
            $check_stmt->execute();
            $book_avail = $check_stmt->get_result()->fetch_assoc()['avail_book'];
            $check_stmt->close();

            if ($book_avail > 0) {
                // 2. Decrement the available book count
                $update_stmt = $mysql->prepare("UPDATE books SET avail_book = avail_book - 1 WHERE id = ?");
                $update_stmt->bind_param("i", $book_id_to_borrow);
                $update_stmt->execute();
                $update_stmt->close();

                // 3. Record the borrow action
                $due_date = date('Y-m-d H:i:s', strtotime('+14 days')); // 2-week due date
                $insert_stmt = $mysql->prepare("INSERT INTO borrowed_books (user_id, book_id, borrow_date, due_date) VALUES (?, ?, NOW(), ?)");
                $insert_stmt->bind_param("iis", $user_id, $book_id_to_borrow, $due_date);
                $insert_stmt->execute();
                $insert_stmt->close();

                $mysql->commit();
                $action_message = "<font color='green'>✅ Book borrowed successfully! Due in 14 days.</font>";
            } else {
                $mysql->rollback();
                $action_message = "<font color='red'>❌ Sorry, that book is no longer available.</font>";
            }
        } catch (mysqli_sql_exception $exception) {
            $mysql->rollback();
            $action_message = "<font color='red'>❌ An error occurred. Could not borrow book.</font>";
        }
    }

    // --- HANDLE RETURN ACTION ---
    if (isset($_POST['return'])) {
        $borrow_id_to_return = $_POST['borrow_id'];
        $book_id_to_return = $_POST['book_id'];

        $mysql->begin_transaction();
        try {
            // 1. Mark the book as returned in the borrowed_books table
            $update_borrow_stmt = $mysql->prepare("UPDATE borrowed_books SET return_date = NOW() WHERE id = ? AND user_id = ?");
            $update_borrow_stmt->bind_param("ii", $borrow_id_to_return, $user_id);
            $update_borrow_stmt->execute();
            $update_borrow_stmt->close();

            // 2. Increment the available book count
            $update_book_stmt = $mysql->prepare("UPDATE books SET avail_book = avail_book + 1 WHERE id = ?");
            $update_book_stmt->bind_param("i", $book_id_to_return);
            $update_book_stmt->execute();
            $update_book_stmt->close();

            $mysql->commit();
            $action_message = "<font color='green'>✅ Thank you for returning the book!</font>";

        } catch (mysqli_sql_exception $exception) {
            $mysql->rollback();
            $action_message = "<font color='red'>❌ An error occurred. Could not return book.</font>";
        }
    }
}


// =================================================================
// FETCH USER'S CURRENTLY BORROWED BOOKS
// =================================================================
$borrowed_list = [];
$borrowed_stmt = $mysql->prepare(
    "SELECT bb.id, b.title_book, bb.borrow_date, bb.due_date, bb.book_id
     FROM borrowed_books bb
     JOIN books b ON bb.book_id = b.id
     WHERE bb.user_id = ? AND bb.return_date IS NULL
     ORDER BY bb.due_date ASC"
);
$borrowed_stmt->bind_param("i", $user_id);
$borrowed_stmt->execute();
$borrowed_result = $borrowed_stmt->get_result();
while ($row = $borrowed_result->fetch_assoc()) {
    $borrowed_list[] = $row;
}
$borrowed_stmt->close();


// =================================================================
// HANDLE BOOK SEARCH (GET REQUEST)
// =================================================================
$searchResults = [];
$searchPerformed = false;
if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search = trim($_GET['query']);
    $searchPerformed = true;
    
    // Use prepared statement to prevent SQL injection
    $stmt = $mysql->prepare("SELECT * FROM books WHERE title_book LIKE ? OR author_book LIKE ? OR isbn_num LIKE ?");
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
</head>
<body>
    <table width="100%" border="0" cellpadding="15" bgcolor="#2c3e50">
        <tr>
            <td align="center" width="15%">
                <font color="white" size="6"><b>📚 Library Management System</b></font>
            </td>
            <td align="right" width="30%">
                <font color="white">
                    Welcome, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>!<br>
                    <a href="login.php?logout=true" style="color:white;">Logout</a>
                </font>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellpadding="20">
        <tr>
            <td>
                <?php if (!empty($action_message)): ?>
                <table width="100%" border="0" cellpadding="15" bgcolor="#ecf0f1" style="margin-bottom: 20px;">
                    <tr><td align="center"><b><?php echo $action_message; ?></b></td></tr>
                </table>
                <?php endif; ?>

                <table width="100%" border="0" cellpadding="15">
                    <tr>
                        <td>
                            <h2>📖 My Borrowed Books</h2>
                            <?php if (count($borrowed_list) > 0): ?>
                                <table width='100%' border='1' cellpadding='12' cellspacing='0' style='border-collapse: collapse;'>
                                    <tr bgcolor='#34495e'>
                                        <th><font color='white'>TITLE</font></th>
                                        <th><font color='white'>BORROW DATE</font></th>
                                        <th><font color='white'>DUE DATE</font></th>
                                        <th width="15%"><font color='white'>ACTION</font></th>
                                    </tr>
                                    <?php foreach ($borrowed_list as $borrowed_book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($borrowed_book['title_book']); ?></td>
                                        <td><?php echo date("F j, Y", strtotime($borrowed_book['borrow_date'])); ?></td>
                                        <td><?php echo date("F j, Y", strtotime($borrowed_book['due_date'])); ?></td>
                                        <td align='center'>
                                            <form method="POST" action="user.php" style="margin:0;">
                                                <input type="hidden" name="borrow_id" value="<?php echo $borrowed_book['id']; ?>">
                                                <input type="hidden" name="book_id" value="<?php echo $borrowed_book['book_id']; ?>">
                                                <input type="submit" name="return" value="Return Book" style="padding:5px 10px;">
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php else: ?>
                                <p>You have not borrowed any books.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <br><hr><br>

                <table width="100%" border="0" cellpadding="15" bgcolor="#ecf0f1">
                    <tr>
                        <td align="center">
                            <h2>🔍 Search for a Book</h2>
                            <p>Enter a book title, author, or ISBN to search our library</p>
                            <form method="GET" action="user.php">
                                <table border="0" align="center">
                                    <tr>
                                        <td>
                                            <input type="text" name="query" size="50" 
                                                   placeholder="Enter book title, author, or ISBN..." 
                                                   value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                                                   style="padding:8px; width:300px;">
                                        </td>
                                        <td>
                                            <input type="submit" value="Search Books" style="padding:8px 20px; width:120px;">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </td>
                    </tr>
                </table>

                <br>

                <table width="100%" border="0" cellpadding="15">
                    <tr>
                        <td>
                            <h2>📋 Search Results</h2>
                            <?php if ($searchPerformed): ?>
                                <p><font color="#27ae60"><b>📊 <?php echo count($searchResults); ?> book(s) found</b></font></p>
                            <?php endif; ?>
                            
                            <?php
                            if ($searchPerformed) {
                                if (count($searchResults) > 0) {
                                    echo "<table width='100%' border='1' cellpadding='12' cellspacing='0' style='border-collapse: collapse;'>";
                                    echo "<tr bgcolor='#34495e'>
                                            <th width='25%'><font color='white'>📖 TITLE</font></th>
                                            <th width='25%'><font color='white'>✍️ AUTHOR</font></th>
                                            <th width='10%'><font color='white'>📚 COPIES</font></th>
                                            <th width='15%'><font color='white'>✅ AVAILABLE</font></th>
                                            <th width='15%'><font color='white'>🏷️ ISBN</font></th>
                                            <th width='10%'><font color='white'>⚙️ ACTION</font></th>
                                          </tr>";
                                    
                                    $rowCount = 0;
                                    foreach ($searchResults as $row) {
                                        $bgColor = ($rowCount % 2 == 0) ? '#ffffff' : '#f8f9fa';
                                        echo "<tr bgcolor='$bgColor'>";
                                        echo "<td><b>" . htmlspecialchars($row['title_book']) . "</b></td>";
                                        echo "<td>" . htmlspecialchars($row['author_book']) . "</td>";
                                        echo "<td align='center'>" . $row['book_copy'] . "</td>";
                                        echo "<td align='center'><b>" . $row['avail_book'] . "</b></td>";
                                        echo "<td><code>" . $row['isbn_num'] . "</code></td>";
                                        echo "<td align='center'>";
                                        if ($row['avail_book'] > 0) {
                                            echo "<form method='POST' action='user.php' style='margin:0;'>
                                                    <input type='hidden' name='book_id' value='{$row['id']}'>
                                                    <input type='submit' name='borrow' value='Borrow' style='padding:5px 10px;'>
                                                  </form>";
                                        } else {
                                            echo "<font color='red'>Not Available</font>";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                        $rowCount++;
                                    }
                                    
                                    echo "</table>";
                                } else {
                                    echo "<table width='100%' border='0' cellpadding='40' bgcolor='#f8f9fa'>
                                            <tr>
                                                <td align='center'>
                                                    <h3>❌ No books found</h3>
                                                    <p>Try a different search term</p>
                                                </td>
                                            </tr>
                                          </table>";
                                }
                            } else {
                                echo "<table width='100%' border='0' cellpadding='40' bgcolor='#f8f9fa'>
                                        <tr>
                                            <td align='center'>
                                                <h3>⭐ Ready to search</h3>
                                                <p>Enter a search term above to find books in our library</p>
                                            </td>
                                        </tr>
                                      </table>";
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>