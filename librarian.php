<?php
session_start();

// Check if user is logged in as librarian
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'librarian') {
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
    <title>Library Management System - Librarian</title>
    <style>
        :root {
            --primary: #4a6fa5;
            --secondary: #6b8cbc;
            --accent: #ff7e5f;
            --light: #f8f9fa;
            --dark: #343a40;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .logo-icon {
            font-size: 2.5rem;
            color: var(--primary);
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .tagline {
            color: var(--secondary);
            font-size: 1.2rem;
            font-style: italic;
        }
        
        .user-info {
            text-align: right;
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        .search-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e1e5ee;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .search-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .search-btn:hover {
            background: #ff6b4a;
        }
        
        .results-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .results-count {
            color: var(--secondary);
            font-size: 1.1rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e1e5ee;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: var(--secondary);
        }
        
        .no-results-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #e1e5ee;
        }
        
        .librarian-actions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .back-link {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            margin-top: 20px;
        }
        
        .back-link:hover {
            background: var(--secondary);
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            th, td {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">📚</div>
                <h1>Library Management System - Librarian</h1>
            </div>
            <p class="tagline">Administrator Dashboard</p>
            <div class="user-info">
                Welcome, Librarian <?php echo $_SESSION['username']; ?>! | 
                <a href="login.php?logout=true">Logout</a>
            </div>
        </header>
        
        <section class="search-section">
            <h2>Search Books (Librarian View)</h2>
            <p>Enter a book title, author, or ISBN to search our library</p>
            
            <form method="GET" action="librarian.php" class="search-form">
                <input type="text" name="query" class="search-input" 
                       placeholder="Search by title, author, or ISBN..." 
                       value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                <button type="submit" class="search-btn">Search</button>
            </form>
        </section>
        
        <section class="results-section">
            <div class="results-header">
                <h2>Search Results</h2>
                <?php if ($searchPerformed): ?>
                    <div class="results-count"><?php echo count($searchResults); ?> book(s) found</div>
                <?php endif; ?>
            </div>
            
            <?php
            if ($searchPerformed) {
                if (count($searchResults) > 0) {
                    echo "<table>";
                    echo "<thead>
                            <tr>
                                <th>TITLE</th>
                                <th>AUTHOR</th>
                                <th>TOTAL COPIES</th>
                                <th>AVAILABLE</th>
                                <th>ISBN</th>
                                <th>DATE ADDED</th>
                            </tr>
                        </thead>";
                    echo "<tbody>";
                    
                    foreach ($searchResults as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title_book']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['author_book']) . "</td>";
                        echo "<td>" . $row['book_copy'] . "</td>";
                        echo "<td>" . $row['avail_book'] . "</td>";
                        echo "<td>" . $row['isbn_num'] . "</td>";
                        echo "<td>" . $row['date_added'] . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</tbody>";
                    echo "</table>";
                } else {
                    echo '<div class="no-results">
                            <div class="no-results-icon">🔍</div>
                            <h3>No books found</h3>
                            <p>Try a different search term</p>
                          </div>';
                }
            } else {
                echo '<div class="no-results">
                        <div class="no-results-icon">📖</div>
                        <h3>Ready to search</h3>
                        <p>Enter a search term above to find books in our library</p>
                      </div>';
            }
            ?>
            
            <div class="librarian-actions">
                <h3>Librarian Management Tools</h3>
                <p>As a librarian, you have access to additional management functions.</p>
            </div>
            
            <a href="login.php" class="back-link">Back to Login</a>
        </section>
    </div>
</body>
</html>