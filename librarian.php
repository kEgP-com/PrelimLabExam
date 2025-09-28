<?php
session_start();

$conn = new mysqli("db", "root", "rootpassword", "library_db");
if ($conn->connect_error) {
    die("Can't connect: " . $conn->connect_error);
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
// s  dsdsdsds

if (isset($_GET['delete'])) {
    $isbn = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM books WHERE isbn_num=?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $stmt->close();
    header("Location: librarian.php"); 
    exit;
}

if (isset($_POST['update'])) {
    $isbn = $_POST['isbn_num'];
    $title = $_POST['title_book'];
    $author = $_POST['author_book'];
    $copies = $_POST['book_copy'];
    $available = $_POST['avail_book'];

    $stmt = $conn->prepare("UPDATE books SET title_book=?, author_book=?, book_copy=?, avail_book=? WHERE isbn_num=?");
    $stmt->bind_param("ssiis", $title, $author, $copies, $available, $isbn);
    $stmt->execute();
    $stmt->close();

    header("Location: librarian.php");
    exit;
}

$result = $conn->query("SELECT * FROM books");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Librarian  Manage Books</title>
    <style>
        .container {
            width: 90%;
            margin: auto;
            font-family: Arial, sans-serif;
        }
        .table-wrapper {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: center;
        }
        button, a {
            padding: 4px 8px;
            text-decoration: none;
        }
        a {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">

        <h2>Librarian Panel - Manage Books</h2>

        <a href="librarian.php?logout=true" class="logout-btn">Logout</a>

        <table>
            <tr>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Copies</th>
                <th>Available</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="post" action="librarian.php">
                    <td><?= htmlspecialchars($row['isbn_num']) ?></td>
                    <td><input type="text" name="title_book" value="<?= htmlspecialchars($row['title_book']) ?>"></td>
                    <td><input type="text" name="author_book" value="<?= htmlspecialchars($row['author_book']) ?>"></td>
                    <td><input type="number" name="book_copy" value="<?= $row['book_copy'] ?>"></td>
                    <td><input type="number" name="avail_book" value="<?= $row['avail_book'] ?>"></td>
                    <td><?= $row['date_added'] ?></td>
                    <td>
                        <input type="hidden" name="isbn_num" value="<?= $row['isbn_num'] ?>">
                        <button type="submit" name="update">Update</button>
                        <a href="librarian.php?delete=<?= $row['isbn_num'] ?>" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </table>

    </div> 
</body>
</html>
