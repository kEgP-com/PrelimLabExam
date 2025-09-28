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

$messageBook = "";
$messageBorrow = "";
$editBook = null;


function generateISBN($conn) {
    $result = $conn->query("SELECT isbn_num FROM books ORDER BY CAST(SUBSTRING(isbn_num, 2) AS UNSIGNED) DESC LIMIT 1");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastNum = (int)substr($row['isbn_num'], 1);
        $newNum = $lastNum + 1;
    } else {
        $newNum = 1;
    }

    return 'B' . str_pad($newNum, 3, '0', STR_PAD_LEFT);
}


// Merge into develop branch from student 1: Create book feauture by Kin Prudente
// -------------------- ADD BOOK --------------------
if (isset($_POST['add_book'])) {
    $isbn = generateISBN($conn); 
    $title = $conn->real_escape_string($_POST['title_book']);
    $author = $conn->real_escape_string($_POST['author_book']);
    $copies = (int)$_POST['book_copy'];
    $available = (int)$_POST['avail_book'];
    $date_added = $conn->real_escape_string($_POST['date_added']);

    $sql = "INSERT INTO books (isbn_num, title_book, author_book, book_copy, avail_book, date_added)
            VALUES ('$isbn', '$title', '$author', $copies, $available, '$date_added')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['flash'] = "Book added successfully! (ISBN: $isbn)";
    } else {
        $_SESSION['flash'] = "Error: " . $conn->error;
    }
    header("Location: librarian.php"); 
    exit;
}


// Merge into develop branch from student 2: Edit and Delete book feauture by Niel Reyes
// -------------------- EDIT BOOK --------------------
if (isset($_GET['edit'])) {
    $isbn = $conn->real_escape_string($_GET['edit']);
    $result = $conn->query("SELECT * FROM books WHERE isbn_num='$isbn'");
    if ($result && $result->num_rows > 0) {
        $editBook = $result->fetch_assoc();
    }
}

// -------------------- UPDATE BOOK --------------------
if (isset($_POST['update_book'])) {
    $old_isbn = $conn->real_escape_string($_POST['old_isbn']); // ISBN stays fixed
    $title = $conn->real_escape_string($_POST['title_book']);
    $author = $conn->real_escape_string($_POST['author_book']);
    $copies = (int)$_POST['book_copy'];
    $available = (int)$_POST['avail_book'];
    $date_added = $conn->real_escape_string($_POST['date_added']);

    $sql = "UPDATE books 
            SET title_book='$title', author_book='$author', 
                book_copy=$copies, avail_book=$available, date_added='$date_added'
            WHERE isbn_num='$old_isbn'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['flash'] = "Book updated successfully!";
    } else {
        $_SESSION['flash'] = "Error: " . $conn->error;
    }
    header("Location: librarian.php"); 
    exit;
}

// -------------------- DELETE BOOK --------------------
if (isset($_GET['delete'])) {
    $isbn = $conn->real_escape_string($_GET['delete']);
    $sql = "DELETE FROM books WHERE isbn_num='$isbn'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['flash'] = "Book deleted successfully!";
    } else {
        $_SESSION['flash'] = "Error: " . $conn->error;
    }
    header("Location: librarian.php"); 
}


// Merge into develop branch from student 3: Search book feauture by Yasmien Regidor
// -------------------- SEARCH BOOKS --------------------
$searchQuery = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = $conn->real_escape_string(trim($_GET['search']));
    $result = $conn->query("SELECT * FROM books 
                            WHERE title_book LIKE '%$searchQuery%' 
                               OR author_book LIKE '%$searchQuery%' 
                               OR isbn_num LIKE '%$searchQuery%'
                            ORDER BY date_added DESC");
} else {
    $result = $conn->query("SELECT * FROM books ORDER BY date_added DESC");
}


// Merge into develop branch from student 5: feature/borrow and return book feauture by Marc Reantaso
// -------------------- RETURN BOOK --------------------
if (isset($_POST['return_book'])) {
    $borrow_id = (int)$_POST['borrow_id'];

    // Update borrowed_books
    $conn->query("UPDATE borrowed_books 
                  SET status='returned', return_date=CURDATE() 
                  WHERE id=$borrow_id");

    // Increase available count
    $conn->query("UPDATE books 
                  SET avail_book = avail_book + 1 
                  WHERE isbn_num = (SELECT book_isbn FROM borrowed_books WHERE id=$borrow_id)");

    $_SESSION['flash'] = "Book marked as returned.";
    header("Location: librarian.php"); 
    exit;
}

// -------------------- FETCH BORROW HISTORY --------------------   
$borrowed = $conn->query("SELECT b.id AS borrow_id, b.borrower_name, bk.title_book, bk.author_book, 
                                 b.borrow_date, b.return_date, b.status
                          FROM borrowed_books b
                          JOIN books bk ON b.book_isbn = bk.isbn_num
                          ORDER BY b.borrow_date DESC");


if (isset($_SESSION['flash'])) {
    $messageBook = $_SESSION['flash'];
    unset($_SESSION['flash']);
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
    <!-- Merge into develop branch from student 1: Create book feauture by Kin Prudente -->
    <div class="dashboard">
        <div class="box">
            <h3><?php echo $editBook ? "Edit Book" : "Add New Book"; ?></h3>
            <?php if ($messageBook) { ?>
            <p class="message <?php echo strpos($messageBook, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $messageBook; ?>
            </p>
            <?php } ?>

            <form method="post" action="">
                <?php if ($editBook) { ?>

                <input type="hidden" name="old_isbn" value="<?php echo $editBook['isbn_num']; ?>">
                ISBN: <input type="text" value="<?php echo $editBook['isbn_num']; ?>" readonly><br>
                <?php } ?>

                Title: <input type="text" name="title_book" value="<?php echo $editBook['title_book'] ?? ''; ?>"
                    required><br>
                Author: <input type="text" name="author_book" value="<?php echo $editBook['author_book'] ?? ''; ?>"
                    required><br>
                Copies: <input type="number" name="book_copy" value="<?php echo $editBook['book_copy'] ?? '1'; ?>"
                    min="1" required><br>
                Available: <input type="number" name="avail_book" value="<?php echo $editBook['avail_book'] ?? '1'; ?>"
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


        <!-- Merge into develop branch from student 5: feature/borrow and return book feauture by Marc Reantaso-->
        <div class="borrow-history">
            <h3>Borrowed & Returned History</h3>
            <?php if ($messageBorrow) { ?>
            <p class="message success"><?php echo $messageBorrow; ?></p>
            <?php } ?>
            <div class="borrow-history-table">
                <table>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Borrowed</th>
                        <th>Returned</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php if ($borrowed && $borrowed->num_rows > 0) { 
                        while ($row = $borrowed->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['borrower_name']; ?></td>
                        <td><?php echo $row['title_book'] . " by " . $row['author_book']; ?></td>
                        <td><?php echo $row['borrow_date']; ?></td>
                        <td><?php echo $row['return_date'] ?: "Not yet"; ?></td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending') { ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
                                <button type="submit" name="return_book">Mark as Returned</button>
                            </form>
                            <?php } else { ?>
                            <p>Done</p>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="6">No borrowed records yet.</td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Merge into develop branch from student 3: Borrow and view catalog book feauture by Rachel Ramos -->
    <div class="book-list">
        <h3>Book List</h3>
        <form method="get" action="librarian.php" class="search-form">
            <input type="text" name="search" placeholder="Search by Title, Author, or ISBN"
                value="<?php echo isset($searchQuery) ? $searchQuery : ''; ?>">
            <button type="submit">Search</button>
            <a href="librarian.php" class="clear-btn">Clear</a>
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
                    <td><?php echo $row['isbn_num']; ?></td>
                    <td><?php echo $row['title_book']; ?></td>
                    <td><?php echo $row['author_book']; ?></td>
                    <td><?php echo $row['book_copy']; ?></td>
                    <td><?php echo $row['avail_book']; ?></td>
                    <td><?php echo $row['date_added']; ?></td>
                    <td>
                        <a href="?edit=<?php echo $row['isbn_num']; ?>"><button type="button">Edit</button></a>
                        <a href="?delete=<?php echo $row['isbn_num']; ?>" onclick="return confirm('Delete this book?')">
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

    <script>
    setTimeout(() => {
        document.querySelectorAll('.message').forEach(msg => {
            msg.style.display = 'none';
        });
    }, 3000);
    </script>
</body>

</html>