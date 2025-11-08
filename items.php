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
$sql_items = "SELECT items.*, categories.name as category_name, suppliers.name as supplier_name FROM items 
              LEFT JOIN categories ON items.category_id = categories.id
              LEFT JOIN suppliers ON items.supplier_id = suppliers.id";
$result_items = $conn->query($sql_items);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items - Inventory Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Manage Items</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <div class="item-filters">
                <input type="text" placeholder="Search by name, SKU...">
                <select>
                    <option value="">All Categories</option>
                </select>
                <select>
                    <option value="">All Suppliers</option>
                </select>
                <button>Filter</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['sku']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['category_name']; ?></td>
                            <td><?php echo $row['supplier_name']; ?></td>
                            <td><?php echo $row['current_quantity']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <a href="edit_item.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a href="php/api.php?action=archive_item&id=<?php echo $row['id']; ?>">Archive</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="add_item.php">Add New Item</a>
        </main>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
