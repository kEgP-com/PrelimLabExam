<?php

    $mysql = new mysqli("db", "root", "rootpassword", "library_db");
        if ($mysql->connect_error) {
        echo "<h2> Connection Failed </h2>";
        exit;
        }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initialscale=1.0">
        <title>ONLINE LIBRARY</title>
    </head>
    <body>
        <h2>Hello Student! Search for a Book</h2>
        <form method="GET" action="user.php">
        <input type="text" name="query" placeholder="Enter title" required>
        <input type="text" name="query" placeholder="Enter Author" required>
        <input type="text" name="query" placeholder="Enter Publication Year" required>
        <input type="text" name="query" placeholder="Enter ISBN" required>
        <input type="submit" value="Search">
        </form>
    <br>

<?php
    if (isset($_GET['query'])) {
        $search = $_GET['query'];
        $searchQuery = "
            SELECT * FROM books
            WHERE Title LIKE '%$search%'
            OR Author LIKE '%$search%'
            OR Pub_Year LIKE '%$search%'
            OR ISBN LIKE '%$search%'";

    $data = $mysql->query($searchQuery);
    if ($data->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='8'>";
        echo "<tr>
                <th> TITLE </th>
                <th> AUTHOR </th>
                <th> YEAR </th>
                <th> ISBN </th>
                </tr>";
    while ($row = $data->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Title'] . "</td>";
        echo "<td>" . $row['Author'] . "</td>";
        echo "<td>" . $row['Pub_Year'] . "</td>";
        echo "<td>" . $row['ISBN'] . "</td>";
        echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h3>No books found.</h3>";
        }
    }

    echo "<a href='login.php'>BACK TO LOGIN</a>";
?>
    </body>
</html>