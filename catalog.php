```php
<?php
// Database connection
$conn = new mysqli("db", "root", "rootpassword", "library_db");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// If may isbn sa URL → filter specific book, else show all
if (isset($_GET['isbn'])) {
    $isbn = $conn->real_escape_string($_GET['isbn']);
    $sql = "SELECT * FROM books WHERE isbn_num = '$isbn'";
} else {
    $sql = "SELECT * FROM books";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Catalog</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fa;
            text-align: center;
        }
        .book-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin: 20px;
            gap: 20px;
        }
        .book-card {
            background-color: #a8c7f0;
            padding: 15px;
            border-radius: 12px;
            width: 200px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .book-card h3 {
            margin: 0;
        }
        .book-card a {
            display: inline-block;
            margin-top: 10px;
            color: purple;
            text-decoration: none;
            font-weight: bold;
        }
        .book-card a:hover {
            text-decoration: underline;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            border: 2px solid skyblue;
        }
        th, td {
            border: 1px solid skyblue;
            padding: 10px;
            text-align: center;
        }
        th {
            font-weight: bold;
        }
        .back-btn {
            display: inline-block;
            margin: 20px;
            padding: 8px 15px;
            border: 2px solid black;
            text-decoration: none;
            font-weight: bold;
            color: black;
            border-radius: 6px;
        }
        .back-btn:hover {
            background-color: lightgray;
        }
    </style>
</head>
<body>

<h2>Library Catalog</h2>

<?php if (!isset($_GET['isbn'])) { ?>
    <!-- Show book cards -->
    <div class="book-container">
        <div class="book-card">
            <h3>The Great Gatsby</h3>
            <p>by F. Scott Fitzgerald</p>
            <a href="?isbn=B001">View Details</a>
        </div>
        <div class="book-card">
            <h3>1984</h3>
            <p>by George Orwell</p>
            <a href="?isbn=B002">View Details</a>
        </div>
        <div class="book-card">
            <h3>To Kill a Mockingbird</h3>
            <p>by Harper Lee</p>
            <a href="?isbn=B003">View Details</a>
        </div>
        <div class="book-card">
            <h3>Pride and Prejudice</h3>
            <p>by Jane Austen</p>
            <a href="?isbn=B004">View Details</a>
        </div>
        <div class="book-card">
            <h3>Salamisim</h3>
            <p>by Binibining Mia</p>
            <a href="?isbn=B005">View Details</a>
        </div>
    </div>
<?php } ?>

<!-- Show table (filtered or all) -->
<table>
    <tr>
        <th>ISBN</th>
        <th>TITLE</th>
        <th>AUTHOR</th>
        <th>TOTAL COPIES</th>
        <th>AVAILABLE COPIES</th>
        <th>DATE ADDED</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['isbn_num'] ?></td>
            <td><?= $row['title_book'] ?></td>
            <td><?= $row['author_book'] ?></td>
            <td><?= $row['book_copy'] ?></td>
            <td><?= $row['available_copies'] ?></td>
            <td><?= $row['date_added'] ?></td>
        </tr>
    <?php } ?>
</table>

<?php if (isset($_GET['isbn'])) { ?>
    <a href="catalog.php" class="back-btn">Back to Catalog</a>
<?php } ?>

</body>
</html>
```
