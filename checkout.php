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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Inventory Manager</title>
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
                    <h1 class="text-2xl font-semibold text-gray-700 ml-4">Checkout</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700">Welcome, <?php echo $user['name']; ?>!</span>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="checkout-cart bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Cart</h2>
                        <form action="php/api.php?action=checkout" method="POST">
                            <div id="cart-items" class="mb-4">
                                <!-- Cart items will be added here dynamically -->
                            </div>
                            <div class="form-group mb-4">
                                <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                                <input type="text" id="customer_name" name="customer_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div class="form-group mb-4">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select id="payment_method" name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Online Payment">Online Payment</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Complete Checkout
                            </button>
                        </form>
                    </div>
                    <div class="item-search bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Search Items</h2>
                        <input type="text" id="item-search-input" placeholder="Search by SKU or name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <div id="item-search-results" class="mt-4">
                            <!-- Search results will be displayed here -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>