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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Admin</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <div class="admin-links">
                <a href="employees.php" class="admin-link">
                    <h2>Manage Employees</h2>
                    <p>Add, edit, and remove user accounts.</p>
                </a>
                <a href="suppliers.php" class="admin-link">
                    <h2>Manage Suppliers</h2>
                    <p>Add, edit, and remove suppliers.</p>
                </a>
                <a href="reports.php" class="admin-link">
                    <h2>View Reports</h2>
                    <p>Access sales, inventory, and user reports.</p>
                </a>
                <a href="#" class="admin-link">
                    <h2>System Settings</h2>
                    <p>Configure application settings.</p>
                </a>
            </div>
        </main>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
