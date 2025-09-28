<?php
// Librarian book management
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

$message = "";
$editBook = null;

// Merge from student 1 : Create book features by Mr. Prudente
// Add Book
if (isset($_POST['add_book'])) {
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $copies = (int)$_POST['copies'];
    $available = (int)$_POST['available'];
    $date_added = $conn->real_escape_string($_POST['date_added']);

    $sql = "INSERT INTO books (isbn, title, author, copies, available, date_added)
            VALUES ('$isbn', '$title', '$author', $copies, $available, '$date_added')";

    if ($conn->query($sql) === TRUE) {
        $message = "Book added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Merge from student 2 :Edit and Delete Features on the book by Mr. Reyes
// Edit
if (isset($_GET['edit'])) {
    $isbn = $conn->real_escape_string($_GET['edit']);
    $result = $conn->query("SELECT * FROM books WHERE isbn='$isbn'");
    if ($result && $result->num_rows > 0) {
        $editBook = $result->fetch_assoc();
    }
}

// Update
if (isset($_POST['update_book'])) {
    $old_isbn = $conn->real_escape_string($_POST['old_isbn']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $copies = (int)$_POST['copies'];
    $available = (int)$_POST['available'];
    $date_added = $conn->real_escape_string($_POST['date_added']);

    $sql = "UPDATE books 
            SET isbn='$isbn', title='$title', author='$author', copies=$copies, available=$available, date_added='$date_added'
            WHERE isbn='$old_isbn'";

    if ($conn->query($sql) === TRUE) {
        $message = "Book updated successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Delete
if (isset($_GET['delete'])) {
    $isbn = $conn->real_escape_string($_GET['delete']);
    $sql = "DELETE FROM books WHERE isbn='$isbn'";
    if ($conn->query($sql) === TRUE) {
        $message = "Book deleted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Search
$searchQuery = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = $conn->real_escape_string(trim($_GET['search']));
    $result = $conn->query("SELECT * FROM books 
                            WHERE title LIKE '%$searchQuery%' 
                               OR author LIKE '%$searchQuery%' 
                               OR isbn LIKE '%$searchQuery%'
                            ORDER BY date_added DESC");
} else {
    $result = $conn->query("SELECT * FROM books ORDER BY date_added DESC");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Librarian Dashboard</title>
    <link rel="stylesheet" href="style_library.css">
</head>

<body>
    <div class="header">
        <h2>Librarian Dashboard</h2>
        <form method="post" style="margin:0;">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="box">
        <h3><?php echo $editBook ? "Edit Book" : "Add New Book"; ?></h3>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>

        <form method="post" action="">
            <?php if ($editBook) { ?>
            <input type="hidden" name="old_isbn" value="<?php echo $editBook['isbn']; ?>">
            <?php } ?>

            ISBN: <input type="text" name="isbn" value="<?php echo $editBook['isbn'] ?? ''; ?>" required><br>
            Title: <input type="text" name="title" value="<?php echo $editBook['title'] ?? ''; ?>" required><br>
            Author: <input type="text" name="author" value="<?php echo $editBook['author'] ?? ''; ?>" required><br>
            Copies: <input type="number" name="copies" value="<?php echo $editBook['copies'] ?? '1'; ?>" min="1"
                required><br>
            Available: <input type="number" name="available" value="<?php echo $editBook['available'] ?? '1'; ?>"
                min="0" required><br>
            Date Added: <input type="date" name="date_added" value="<?php echo $editBook['date_added'] ?? ''; ?>"
                required><br>

            <?php if ($editBook) { ?>
            <input type="submit" name="update_book" value="Save Changes">
            <a href="librarian.php" class="cancel-btn">Cancel</a>
            <?php } else { ?>
            <input type="submit" name="add_book" value="Add Book">
            <?php } ?>
        </form>
    </div>
  
 <!-- This was merged together in the same table by Ms. Ramos and Ms. Regidor before merging into develop branch-->
<!-- Merge from student 3 into develop :  browse and view catalog -->
    <div class="book-list">
        <h3>Book List</h3>

        <!-- Merge from student4 into develop :  Search books feature.  -->
        <form method="get" action="librarian.php" class="search-form">
            <input type="text" name="search" placeholder="Search by Title, Author, or ISBN"
                value="<?php echo isset($searchQuery) ? $searchQuery : ''; ?>">
            <button type="submit">Search</button>
            <a href="librarian.php"><button type="button">Clear</button></a>
        </form>

        <div class="table-container">
            <table>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Copies</th>
                    <th>Available</th>
                    <th>Date Added</th>
                    <th>Action</th>
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
                    <td>
                        <a href="?edit=<?php echo $row['isbn']; ?>"><button type="button">Edit</button></a>
                        <a href="?delete=<?php echo $row['isbn']; ?>" onclick="return confirm('Delete this book?')">
                            <button type="button">Delete</button>
                        </a>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td colspan="7">No books found.</td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>

</html>