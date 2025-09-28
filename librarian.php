
<?php



$browseHistory = $conn->query("SELECT h.id, h.username, h.browse_date, 
                                      bk.title_book, bk.author_book 
                               FROM browse_history h
                               JOIN books bk ON h.book_isbn = bk.isbn_num
                               ORDER BY h.browse_date DESC");
?>

<div class="borrow-history">
            <h3>Borrowed & Returned History</h3>
            <?php if ($messageBorrow) { ?>
            <p class="message success"><?php echo $messageBorrow; ?></p>
            <?php } ?>
            <div class="borrow-history-table">
                <table>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Borrowed</th>
                        <th>Returned</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php if ($borrowed && $borrowed->num_rows > 0) { 
                        while ($row = $borrowed->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['borrower_name']; ?></td>
                        <td><?php echo $row['title_book'] . " by " . $row['author_book']; ?></td>
                        <td><?php echo $row['borrow_date']; ?></td>
                        <td><?php echo $row['return_date'] ?: "Not yet"; ?></td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending') { ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
                                <button type="submit" name="return_book">Mark as Returned</button>
                            </form>
                            <?php } else { ?>
                            <p>Done</p>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="6">No borrowed records yet.</td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<div class="browse-history">
    <h3>Browse History</h3>
    <div class="browse-history-table">
        <table>
            <tr>
                <th>User</th>
                <th>Book</th>
                <th>Date Browsed</th>
            </tr>
            <?php if ($browseHistory && $browseHistory->num_rows > 0) { 
                while ($row = $browseHistory->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['title_book'] . " by " . $row['author_book']; ?></td>
                <td><?php echo $row['browse_date']; ?></td>
            </tr>
            <?php } } else { ?>
            <tr>
                <td colspan="3">No browsing history yet.</td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

