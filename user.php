<?php
    // Your original database connection
    $mysql = new mysqli("db", "root", "rootpassword", "library_db");
    if ($mysql->connect_error) {
        die("<h2> Connection Failed: " . $mysql->connect_error . "</h2>");
    }
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
        <input type="text" name="query" placeholder="Search by Title, Author, Year, or ISBN" size="50" required>
        <input type="submit" value="Search">
    </form>
    <br>

<?php
    // This block displays messages after a user borrows or returns a book
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'borrow_success') {
            echo "<div class='message success'>Book borrowed successfully!</div>";
        } elseif ($_GET['status'] == 'return_success') {
            echo "<div class='message success'>Book returned successfully!</div>";
        } elseif ($_GET['status'] == 'error') {
            echo "<div class='message error'>An error occurred. That book may no longer be available.</div>";
        }
    }

    // --- Search Results from your original code, now with borrow functionality ---
    if (isset($_GET['query'])) {
        echo "<h3>Search Results</h3>";
        $search = "%" . $_GET['query'] . "%";

        // CORRECTED: Using lowercase column names (title, author, pub_year, isbn)
        $stmt = $mysql->prepare("
            SELECT * FROM books
            WHERE title LIKE ? OR author LIKE ? OR pub_year LIKE ? OR isbn LIKE ?
        ");
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        $data = $stmt->get_result();

        if ($data->num_rows > 0) {
            echo "<table>";
            // CORRECTED: Using lowercase column names in the table display
            echo "<tr><th>TITLE</th><th>AUTHOR</th><th>YEAR</th><th>ISBN</th><th>ACTION</th></tr>";
            while ($row = $data->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                echo "<td>" . htmlspecialchars($row['pub_year']) . "</td>";
                echo "<td>" . htmlspecialchars($row['isbn']) . "</td>";
                echo "<td>";
                if ($row['status'] == 'available') {
                    echo "<form action='borrow_handler.php' method='POST' style='display:inline;'>
                            <input type='hidden' name='book_id' value='" . $row['id'] . "'>
                            <button type='submit'>Borrow</button>
                          </form>";
                } else {
                    echo "<span class='borrowed-text'>Borrowed</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<h3>No books found.</h3>";
        }
        $stmt->close();
    }

    // --- NEW SECTION: Displaying books the user has borrowed ---
    echo "<h3>Your Borrowed Books</h3>";
    // CORRECTED: Using lowercase b.title and b.author
    $borrowed_query = "
        SELECT t.id as transaction_id, b.title, b.author, t.borrow_date
        FROM transactions t
        JOIN books b ON t.book_id = b.id
        WHERE t.return_date IS NULL
        ORDER BY t.borrow_date DESC
    ";
    $borrowed_data = $mysql->query($borrowed_query);
    if ($borrowed_data->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>TITLE</th><th>AUTHOR</th><th>BORROW DATE</th><th>ACTION</th></tr>";
        while ($row = $borrowed_data->fetch_assoc()) {
            echo "<tr>";
            // CORRECTED: Using lowercase title and author
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
            echo "<td>" . date('F j, Y', strtotime($row['borrow_date'])) . "</td>";
            echo "<td>
                    <form action='return_handler.php' method='POST' style='display:inline;'>
                      <input type='hidden' name='transaction_id' value='" . $row['transaction_id'] . "'>
                      <button type='submit'>Return</button>
                    </form>
                  </td>";
            echo "</tr>";
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