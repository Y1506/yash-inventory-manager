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

// Get all sales
$sql_sales = "SELECT * FROM sales ORDER BY timestamp DESC";
$result_sales = $conn->query($sql_sales);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Inventory Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .details-row {
            display: none;
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
                    <h1 class="text-2xl font-semibold text-gray-700 ml-4">Transactions</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700">Welcome, <?php echo $user['name']; ?>!</span>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $result_sales->fetch_assoc()): ?>
                                    <tr class="cursor-pointer" onclick="toggleDetails(<?php echo $row['id']; ?>)">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <i class="fas fa-chevron-down"></i>
                                        </td>
                                    </tr>
                                    <tr class="details-row" id="details-<?php echo $row['id']; ?>">
                                        <td colspan="7" class="p-4">
                                            <h4 class="text-lg font-semibold mb-2">Sale Items</h4>
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <?php
                                                    $sale_id = $row['id'];
                                                    $sql_sale_items = "SELECT si.quantity, i.name FROM sale_items si JOIN inventory_items i ON si.item_id = i.id WHERE si.sale_id = $sale_id";
                                                    $result_sale_items = $conn->query($sql_sale_items);
                                                    while ($item_row = $result_sale_items->fetch_assoc()):
                                                    ?>
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item_row['name']; ?></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item_row['quantity']; ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </td>
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
    <script>
        function toggleDetails(saleId) {
            const detailsRow = document.getElementById('details-' + saleId);
            if (detailsRow) {
                detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
            }
        }
    </script>
</body>
</html>