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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Checkout</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <div class="checkout-cart">
                <h2>Cart</h2>
                <form action="php/api.php?action=checkout" method="POST">
                    <div id="cart-items">
                        <!-- Cart items will be added here dynamically -->
                    </div>
                    <div class="form-group">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_contact">Customer Contact</label>
                        <input type="text" id="customer_contact" name="customer_contact">
                    </div>
                    <button type="submit">Complete Checkout</button>
                </form>
            </div>
            <div class="item-search">
                <h2>Search Items</h2>
                <input type="text" id="item-search-input" placeholder="Search by SKU or name">
                <div id="item-search-results">
                    <!-- Search results will be displayed here -->
                </div>
            </div>
        </main>
    </div>
    <script src="js/script.js"></script>
    <script>
        // Add your checkout specific JavaScript here
    </script>
</body>
</html>
