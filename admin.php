<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    // User is not an admin, redirect to the dashboard
    header('Location: dashboard.php');
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
    <title>Admin - Inventory Manager</title>
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
                    <h1 class="text-2xl font-semibold text-gray-700 ml-4">Admin Panel</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700">Welcome, <?php echo $user['name']; ?>!</span>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="employees.php" class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-users text-4xl text-blue-500 mb-4"></i>
                        <h2 class="text-xl font-semibold text-gray-700 mb-2">Manage Employees</h2>
                        <p class="text-gray-600">Add, edit, and remove user accounts.</p>
                    </a>
                    <a href="suppliers.php" class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-truck text-4xl text-green-500 mb-4"></i>
                        <h2 class="text-xl font-semibold text-gray-700 mb-2">Manage Suppliers</h2>
                        <p class="text-gray-600">Add, edit, and remove suppliers.</p>
                    </a>
                    <a href="reports.php" class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-chart-bar text-4xl text-purple-500 mb-4"></i>
                        <h2 class="text-xl font-semibold text-gray-700 mb-2">View Reports</h2>
                        <p class="text-gray-600">Access sales, inventory, and user reports.</p>
                    </a>
                    <a href="#" class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center text-center hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-cogs text-4xl text-gray-500 mb-4"></i>
                        <h2 class="text-xl font-semibold text-gray-700 mb-2">System Settings</h2>
                        <p class="text-gray-600">Configure application settings.</p>
                    </a>
                </div>
            </main>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>