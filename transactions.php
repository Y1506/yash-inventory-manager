<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'php/db_connect.php';

// Get user information
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE id = $user_id";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();

// Get all transactions
$sql_transactions = "SELECT transactions.*, items.name as item_name, users.name as user_name FROM transactions
                     LEFT JOIN items ON transactions.item_id = items.id
                     LEFT JOIN users ON transactions.user_id = users.id
                     ORDER BY transactions.timestamp DESC";
$result_transactions = $conn->query($sql_transactions);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Inventory Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Transactions</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>User</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['timestamp']; ?></td>
                            <td><?php echo $row['type']; ?></td>
                            <td><?php echo $row['item_name']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td>$<?php echo $row['unit_price']; ?></td>
                            <td><?php echo $row['user_name']; ?></td>
                            <td><?php echo $row['customer_name']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
