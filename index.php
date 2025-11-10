<?php
session_start();

// First, check if the database exists without selecting it
@include 'php/config.php';

// Turn off exceptions for this check
mysqli_report(MYSQLI_REPORT_OFF);
$conn_check = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if ($conn_check->connect_error) {
    die("Initial connection failed: " . $conn_check->connect_error);
}

// Try to select the database
$db_selected = $conn_check->select_db(DB_NAME);
$conn_check->close();

// Turn exceptions back on to the default
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


if (!$db_selected) {
    // Database doesn't exist, redirect to setup
    header('Location: php/setup.php');
    exit;
}


// Now that we know the database exists, we can connect normally.
require_once 'php/db_connect.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, show the dashboard
    header('Location: dashboard.php');
    exit;
}

// If the user is not logged in, show the login page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md">
        <form action="php/api.php?action=login" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">Login</h2>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Sign In
                </button>
            </div>
            <div class="mt-4 text-center text-sm text-gray-600">
                <p>For initial setup, please import <code>database.sql</code>.</p>
                <p>Default admin credentials:</p>
                <p>Email: <strong>admin@example.com</strong><br>Password: <strong>password</strong></p>
            </div>
        </form>
    </div>
</body>
</html>