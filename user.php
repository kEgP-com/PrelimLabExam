<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// =================================================================
// DATABASE CONNECTION
// =================================================================
$db_host = "db";
$db_user = "root";
$db_pass = "rootpassword";
$db_name = "library_db";
$mysql = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysql->connect_error) { die("DB Error: " . $mysql->connect_error); }

// Config
$book_table_primary_key = 'isbn_num';
$borrowed_table_foreign_key = 'book_isbn';

// Get user_id
$stmt = $mysql->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user_id = $stmt->get_result()->fetch_assoc()['id'];
$stmt->close();

$action_message = '';

// Handle borrow
if (isset($_POST['borrow'])) {
    $book_id = $_POST['book_key_to_borrow'];
    $mysql->begin_transaction();
    try {
        $chk = $mysql->prepare("SELECT avail_book FROM books WHERE {$book_table_primary_key}=? FOR UPDATE");
        $chk->bind_param("s", $book_id);
        $chk->execute();
        $res = $chk->get_result();
        if ($res->num_rows > 0) {
            $avail = $res->fetch_assoc()['avail_book'];
            if ($avail > 0) {
                $mysql->query("UPDATE books SET avail_book = avail_book-1 WHERE {$book_table_primary_key}='$book_id'");
                $due = date('Y-m-d H:i:s', strtotime('+14 days'));
                $ins = $mysql->prepare("INSERT INTO borrowed_books (user_id, {$borrowed_table_foreign_key}, borrow_date, due_date) VALUES (?, ?, NOW(), ?)");
                $ins->bind_param("iss", $user_id, $book_id, $due);
                $ins->execute();
                $ins->close();
                $mysql->commit();
                $action_message = "<font color=green>✅ Borrowed successfully</font>";
            } else { $mysql->rollback(); $action_message="<font color=red>❌ Not available</font>"; }
        }
    } catch (Exception $e) { $mysql->rollback(); }
}

// Handle return
if (isset($_POST['return'])) {
    $bid = $_POST['borrow_id']; $book_id = $_POST['book_key_to_return'];
    $mysql->begin_transaction();
    try {
        $mysql->query("UPDATE borrowed_books SET return_date=NOW() WHERE id=$bid AND user_id=$user_id");
        $mysql->query("UPDATE books SET avail_book=avail_book+1 WHERE {$book_table_primary_key}='$book_id'");
        $mysql->commit();
        $action_message="<font color=green>✅ Returned successfully</font>";
    } catch (Exception $e) { $mysql->rollback(); }
}

// Borrowed list
$borrowed_list = [];
$q = $mysql->prepare("SELECT bb.id,b.title_book,bb.borrow_date,bb.due_date,bb.$borrowed_table_foreign_key 
                      FROM borrowed_books bb JOIN books b 
                      ON bb.$borrowed_table_foreign_key=b.$book_table_primary_key 
                      WHERE bb.user_id=? AND bb.return_date IS NULL ORDER BY bb.due_date ASC");
$q->bind_param("i",$user_id); $q->execute();
$borrowed_list=$q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close();
$pending_count=count($borrowed_list);

// History
$history=[];
$q=$mysql->prepare("SELECT b.title_book,bb.borrow_date,bb.return_date 
                    FROM borrowed_books bb JOIN books b 
                    ON bb.$borrowed_table_foreign_key=b.$book_table_primary_key 
                    WHERE bb.user_id=? AND bb.return_date IS NOT NULL ORDER BY bb.return_date DESC");
$q->bind_param("i",$user_id); $q->execute();
$history=$q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"><title>My Books</title>
<style>
body {font-family:Arial;background:#f4f6f8;padding:20px;}
.scroll-box{max-height:300px;overflow-y:auto;border:1px solid #ccc;background:#fff;}
.scroll-box th{background:#34495e;color:#fff;position:sticky;top:0;}
.notification{background:#ffefc6;border:1px solid #f0c36d;color:#856404;padding:15px;margin-bottom:20px;border-radius:8px;font-weight:bold;}
</style>
</head>
<body>
<h2>📖 My Borrowed Books</h2>
<?php if($pending_count>0): ?>
<div class="notification">🔔 You have <b><?php echo $pending_count; ?></b> pending book(s).</div>
<div class="scroll-box">
<table width="100%" border="1" cellpadding="8">
<tr><th>Title</th><th>Borrowed</th><th>Due</th><th>Action</th></tr>
<?php foreach($borrowed_list as $b): ?>
<tr>
<td><?php echo htmlspecialchars($b['title_book']); ?></td>
<td><?php echo date("M j, Y",strtotime($b['borrow_date'])); ?></td>
<td><?php echo date("M j, Y",strtotime($b['due_date'])); ?></td>
<td>
<form method="POST">
<input type="hidden" name="borrow_id" value="<?php echo $b['id']; ?>">
<input type="hidden" name="book_key_to_return" value="<?php echo $b[$borrowed_table_foreign_key]; ?>">
<input type="submit" name="return" value="Return">
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php else: ?><p>No borrowed books.</p><?php endif; ?>

<h2>📜 Borrow History</h2>
<?php if(count($history)>0): ?>
<div class="scroll-box">
<table width="100%" border="1" cellpadding="8">
<tr><th>Title</th><th>Borrowed</th><th>Returned</th></tr>
<?php foreach($history as $h): ?>
<tr>
<td><?php echo htmlspecialchars($h['title_book']); ?></td>
<td><?php echo date("M j, Y",strtotime($h['borrow_date'])); ?></td>
<td><?php echo date("M j, Y",strtotime($h['return_date'])); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php else: ?><p>No history.</p><?php endif; ?>

<br><a href="catalog.php">⬅ Back to Catalog</a>
</body>
</html>
