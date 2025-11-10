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

// Get all items
$sql_items = "SELECT * FROM inventory_items";
$result_items = $conn->query($sql_items);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items - Inventory Manager</title>
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
                    <h1 class="text-2xl font-semibold text-gray-700 ml-4">Manage Items</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700">Welcome, <?php echo $user['name']; ?>!</span>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="flex justify-end mb-4">
                    <a href="add_item.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-plus mr-2"></i> Add New Item
                    </a>
                </div>
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $result_items->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $row['sku']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['category']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['supplier']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['quantity']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹<?php echo number_format($row['price'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php
                                            $status = '';
                                            $color = '';
                                            if ($row['quantity'] > $row['min_stock_threshold']) {
                                                $status = 'In Stock';
                                                $color = 'bg-green-100 text-green-800';
                                            } elseif ($row['quantity'] > 0) {
                                                $status = 'Low Stock';
                                                $color = 'bg-yellow-100 text-yellow-800';
                                            } else {
                                                $status = 'Out of Stock';
                                                $color = 'bg-red-100 text-red-800';
                                            }
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $color; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="edit_item.php?id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <a href="php/api.php?action=delete_item&id=<?php echo $row['id']; ?>" class="text-red-600 hover:text-red-900 ml-4">Delete</a>
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
</body>
</html>