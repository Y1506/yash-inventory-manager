<?php
// Get user information
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
}
?>
<div class="sidebar">
    <h2>Inventory Manager</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="items.php">Items</a></li>
        <li><a href="checkout.php">Checkout</a></li>
        <li><a href="transactions.php">Transactions</a></li>
        <?php if (isset($user) && $user['role'] == 'admin'): ?>
            <li><a href="employees.php">Employees</a></li>
            <li><a href="suppliers.php">Suppliers</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="admin.php">Admin</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
