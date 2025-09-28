<?php
$conn = new mysqli("db", "root", "rootpassword", "library_db");

if ($conn->connect_error) {
    die("<h2>Connection Failed</h2>");
}

if (isset($_GET['title'])) {
    $title = $conn->real_escape_string($_GET['title']);
    $sql = "SELECT * FROM books WHERE title_book = '$title'";
    $result = $conn->query($sql);
} else {
    die("No book selected.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            background: #a4c6f1;
            padding: 20px;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 70%;
            background: #ffffff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }
        th {
            background: #a4c6f1;
            color: white;
        }
        .back-btn {
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 10px 20px;
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
    </style>
</head>
<body>

<h1>Book Details</h1>

<?php
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr>
            <th>ISBN</th>
            <th>Title</th>
            <th>Author</th>
            <th>Total Copies</th>
            <th>Available Copies</th>
            <th>Date Added</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['isbn_num'] . "</td>";
        echo "<td>" . $row['title_book'] . "</td>";
        echo "<td>" . $row['author_book'] . "</td>";
        echo "<td>" . $row['book_copy'] . "</td>";
        echo "<td>" . $row['avail_book'] . "</td>";
        echo "<td>" . $row['date_added'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>Book not found.</p>";
}
$conn->close();
?>

<a href="browse.php" class="back-btn">Back to Browse</a>

</body>
</html>
