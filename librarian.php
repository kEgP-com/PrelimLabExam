<?php
// Librarian book management (View Catalog Feature Only)
session_start();

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'librarian') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("db", "root", "rootpassword", "library_db");
if ($conn->connect_error) {
    die("Can't connect: " . $conn->connect_error);
}

  // Student 3 Feature: View Catalog

$result = $conn->query("SELECT * FROM books ORDER BY date_added DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Librarian Dashboard - View Catalog</title>
    <link rel="stylesheet" href="style_library.css">
</head>
<body>
    <div class="header">
        <h2>Librarian Dashboard (View Catalog Only)</h2>
        <form method="post" style="margin:0;">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="book-list">
        <h3>Book Catalog</h3>

        <!-- Student 3 Feature: Browse/View Catalog -->
        <div class="table-container">
            <table>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Copies</th>
                    <th>Available</th>
                    <th>Date Added</th>
                </tr>
                <?php if ($result && $result->num_rows > 0) { 
                    while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['isbn']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['author']; ?></td>
                    <td><?php echo $row['copies']; ?></td>
                    <td><?php echo $row['available']; ?></td>
                    <td><?php echo $row['date_added']; ?></td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td colspan="6">No books found.</td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>
