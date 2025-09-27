<?php
// Use the same database connection details as your user.php file
$mysql = new mysqli("db", "root", "rootpassword", "library_db");
if ($mysql->connect_error) {
    die("Connection Failed: " . $mysql->connect_error);
}

if (isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $mysql->begin_transaction();
    try {
        // Check if the book is still available
        $stmt_check = $mysql->prepare("SELECT status FROM books WHERE id = ? FOR UPDATE");
        $stmt_check->bind_param("i", $book_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result()->fetch_assoc();
        
        if ($result && $result['status'] == 'available') {
            // Update book status to 'borrowed'
            $stmt_update = $mysql->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?");
            $stmt_update->bind_param("i", $book_id);
            $stmt_update->execute();

            // Create a new transaction record
            $stmt_insert = $mysql->prepare("INSERT INTO transactions (book_id) VALUES (?)");
            $stmt_insert->bind_param("i", $book_id);
            $stmt_insert->execute();
            
            // Success! Commit changes and redirect back to the main page.
            $mysql->commit();
            header("Location: user.php?status=borrow_success");
        } else {
            $mysql->rollback();
            header("Location: user.php?status=error");
        }
    } catch (mysqli_sql_exception $exception) {
        $mysql->rollback();
        header("Location: user.php?status=error");
    }
} else {
    header("Location: user.php?status=error");
}
$mysql->close();
exit();
?>