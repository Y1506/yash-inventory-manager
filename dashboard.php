<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to the login page
    header('Location: index.php');
    exit;
}

// Include the database connection
require_once 'php/db_connect.php';

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Get stats for dashboard
$items_count = $conn->query("SELECT COUNT(*) as count FROM inventory_items")->fetch_assoc()['count'];
$low_stock_count = $conn->query("SELECT COUNT(*) as count FROM inventory_items WHERE quantity <= min_stock_threshold")->fetch_assoc()['count'];
$sales_count = $conn->query("SELECT COUNT(*) as count FROM sales")->fetch_assoc()['count'];
$total_sales_amount = $conn->query("SELECT SUM(total_amount) as total FROM sales")->fetch_assoc()['total'];

// Get recent sales
$sql_recent_sales = "SELECT * FROM sales ORDER BY timestamp DESC LIMIT 5";
$result_recent_sales = $conn->query($sql_recent_sales);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen bg-gray-200">
        <?php include 'templates/sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-6 bg-white border-b-2 border-gray-200">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6H20M4 12H20M4 18H11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                    <h1 class="text-2xl font-semibold text-gray-700 ml-4">Dashboard</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700">Welcome, <?php echo $user['name']; ?>!</span>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="bg-blue-500 rounded-full p-3">
                                <i class="fas fa-box text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-600">Total Items</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $items_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="bg-yellow-500 rounded-full p-3">
                                <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-600">Low Stock Items</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $low_stock_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="bg-green-500 rounded-full p-3">
                                <i class="fas fa-chart-line text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-600">Total Sales</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $sales_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="bg-red-500 rounded-full p-3">
                                <i class="fas fa-dollar-sign text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-600">Total Sales Amount</p>
                                <p class="text-2xl font-bold text-gray-800">₹<?php echo number_format($total_sales_amount, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-700">Recent Sales</h2>
                    </div>
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $result_recent_sales->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $row['sale_number']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['customer_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['timestamp']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>