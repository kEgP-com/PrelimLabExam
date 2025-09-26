<?php   
    $mysql = new mysqli("db", "root", "rootpassword", "library_db");
    if($mysql->connect_error) {
        echo "<h3> CONNECTION FAILED</h3>";
    }


    $selectQuery = "SELECT * FROM books;";
    $data = $mysql->query($selectQuery);
    echo "<table border='1' cellpadding='8' cellspacing='8'>";
    echo "<tr>";
    echo "<th> ISBN </th>";
    echo "<th> TITLE </th>";
    echo "<th> AUTHOR </th>";
    echo "<th> TOTAL COPIES </th>";
    echo "<th> AVAILABLE COPIES </th>";
    echo "<th> DATE ADDED </th>";
    
    echo "</tr>";
    while($result= $data->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $result['isbn_num'] . "</td>";
        echo "<td>" . $result['title_book'] . "</td>";
        echo "<td>" . $result['author_book'] . "</td>";
        echo "<td>" . $result['book_copy'] . "</td>";
        echo "<td>" . $result['avail_book'] . "</td>";
        echo "<td>" . $result['date_added'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<a href='login.php'>BACK TO LOGIN</a>";
?>