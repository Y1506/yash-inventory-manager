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

// Get inventory valuation
$sql_valuation = "SELECT sku, name, quantity, cost, (quantity * cost) as total_value FROM inventory_items";
$result_valuation = $conn->query($sql_valuation);

// Get low-stock items
$sql_low_stock = "SELECT sku, name, quantity, min_stock_threshold FROM inventory_items WHERE quantity <= min_stock_threshold";
$result_low_stock = $conn->query($sql_low_stock);

// Sales report filtering
$sql_sales = "SELECT * FROM sales WHERE 1=1";

if (!empty($_GET['start_date'])) {
    $sql_sales .= " AND DATE(timestamp) >= '" . $_GET['start_date'] . "'";
}
if (!empty($_GET['end_date'])) {
    $sql_sales .= " AND DATE(timestamp) <= '" . $_GET['end_date'] . "'";
}
if (!empty($_GET['customer_name'])) {
    $sql_sales .= " AND customer_name LIKE '%" . $_GET['customer_name'] . "%'";
}
if (!empty($_GET['payment_method'])) {
    $sql_sales .= " AND payment_method = '" . $_GET['payment_method'] . "'";
}

$sql_sales .= " ORDER BY timestamp DESC";
$result_sales = $conn->query($sql_sales);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory Manager</title>
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
                    <h1 class="text-2xl font-semibold text-gray-700 ml-4">Reports</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700">Welcome, <?php echo $user['name']; ?>!</span>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Inventory Valuation</h2>
                    <h3 class="text-lg font-medium text-gray-600 mb-4">Total Inventory Value: ₹<?php
                        $total_value_query = "SELECT SUM(quantity * cost) as total FROM inventory_items";
                        $total_value_result = $conn->query($total_value_query);
                        $total_value_row = $total_value_result->fetch_assoc();
                        echo number_format($total_value_row['total'], 2);
                    ?></h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result_valuation->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $row['sku']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['quantity']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹<?php echo number_format($row['cost'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹<?php echo number_format($row['total_value'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Low-Stock Report</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Stock Threshold</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suggested Reorder</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result_low_stock->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $row['sku']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['quantity']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['min_stock_threshold']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo ($row['min_stock_threshold'] - $row['quantity']) > 0 ? ($row['min_stock_threshold'] - $row['quantity']) : 0; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Sales Report</h2>
                    <form method="GET" action="reports.php" class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                            <input type="text" id="customer_name" name="customer_name" value="<?php echo isset($_GET['customer_name']) ? $_GET['customer_name'] : ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All</option>
                                <option value="Cash" <?php if (isset($_GET['payment_method']) && $_GET['payment_method'] == 'Cash') echo 'selected'; ?>>Cash</option>
                                <option value="Credit Card" <?php if (isset($_GET['payment_method']) && $_GET['payment_method'] == 'Credit Card') echo 'selected'; ?>>Credit Card</option>
                                <option value="Debit Card" <?php if (isset($_GET['payment_method']) && $_GET['payment_method'] == 'Debit Card') echo 'selected'; ?>>Debit Card</option>
                                <option value="Online Payment" <?php if (isset($_GET['payment_method']) && $_GET['payment_method'] == 'Online Payment') echo 'selected'; ?>>Online Payment</option>
                            </select>
                        </div>
                        <div class="col-span-full flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Filter
                            </button>
                            <a href="php/api.php?action=export_sales_report&<?php echo http_build_query($_GET); ?>" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Export to CSV
                            </a>
                        </div>
                    </form>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Number</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result_sales->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $row['sale_number']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['customer_name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['payment_method']; ?></td>
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
            </main>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>