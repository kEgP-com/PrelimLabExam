<!DOCTYPE html>
<html>
<head>
    <title>LIBRARIAN CATALOG</title>
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
        .catalog_tbl {
            display: flex;
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            min-height: 50vh; 
        }   
        table {
            border-collapse: collapse;
            background: white;
        }
        table th, table td {
            border: 5px solid #76ccf3ff;
            padding: 20px;
            text-align: center;
        }
        .box-link {
            display: inline-block;
            padding: 10px;
            margin: 20px;
            border: 2px solid #333;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            background-color: #f9f9f9;
            transition: 0.3s;
        }
        .box-link:hover {
            background-color: #333;
            color: white;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .status-borrowed {
            color: black;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>WELCOME TO LIBRARY MANAGEMENT SYSTEM - LIBRARIAN</h1>

    <div class="catalog_tbl">
        <?php   
        $mysql = new mysqli("db", "root", "rootpassword", "library_db");
        if($mysql->connect_error) {
            echo "<h3> CONNECTION FAILED</h3>";
        }
        
        $selectQuery = "SELECT * FROM books;";
        $data = $mysql->query($selectQuery);

        echo "<table>";
        echo "<tr>";
        echo "<th> ISBN </th>";
        echo "<th> TITLE </th>";
        echo "<th> AUTHOR </th>";
        echo "<th> TOTAL COPIES </th>";
        echo "<th> AVAILABLE COPIES </th>";
        echo "<th> DATE ADDED </th>";
        echo "<th> STATUS </th>";
        echo "</tr>";
        
        while($result= $data->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $result['isbn_num'] . "</td>";
            echo "<td>" . $result['title_book'] . "</td>";
            echo "<td>" . $result['author_book'] . "</td>";
            echo "<td>" . $result['book_copy'] . "</td>";
            echo "<td>" . $result['avail_book'] . "</td>";
            echo "<td>" . $result['date_added'] . "</td>";

            
            if ($result['avail_book'] < $result['book_copy']) {
                echo "<td class='status-borrowed'>Borrowed</td>";
            } else {
                echo "<td></td>"; 
            }

            echo "</tr>";
        }
        echo "</table>";
        ?>
    </div>

    <div class="links">
        <a href="login.php" class="box-link">LOG OUT</a>
        <a href="user.php" class="box-link">EDIT</a>
    </div>

</body>
</html>
