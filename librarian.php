<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'librarian') {
    header("Location: login.php");
    exit;
}
$mysql = new mysqli("db", "root", "rootpassword", "library_db");
if ($mysql->connect_error) {
    echo "Connection Failed";
    exit;
}
$searchResults = [];
$searchPerformed = false;

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search = trim($_GET['query']);
    $searchPerformed = true;
    
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>📚 Library Management System</h1>
                <div class="welcome-message">Welcome, Librarian <strong><?php echo $_SESSION['username']; ?></strong></div>
            </div>
            <div class="role-badge">Librarian Admin</div>
        </div>
        <!-- Search Section -->
        <div class="card search-section">
            <h2>🔍 Search Books</h2>
            <p>Manage library collection by searching books</p>
            
            <form method="GET" action="librarian.php" class="search-form">
                <input type="text" name="query" class="search-input" 
                       placeholder="Enter book title, author name, or ISBN..." 
                       value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                <button type="submit" class="search-button">
                    <span>Search Books</span>
                </button>
            </form>
        </div>
        <!-- Results Section -->
        <div class="card results-section">
            <h2>📋 Search Results</h2>
            
            <?php if ($searchPerformed): ?>
                <div class="results-count">
                    📊 Found <?php echo count($searchResults); ?> book(s)
                </div>
            <?php endif; ?>
            
            <?php if ($searchPerformed): ?>
                <?php if (count($searchResults) > 0): ?>
                    <div class="table-container">
                        <table class="books-table" border="1">
                            <thead>
                                <tr>
                                    <th>📖 Title</th>
                                    <th>✍️ Author</th>
                                    <th>📚 Copies</th>
                                    <th>✅ Available</th>
                                    <th>🏷️ ISBN</th>
                                    <th>📅 Date Added</th>
                                    <th>⚡ Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($searchResults as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['title_book']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['author_book']); ?></td>
                                    <td style="text-align: center;"><?php echo $row['book_copy']; ?></td>
                                    <td style="text-align: center;"><strong><?php echo $row['avail_book']; ?></strong></td>
                                    <td><code><?php echo $row['isbn_num']; ?></code></td>
                                    <td><?php echo $row['date_added']; ?></td>
                                    <td class="actions">
                                        <button class="action-btn edit-btn">Edit</button>
                                        <button class="action-btn remove-btn">Remove</button>
                                        <button class="action-btn return-btn">Return</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="message">
                        <h3>📭 No Books Found</h3>
                        <p>Try adjusting your search terms or add new books to the collection</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="message">
                    <h3>⭐ Management Dashboard</h3>
                    <p>Search above to manage books or use the tools below</p>
                </div>
            <?php endif; ?>
        </div>
        <!-- Back to Login -->
        <div class="back-link">
            <a href="login.php">← Back to Login Page</a>
        </div>
    </div>
</body>
</html>