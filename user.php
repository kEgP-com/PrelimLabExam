<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$mysql = new mysqli("db", "root", "rootpassword", "library_db");
if ($mysql->connect_error) {
    echo "<h2>Connection Failed</h2>";
    exit;
}

// Handle search functionality
$searchResults = [];
$searchPerformed = false;

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search = trim($_GET['query']);
    $searchPerformed = true;
    
    // Use prepared statement to prevent SQL injection
    $stmt = $mysql->prepare("SELECT * FROM books WHERE title_book LIKE ? OR author_book LIKE ? OR isbn_num LIKE ?");
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
</head>
<body>
    <!-- Header Section -->
    <table width="100%" border="0" cellpadding="15" bgcolor="#2c3e50">
        <tr>
            <td align="center" width="15%">
                <font color="white" size="6"><b>📚 Library Management System</b></font>
            </td>
            <td align="right" width="30%">
                <font color="white">
                    Welcome, <b><?php echo $_SESSION['username']; ?></b>!<br>
                    <a href="login.php?logout=true" style="color:white;">Logout</a>
                </font>
            </td>
        </tr>
    </table>

    <!-- Main Content -->
    <table width="100%" border="0" cellpadding="20">
        <tr>
            <td>
                <!-- Search Section -->
                <table width="100%" border="0" cellpadding="15" bgcolor="#ecf0f1">
                    <tr>
                        <td align="center">
                            <h2>🔍 Search for a Book</h2>
                            <p>Enter a book title, author, or ISBN to search our library</p>
                            <form method="GET" action="user.php">
                                <table border="0" align="center">
                                    <tr>
                                        <td>
                                            <input type="text" name="query" size="50" 
                                                   placeholder="Enter book title, author, or ISBN..." 
                                                   value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                                                   style="padding:8px; width:300px;">
                                        </td>
                                        <td>
                                            <input type="submit" value="Search Books" style="padding:8px 20px; width:120px;">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </td>
                    </tr>
                </table>

                <br>

                <!-- Results Section -->
                <table width="100%" border="0" cellpadding="15">
                    <tr>
                        <td>
                            <h2>📋 Search Results</h2>
                            <?php if ($searchPerformed): ?>
                                <p><font color="#27ae60"><b>📊 <?php echo count($searchResults); ?> book(s) found</b></font></p>
                            <?php endif; ?>
                            
                            <?php
                            if ($searchPerformed) {
                                if (count($searchResults) > 0) {
                                    echo "<table width='100%' border='1' cellpadding='12' cellspacing='0' style='border-collapse: collapse;'>";
                                    echo "<tr bgcolor='#34495e'>
                                            <th width='25%'><font color='white'>📖 TITLE</font></th>
                                            <th width='25%'><font color='white'>✍️ AUTHOR</font></th>
                                            <th width='10%'><font color='white'>📚 COPIES</font></th>
                                            <th width='15%'><font color='white'>✅ AVAILABLE</font></th>
                                            <th width='25%'><font color='white'>🏷️ ISBN</font></th>
                                          </tr>";
                                    
                                    $rowCount = 0;
                                    foreach ($searchResults as $row) {
                                        $bgColor = ($rowCount % 2 == 0) ? '#ffffff' : '#f8f9fa';
                                        echo "<tr bgcolor='$bgColor'>";
                                        echo "<td><b>" . htmlspecialchars($row['title_book']) . "</b></td>";
                                        echo "<td>" . htmlspecialchars($row['author_book']) . "</td>";
                                        echo "<td align='center'>" . $row['book_copy'] . "</td>";
                                        echo "<td align='center'><b>" . $row['avail_book'] . "</b></td>";
                                        echo "<td><code>" . $row['isbn_num'] . "</code></td>";
                                        echo "</tr>";
                                        $rowCount++;
                                    }
                                    
                                    echo "</table>";
                                } else {
                                    echo "<table width='100%' border='0' cellpadding='40' bgcolor='#f8f9fa'>
                                            <tr>
                                                <td align='center'>
                                                    <h3>❌ No books found</h3>
                                                    <p>Try a different search term</p>
                                                </td>
                                            </tr>
                                          </table>";
                                }
                            } else {
                                echo "<table width='100%' border='0' cellpadding='40' bgcolor='#f8f9fa'>
                                        <tr>
                                            <td align='center'>
                                                <h3>⭐ Ready to search</h3>
                                                <p>Enter a search term above to find books in our library</p>
                                            </td>
                                        </tr>
                                      </table>";
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <!-- Back Link -->
                <table width="100%" border="0" cellpadding="15">
                    <tr>
                        <td>
                            <a href="login.php">← Back to Login Page</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>