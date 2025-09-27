<?php
// Use the same database connection details
$mysql = new mysqli("db", "root", "rootpassword", "library_db");
if ($mysql->connect_error) {
    die("Connection Failed: " . $mysql->connect_error);
}

if (isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];
    $mysql->begin_transaction();
    try {
        // Find the transaction and get the book ID
        $stmt_select = $mysql->prepare("SELECT book_id FROM transactions WHERE id = ? AND return_date IS NULL");
        $stmt_select->bind_param("i", $transaction_id);
        $stmt_select->execute();
        $transaction = $stmt_select->get_result()->fetch_assoc();

        if ($transaction) {
            $book_id = $transaction['book_id'];
            // Mark the transaction as returned
            $stmt_trans = $mysql->prepare("UPDATE transactions SET return_date = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt_trans->bind_param("i", $transaction_id);
            $stmt_trans->execute();

            // Update the book's status back to 'available'
            $stmt_book = $mysql->prepare("UPDATE books SET status = 'available' WHERE id = ?");
            $stmt_book->bind_param("i", $book_id);
            $stmt_book->execute();
            
            // Success! Commit and redirect.
            $mysql->commit();
            header("Location: user.php?status=return_success");
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