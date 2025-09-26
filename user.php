<?php

    $mysql = new mysqli("db", "root", "rootpassword", "library_db");
        if ($mysql->connect_error) {
        echo "<h2> Connection Failed </h2>";
        exit;
        }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 70%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<h2>List of Books</h2>
<?php
    $sql = "SELECT isbn_num, title_book, author_book, book_copy, avail_book, date_added FROM books"; // change to match your table columns
$result = $conn->query($sql);

// Display in HTML table
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Year</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . $row["title"] . "</td>
                <td>" . $row["author"] . "</td>
                <td>" . $row["year"] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>No books found.</p>";
}

$conn->close();


    echo "<a href='login.php'>BACK TO LOGIN</a>";
?>
    </body>
</html>