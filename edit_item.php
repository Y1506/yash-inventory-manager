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

// Get item information
$item_id = $_GET['id'];
$sql_item = "SELECT * FROM inventory_items WHERE id = $item_id";
$result_item = $conn->query($sql_item);
$item = $result_item->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Inventory Manager</title>
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
                    <h1 class="text-2xl font-semibold text-gray-700 ml-4">Edit Item</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700">Welcome, <?php echo $user['name']; ?>!</span>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form action="php/api.php?action=edit_item&id=<?php echo $item_id; ?>" method="POST">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                                <input type="text" id="sku" name="sku" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['sku']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="name" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['name']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input type="number" id="quantity" name="quantity" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['quantity']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="min_stock_threshold" class="block text-sm font-medium text-gray-700">Min Stock Threshold</label>
                                <input type="number" id="min_stock_threshold" name="min_stock_threshold" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['min_stock_threshold']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['price']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="cost" class="block text-sm font-medium text-gray-700">Cost</label>
                                <input type="number" id="cost" name="cost" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['cost']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                <input type="text" id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['category']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                                <input type="text" id="supplier" name="supplier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['supplier']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="storage_location" class="block text-sm font-medium text-gray-700">Storage Location</label>
                                <input type="text" id="storage_location" name="storage_location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="<?php echo $item['storage_location']; ?>">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>