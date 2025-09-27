<?php
// --- DATABASE CONNECTION ---
$mysql = new mysqli("db", "root", "rootpassword", "library_db");
if ($mysql->connect_error) {
    die("<h2> Connection Failed: " . $mysql->connect_error . "</h2>");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Borrow & Return</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        form { margin-bottom: 20px; padding: 10px; border: 1px solid #0d120eff; width: 400px; }
        label { display: block; margin-top: 10px; }
        input, select, button { margin-top: 1px; padding: 1px; width: 100%; }
        table { border-collapse: collapse; width: 80%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f9f9f9ff; }
    </style>
</head>
<body>

<h2>📚 Borrow a Book</h2>
<form method="post">
    <label>Student Name:</label>
    <input type="text" name="student" required>

    <label>Select Book:</label>
    <select name="isbn_num" required>
        <option value="">-- Choose a Book --</option>
        <?php
        $books = $mysql->query("SELECT * FROM books WHERE avail_book > 0");
        while ($row = $books->fetch_assoc()) {
            echo "<option value='{$row['isbn_num']}'>
                    {$row['title_book']} by {$row['author_book']} ({$row['avail_book']} available)
                  </option>";
        }
        ?>
    </select>

    <button type="submit" name="borrow">Borrow</button>
</form>

<?php
// --- BORROW BOOK ---
if (isset($_POST['borrow'])) {
    $isbn = $_POST['isbn_num'];
    $student = $_POST['student'];

    // insert into borrowed_books
    $stmt = $mysql->prepare("INSERT INTO borrowed_books (isbn_num, student_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $isbn, $student);
    $stmt->execute();

    // update books availability
    $mysql->query("UPDATE books SET avail_book = avail_book - 1 WHERE isbn_num = '$isbn' AND avail_book > 0");

    echo "<p style='color:green;'>✅ Book borrowed successfully!</p>";
}
?>

<h2>📖 Borrowed Books</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Student</th>
        <th>Book</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php
    $borrowed = $mysql->query("SELECT bb.id, bb.student_name, b.title_book, bb.status 
                               FROM borrowed_books bb
                               JOIN books b ON bb.isbn_num = b.isbn_num");
    while ($row = $borrowed->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['title_book']}</td>
                <td>{$row['status']}</td>
                <td>";
        if ($row['status'] == 'borrowed') {
            echo "<form method='post' style='display:inline;'>
                    <input type='hidden' name='borrow_id' value='{$row['id']}'>
                    <button type='submit' name='return'>Return</button>
                  </form>";
        } else {
            echo "✔ Returned";
        }
        echo "</td></tr>";
    }
    ?>
</table>

<?php
// --- RETURN BOOK ---
if (isset($_POST['return'])) {
    $borrow_id = $_POST['borrow_id'];

    // get isbn of borrowed book
    $result = $mysql->query("SELECT isbn_num FROM borrowed_books WHERE id = $borrow_id AND status='borrowed'");
    if ($row = $result->fetch_assoc()) {
        $isbn = $row['isbn_num'];

        // update borrowed_books
        $mysql->query("UPDATE borrowed_books SET status='returned' WHERE id=$borrow_id");

        // update books availability
        $mysql->query("UPDATE books SET avail_book = avail_book + 1 WHERE isbn_num = '$isbn'");

        echo "<p style='color:blue;'>📘 Book returned successfully!</p>";
        echo "<meta http-equiv='refresh' content='0'>"; // refresh to update table
    }
}
?>

</body>
</html>
