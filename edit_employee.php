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

// Get the employee ID from the query string
$employee_id = $_GET['id'];

// Get the employee's information
$sql = "SELECT * FROM users WHERE id = $employee_id";
$result = $conn->query($sql);
$employee = $result->fetch_assoc();

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
    <title>Edit Employee - Inventory Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Edit Employee</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <form action="php/api.php?action=edit_employee&id=<?php echo $employee_id; ?>" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $employee['name']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $employee['email']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current password)</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="clerk" <?php if ($employee['role'] == 'clerk') echo 'selected'; ?>>Clerk</option>
                        <option value="viewer" <?php if ($employee['role'] == 'viewer') echo 'selected'; ?>>Viewer</option>
                        <option value="admin" <?php if ($employee['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit">Update Employee</button>
            </form>
        </main>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
