<?php
// Get user information
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
}

$low_stock_count = $conn->query("SELECT COUNT(*) as count FROM inventory_items WHERE quantity <= min_stock_threshold")->fetch_assoc()['count'];
?>
<div id="sidebar" class="fixed z-20 inset-y-0 left-0 w-64 transition duration-300 transform bg-gray-900 text-white lg:translate-x-0 lg:static lg:inset-0 -translate-x-full">
    <div class="flex items-center justify-center mt-8">
        <div class="flex items-center">
            <i class="fas fa-cubes text-2xl"></i>
            <span class="text-white text-2xl mx-2 font-semibold">Inventory</span>
        </div>
    </div>
    <nav class="mt-10">
        <a class="flex items-center mt-4 py-2 px-6 bg-gray-700 bg-opacity-25 text-gray-100" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            <span class="mx-3">Dashboard</span>
        </a>
        <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="items.php">
            <i class="fas fa-box"></i>
            <span class="mx-3">Items</span>
        </a>
        <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="checkout.php">
            <i class="fas fa-shopping-cart"></i>
            <span class="mx-3">Checkout</span>
        </a>
        <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="transactions.php">
            <i class="fas fa-history"></i>
            <span class="mx-3">Transactions</span>
        </a>
        <?php if (isset($user) && $user['role'] == 'admin'): ?>
            <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="employees.php">
                <i class="fas fa-users"></i>
                <span class="mx-3">Employees</span>
            </a>
            <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="suppliers.php">
                <i class="fas fa-truck"></i>
                <span class="mx-3">Suppliers</span>
            </a>
            <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="reports.php">
                <i class="fas fa-chart-bar"></i>
                <span class="mx-3">Reports</span>
            </a>
            <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="admin.php">
                <i class="fas fa-user-shield"></i>
                <span class="mx-3">Admin</span>
            </a>
        <?php endif; ?>
        <a class="flex items-center mt-4 py-2 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="#">
            <i class="fas fa-exclamation-triangle"></i>
            <span class="mx-3">Low Stock</span>
            <span class="mx-auto-auto-auto text-xs bg-red-500 text-white rounded-full px-2 py-1" id="low-stock-badge"><?php echo $low_stock_count; ?></span>
        </a>
    </nav>
    <div class="absolute bottom-0 w-full">
        <a class="flex items-center py-4 px-6 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100" href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span class="mx-3">Logout</span>
        </a>
    </div>
</div>