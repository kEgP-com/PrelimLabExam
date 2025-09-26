<?php
// Database connection
$conn = new mysqli("db", "root", "rootpassword", "library_db");

// Check connection
if ($conn->connect_error) {
    die("<h2>Connection Failed</h2>");
}

// Query all books
$sql = "SELECT isbn_num, title_book, author_book, book_copy, avail_book, date_added 
        FROM books";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
        .book-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 20px;
            gap: 20px;
        }
        .book-card {
            background: #fff;
            width: 200px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        .book-card:hover {
            transform: scale(1.05);
        }
        .book-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
        }
        .book-title {
            font-weight: bold;
            margin: 10px 0 5px;
        }
        .book-author {
            color: #555;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .book-info {
            font-size: 13px;
            color: #666;
        }
        .back-link {
            display: block;
            text-align: center;
            margin: 20px;
        }
    </style>
</head>
<body>

<h2>Our Book Collection</h2>

<div class="book-container">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $image = "default.jpg";

        if ($row["title_book"]== "The Great Gatsby") {
            $image = "images/thegreategatsby.jpg";
        } elseif ($row["title_book"]== "1984") {
            $image = "images/1984.jpg";
        }elseif ($row["title_book"]== "To Kill a Mockingbird") {
            $image = "images/tokillamockingbird.jpg";
        }elseif ($row["title_book"]== "Pride and Prejudice") {
            $image = "images/prideandprejudice.jpg";
        }elseif ($row["title_book"]== "Salamisim") {
            $image = "images/salamisim.jpg";
        }
        echo "<div class='book-card'>
                <img src=$image alt='Book Cover'>
                <div class='book-title'>" . $row["title_book"] . "</div>
                <div class='book-author'>by " . $row["author_book"] . "</div>
                <div class='book-info'>Copies: " . $row["book_copy"] . "</div>
                <div class='book-info'>Available: " . $row["avail_book"] . "</div>
                <div class='book-info'>Added: " . $row["date_added"] . "</div>
              </div>";
    }
} else {
    echo "<p style='text-align:center;'>No books found.</p>";
}
$conn->close();
?>
</div>

<div class="back-link">
    <a href="login.php">BACK TO LOGIN</a>
</div>

</body>
</html>
