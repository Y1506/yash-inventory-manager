<?php
session_start();

// Include the database connection
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <form action="php/api.php?action=login" method="POST" class="login-form">
            <h2>Login</h2>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
