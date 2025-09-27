<?php
// --- DATABASE CONNECTION ---
$mysql = new mysqli("db", "root", "rootpassword", "library_db");
if ($mysql->connect_error) {
    die("<h2> Connection Failed: " . $mysql->connect_error . "</h2>");
}

// This variable will hold any success or error messages to display
$status_message = '';

// --- ACTION HANDLER ---
// This block runs ONLY when a form is submitted to this page
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- BORROW LOGIC ---
    // Check if the submitted action was 'borrow'
    if (isset($_POST['action']) && $_POST['action'] == 'borrow') {
        if (isset($_POST['book_id'])) {
            $book_id = $_POST['book_id'];
            $mysql->begin_transaction();
            try {
                // Check if book is available
                $stmt_check = $mysql->prepare("SELECT status FROM books WHERE id = ? FOR UPDATE");
                $stmt_check->bind_param("i", $book_id);
                $stmt_check->execute();
                $result = $stmt_check->get_result()->fetch_assoc();

                if ($result && $result['status'] == 'available') {
                    // Update book status and create transaction
                    $stmt_update = $mysql->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?");
                    $stmt_update->bind_param("i", $book_id);
                    $stmt_update->execute();

                    $stmt_insert = $mysql->prepare("INSERT INTO transactions (book_id) VALUES (?)");
                    $stmt_insert->bind_param("i", $book_id);
                    $stmt_insert->execute();
                    
                    $mysql->commit();
                    $status_message = "<div class='message success'>Book borrowed successfully!</div>";
                } else {
                    $mysql->rollback();
                    $status_message = "<div class='message error'>Error: Book is not available.</div>";
                }
            } catch (mysqli_sql_exception $e) {
                $mysql->rollback();
                $status_message = "<div class='message error'>Database error during borrow.</div>";
            }
        }
    }

    // --- RETURN LOGIC ---
    // Check if the submitted action was 'return'
    if (isset($_POST['action']) && $_POST['action'] == 'return') {
        if (isset($_POST['transaction_id'])) {
            $transaction_id = $_POST['transaction_id'];
            $mysql->begin_transaction();
            try {
                // Find the transaction to get the book_id
                $stmt_select = $mysql->prepare("SELECT book_id FROM transactions WHERE id = ? AND return_date IS NULL");
                $stmt_select->bind_param("i", $transaction_id);
                $stmt_select->execute();
                $transaction = $stmt_select->get_result()->fetch_assoc();

                if ($transaction) {
                    $book_id = $transaction['book_id'];
                    // Update transaction and book status
                    $stmt_trans = $mysql->prepare("UPDATE transactions SET return_date = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt_trans->bind_param("i", $transaction_id);
                    $stmt_trans->execute();

                    $stmt_book = $mysql->prepare("UPDATE books SET status = 'available' WHERE id = ?");
                    $stmt_book->bind_param("i", $book_id);
                    $stmt_book->execute();
                    
                    $mysql->commit();
                    $status_message = "<div class='message success'>Book returned successfully!</div>";
                } else {
                    $mysql->rollback();
                    $status_message = "<div class='message error'>Error: Invalid transaction.</div>";
                }
            } catch (mysqli_sql_exception $e) {
                $mysql->rollback();
                $status_message = "<div class='message error'>Database error during return.</div>";
            }
        }
    }
} // End of POST request handling
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ONLINE LIBRARY</title>

</head>
<body>
    <h2>Hello Student! Search for a Book</h2>
    <form method="GET" action="user.php">
        <input type="text" name="query" placeholder="Search by Title, Author, Year, or ISBN" size="50" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
        <input type="submit" value="Search">
    </form>
    <br>

<?php
    // This is where the success or error message from the top of the file gets displayed
    if (!empty($status_message)) {
        echo $status_message;
    }

    // --- Search Results Display ---
    if (isset($_GET['query'])) {
        echo "<h3>Search Results</h3>";
        $search = "%" . $_GET['query'] . "%";
        $stmt = $mysql->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR pub_year LIKE ? OR isbn LIKE ?");
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        $data = $stmt->get_result();

        if ($data->num_rows > 0) {
            echo "<table><tr><th>TITLE</th><th>AUTHOR</th><th>YEAR</th><th>ISBN</th><th>ACTION</th></tr>";
            while ($row = $data->fetch_assoc()) {
                echo "<tr><td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                echo "<td>" . htmlspecialchars($row['pub_year']) . "</td>";
                echo "<td>" . htmlspecialchars($row['isbn']) . "</td><td>";
                if ($row['status'] == 'available') {
                    echo "<form action='user.php' method='POST' style='display:inline;'>
                            <input type='hidden' name='action' value='borrow'>
                            <input type='hidden' name='book_id' value='" . $row['id'] . "'>
                            <button type='submit'>Borrow</button>
                          </form>";
                } else {
                    echo "<span class='borrowed-text'>Borrowed</span>";
                }
                echo "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<h3>No books found.</h3>";
        }
        $stmt->close();
    }

    // --- Borrowed Books Display ---
    echo "<h3>Your Borrowed Books</h3>";
    $borrowed_query = "SELECT t.id as transaction_id, b.title, b.author, t.borrow_date FROM transactions t JOIN books b ON t.book_id = b.id WHERE t.return_date IS NULL ORDER BY t.borrow_date DESC";
    $borrowed_data = $mysql->query($borrowed_query);
    if ($borrowed_data->num_rows > 0) {
        echo "<table><tr><th>TITLE</th><th>AUTHOR</th><th>BORROW DATE</th><th>ACTION</th></tr>";
        while ($row = $borrowed_data->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
            echo "<td>" . date('F j, Y', strtotime($row['borrow_date'])) . "</td>";
            echo "<td>
                    <form action='user.php' method='POST' style='display:inline;'>
                      <input type='hidden' name='action' value='return'>
                      <input type='hidden' name='transaction_id' value='" . $row['transaction_id'] . "'>
                      <button type='submit'>Return</button>
                    </form>
                  </td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>You have not borrowed any books.</p>";
    }
    echo "<br><br><a href='login.php'>BACK TO LOGIN</a>";
    $mysql->close();
?>
</body>
</html>