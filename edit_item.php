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
$sql_item = "SELECT * FROM items WHERE id = $item_id";
$result_item = $conn->query($sql_item);
$item = $result_item->fetch_assoc();

// Get categories and suppliers for dropdowns
$sql_categories = "SELECT * FROM categories";
$result_categories = $conn->query($sql_categories);

$sql_suppliers = "SELECT * FROM suppliers";
$result_suppliers = $conn->query($sql_suppliers);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Inventory Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Edit Item</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $user['name']; ?>!</span>
            </div>
        </header>
        <main>
            <form action="php/api.php?action=edit_item&id=<?php echo $item_id; ?>" method="POST">
                <div class="form-group">
                    <label for="sku">SKU</label>
                    <input type="text" id="sku" name="sku" value="<?php echo $item['sku']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $item['name']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select Category</option>
                        <?php while ($row = $result_categories->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if ($item['category_id'] == $row['id']) echo 'selected'; ?>><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id">
                        <option value="">Select Supplier</option>
                        <?php while ($row = $result_suppliers->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if ($item['supplier_id'] == $row['id']) echo 'selected'; ?>><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="unit">Unit</label>
                    <input type="text" id="unit" name="unit" value="<?php echo $item['unit']; ?>">
                </div>
                <div class="form-group">
                    <label for="cost_price">Cost Price</label>
                    <input type="number" id="cost_price" name="cost_price" step="0.01" min="0" value="<?php echo $item['cost_price']; ?>">
                </div>
                <div class="form-group">
                    <label for="selling_price">Selling Price</label>
                    <input type="number" id="selling_price" name="selling_price" step="0.01" min="0" value="<?php echo $item['selling_price']; ?>">
                </div>
                <div class="form-group">
                    <label for="reorder_level">Reorder Level</label>
                    <input type="number" id="reorder_level" name="reorder_level" min="0" value="<?php echo $item['reorder_level']; ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active" <?php if ($item['status'] == 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($item['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit">Update Item</button>
            </form>
        </main>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
