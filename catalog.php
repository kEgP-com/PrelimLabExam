<?php
session_start();


$conn = new mysqli("db", "root", "rootpassword", "library_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Borrow a book
if (isset($_POST['borrow_book'])) {
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $borrower_name = $conn->real_escape_string($_POST['borrower_name']);
    $return_date = $conn->real_escape_string($_POST['return_date']);
    $borrow_date = date("Y-m-d");

    // Check availability
    $check = $conn->query("SELECT avail_book FROM books WHERE isbn_num='$isbn'");
    $book = $check->fetch_assoc();

    if ($book && $book['avail_book'] > 0) {
        $conn->query("INSERT INTO borrowed_books 
                      (book_isbn, borrower_name, borrow_date, return_date, status)
                      VALUES ('$isbn', '$borrower_name', '$borrow_date', '$return_date', 'pending')");

        // Decrement availability
        $conn->query("UPDATE books SET avail_book = avail_book - 1 WHERE isbn_num='$isbn'");
    }
}


$searchQuery = "";
if (isset($_GET['search'])) {
    $searchQuery = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM books 
            WHERE isbn_num LIKE '%$searchQuery%' 
               OR title_book LIKE '%$searchQuery%' 
               OR author_book LIKE '%$searchQuery%' 
            ORDER BY date_added DESC";
} else {
    $sql = "SELECT * FROM books ORDER BY date_added DESC";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Book Catalog</title>
    <link rel="stylesheet" href="catalog.css">
</head>

<body>

    <h2>Library Catalog</h2>

    <?php if ($message) echo "<p class='message'>$message</p>"; ?>

    <!-- Controls: Borrower Info + Search -->
    <div class="controls">
        <!-- Borrower Info (outside table) -->
        <form id="borrowerForm">
            <input type="text" id="borrowerName" name="borrower_name" placeholder="Your Name" required>
            <input type="date" id="returnDate" name="return_date" required>
        </form>

        <!-- Search -->
        <form method="get" action="">
            <input type="text" name="search" placeholder="Search books..."
                value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
            <a href="catalog.php" style="text-decoration:none;">
                <button type="button">Clear</button>
            </a>
        </form>
    </div>

    <!-- Book Table -->
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
                <?php if ($row['avail_book'] > 0) { ?>
                <form method="post" name="borrow_form"
                    onsubmit="return confirmBorrow('<?php echo $row['title_book']; ?>')">
                    <input type="hidden" name="isbn" value="<?php echo $row['isbn_num']; ?>">
                    <input type="hidden" name="borrower_name" class="hiddenName">
                    <input type="hidden" name="return_date" class="hiddenDate">
                    <button type="submit" name="borrow_book" class="borrow-btn">Borrow</button>
                </form>

                <?php } else { ?>
                <span style="color:red; font-weight:bold;">Not Available</span>
                <?php } ?>
            </td>
        </tr>
        <?php } } else { ?>
        <tr>
            <td colspan="7">No books available.</td>
        </tr>
        <?php } ?>
    </table>

    <a href="user.php" class="back-btn">Back</a>

    <script>
    function confirmBorrow(bookTitle) {
        return confirm("Are you sure you want to borrow: " + bookTitle + "?");
    }

    document.querySelectorAll("form[name='borrow_form']").forEach(f => {
        f.addEventListener("submit", function(e) {
            let name = document.getElementById("borrowerName").value;
            let date = document.getElementById("returnDate").value;
            if (!name || !date) {
                alert("Please enter your name and return date first.");
                e.preventDefault();
                return false;
            }
            this.querySelector(".hiddenName").value = name;
            this.querySelector(".hiddenDate").value = date;
        });
    });
    </script>


</body>

</html>