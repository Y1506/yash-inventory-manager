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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier - Inventory Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Add New Supplier</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <form action="php/api.php?action=add_supplier" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="contact_person">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"></textarea>
                </div>
                <button type="submit">Add Supplier</button>
            </form>
        </main>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
