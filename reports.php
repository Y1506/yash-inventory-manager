<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once 'php/db_connect.php';

// Get user information
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE id = $user_id";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();

// Get low-stock items
$sql_low_stock = "SELECT * FROM items WHERE current_quantity <= reorder_level AND status = 'active'";
$result_low_stock = $conn->query($sql_low_stock);

// Get inventory valuation
$sql_valuation = "SELECT sku, name, current_quantity, cost_price, (current_quantity * cost_price) as total_value FROM items WHERE status = 'active'";
$result_valuation = $conn->query($sql_valuation);
$total_inventory_value = 0;

// Movement report filtering
$sql_movement = "SELECT transactions.*, items.name as item_name, users.name as user_name FROM transactions
                 LEFT JOIN items ON transactions.item_id = items.id
                 LEFT JOIN users ON transactions.user_id = users.id WHERE 1=1";

if (!empty($_GET['start_date'])) {
    $sql_movement .= " AND DATE(transactions.timestamp) >= '" . $_GET['start_date'] . "'";
}
if (!empty($_GET['end_date'])) {
    $sql_movement .= " AND DATE(transactions.timestamp) <= '" . $_GET['end_date'] . "'";
}
if (!empty($_GET['item_id'])) {
    $sql_movement .= " AND transactions.item_id = " . $_GET['item_id'];
}
if (!empty($_GET['user_id'])) {
    $sql_movement .= " AND transactions.user_id = " . $_GET['user_id'];
}
if (!empty($_GET['type'])) {
    $sql_movement .= " AND transactions.type = '" . $_GET['type'] . "'";
}

$sql_movement .= " ORDER BY transactions.timestamp DESC";
$result_movement = $conn->query($sql_movement);

// Get items and users for filter dropdowns
$sql_items_filter = "SELECT id, name FROM items";
$result_items_filter = $conn->query($sql_items_filter);

$sql_users_filter = "SELECT id, name FROM users";
$result_users_filter = $conn->query($sql_users_filter);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Reports</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <div class="report-section">
                <h2>Inventory Valuation</h2>
                <h3>Total Inventory Value: $<?php
                    $total_value_query = "SELECT SUM(current_quantity * cost_price) as total FROM items WHERE status = 'active'";
                    $total_value_result = $conn->query($total_value_query);
                    $total_value_row = $total_value_result->fetch_assoc();
                    echo number_format($total_value_row['total'], 2);
                ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Current Quantity</th>
                            <th>Cost Price</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_valuation->fetch_assoc()):
                            $total_inventory_value += $row['total_value'];
                        ?>
                            <tr>
                                <td><?php echo $row['sku']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['current_quantity']; ?></td>
                                <td>$<?php echo $row['cost_price']; ?></td>
                                <td>$<?php echo number_format($row['total_value'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="report-section">
                <h2>Movement Report</h2>
                <form method="GET" action="reports.php">
                    <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                    <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                    <select name="item_id">
                        <option value="">Select Item</option>
                        <?php while ($row = $result_items_filter->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if (isset($_GET['item_id']) && $_GET['item_id'] == $row['id']) echo 'selected'; ?>><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="user_id">
                        <option value="">Select User</option>
                        <?php while ($row = $result_users_filter->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if (isset($_GET['user_id']) && $_GET['user_id'] == $row['id']) echo 'selected'; ?>><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="type">
                        <option value="">Select Type</option>
                        <option value="check-in" <?php if (isset($_GET['type']) && $_GET['type'] == 'check-in') echo 'selected'; ?>>Check-in</option>
                        <option value="checkout" <?php if (isset($_GET['type']) && $_GET['type'] == 'checkout') echo 'selected'; ?>>Checkout</option>
                        <option value="adjustment" <?php if (isset($_GET['type']) && $_GET['type'] == 'adjustment') echo 'selected'; ?>>Adjustment</option>
                        <option value="return" <?php if (isset($_GET['type']) && $_GET['type'] == 'return') echo 'selected'; ?>>Return</option>
                    </select>
                    <button type="submit">Filter</button>
                    <a href="php/api.php?action=export_movement_report">Export to CSV</a>
                </form>
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
                        <?php while ($row = $result_movement->fetch_assoc()): ?>
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
            </div>
            <div class="report-section">
                <h2>Low-Stock Report</h2>
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Current Quantity</th>
                            <th>Reorder Level</th>
                            <th>Suggested Reorder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_low_stock->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['sku']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['current_quantity']; ?></td>
                                <td><?php echo $row['reorder_level']; ?></td>
                                <td><?php echo $row['reorder_level'] - $row['current_quantity']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
