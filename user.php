<?php
$conn = new mysqli("db", "root", "rootpassword", "library_db");

if ($conn->connect_error) {
    die("<h2>Connection Failed</h2>");
}

$sql = "SELECT isbn_num, title_book, author_book, book_copy, avail_book, date_added FROM books";
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
        h1 {
            text-align: center;
            margin-top: 20px;
            background: #a4c6f1ff;
            padding: 20px;
        }
        .book-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 20px;
            gap: 20px;
        }
        .book-card {
            background: #a4c6f1ff;
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
        .book-title {
            font-weight: bold;
            margin: 10px 0 5px;
        }
        .book-author {
            color: #555;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px auto;
            border: 2px solid #333;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            background-color: #f9f9f9;
            transition: 0.3s;
        }
        .back-btn:hover {
            background-color: #333;
            color: white;   
        }
        .links {
            text-align: left;
        }
    </style>
</head>
<body>

<h1>Welcome to Library Management System</h1>

<div class="book-container">
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='book-card'>
                <div class='book-title'>" . $row["title_book"] . "</div>
                <div class='book-author'>by " . $row["author_book"] . "</div>
                <a href='librarian.php'>View Details</a>

              </div>";
    }
} else {
    echo "<p style='text-align:center;'>No books found.</p>";
}
$conn->close();
?>
</div>


<div class="links">
    <a href="login.php" class="back-btn">LOG OUT</a>
</div>

</body>
</html>
