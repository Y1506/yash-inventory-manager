<?php
session_start();

// Include the database connection
require_once 'db_connect.php';

// Get the action from the query string
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        login();
        break;
    case 'add_employee':
        add_employee();
        break;
    case 'edit_employee':
        edit_employee();
        break;
    case 'delete_employee':
        delete_employee();
        break;
    case 'checkout':
        checkout();
        break;
    case 'search_items':
        search_items();
        break;
    case 'add_item':
        add_item();
        break;
    case 'edit_item':
        edit_item();
        break;
    case 'delete_item':
        delete_item();
        break;
    case 'add_supplier':
        add_supplier();
        break;
    case 'edit_supplier':
        edit_supplier();
        break;
    case 'delete_supplier':
        delete_supplier();
        break;
    case 'export_sales_report':
        export_sales_report();
        break;
    case 'get_low_stock_count':
        get_low_stock_count();
        break;
    // Add other cases for other API endpoints here
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function get_low_stock_count() {
    global $conn;

    $sql = "SELECT COUNT(*) as count FROM inventory_items WHERE quantity <= min_stock_threshold";
    $result = $conn->query($sql);
    $count = $result->fetch_assoc()['count'];

    echo json_encode(['count' => $count]);

    $conn->close();
}

function login() {
    global $conn;

    // Get the email and password from the POST request
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the SQL statement
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: ../dashboard.php');
        } else {
            // Incorrect password
            header('Location: ../index.php?error=1');
        }
    } else {
        // User not found
        header('Location: ../index.php?error=1');
    }

    $stmt->close();
    $conn->close();
}

function add_employee() {
    global $conn;

    // Check if the user is an admin
    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    // Get the form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Prepare and execute the SQL statement
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    
    if ($stmt->execute()) {
        header('Location: ../employees.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function edit_employee() {
    global $conn;

    // Check if the user is an admin
    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    // Get the employee ID from the query string
    $employee_id = $_GET['id'];

    // Get the form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Check if a new password was provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $password, $role, $employee_id);
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $role, $employee_id);
    }

    if ($stmt->execute()) {
        header('Location: ../employees.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function delete_employee() {
    global $conn;

    // Check if the user is an admin
    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    // Get the employee ID from the query string
    $employee_id = $_GET['id'];

    // Prepare and execute the SQL statement
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);

    if ($stmt->execute()) {
        header('Location: ../employees.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function checkout() {
    global $conn;

    // Start a transaction
    $conn->begin_transaction();

    try {
        $cart_items = json_decode($_POST['cart_items'], true);
        $customer_name = $_POST['customer_name'];
        $payment_method = $_POST['payment_method'];
        $user_id = $_SESSION['user_id'];
        $total_amount = 0;

        // Calculate total amount
        foreach ($cart_items as $item) {
            $item_id = $item['id'];
            $quantity = $item['quantity'];

            $sql = "SELECT price FROM inventory_items WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $db_item = $result->fetch_assoc();
            $total_amount += $db_item['price'] * $quantity;
        }

        // Create sale record
        $sale_number = 'SALE-' . time();
        $sql = "INSERT INTO sales (sale_number, customer_name, total_amount, payment_method, status) VALUES (?, ?, ?, ?, 'completed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssds", $sale_number, $customer_name, $total_amount, $payment_method);
        $stmt->execute();
        $sale_id = $stmt->insert_id;

        foreach ($cart_items as $item) {
            $item_id = $item['id'];
            $quantity = $item['quantity'];

            // Check stock
            $sql = "SELECT quantity FROM inventory_items WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $db_item = $result->fetch_assoc();

            if ($db_item['quantity'] < $quantity) {
                throw new Exception("Not enough stock for item ID: $item_id");
            }

            // Create sale item record
            $sql = "INSERT INTO sale_items (sale_id, item_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $sale_id, $item_id, $quantity);
            $stmt->execute();

            // Update item quantity
            $new_quantity = $db_item['quantity'] - $quantity;
            $sql = "UPDATE inventory_items SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $new_quantity, $item_id);
            $stmt->execute();
        }

        // Commit the transaction
        $conn->commit();
        header('Location: ../transactions.php');
    } catch (Exception $e) {
        // Rollback the transaction
        $conn->rollback();
        echo "Checkout failed: " . $e->getMessage();
    }

    $conn->close();
}

function search_items() {
    global $conn;

    $search_term = $_GET['term'];

    $sql = "SELECT id, sku, name, price FROM inventory_items WHERE (sku LIKE ? OR name LIKE ?)";
    $stmt = $conn->prepare($sql);
    $search_param = "%" . $search_term . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);

    $stmt->close();
    $conn->close();
}

function add_item() {
    global $conn;

    $sku = $_POST['sku'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $min_stock_threshold = $_POST['min_stock_threshold'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $category = $_POST['category'];
    $supplier = $_POST['supplier'];
    $storage_location = $_POST['storage_location'];

    $sql = "INSERT INTO inventory_items (sku, name, quantity, min_stock_threshold, price, cost, category, supplier, storage_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiddsss", $sku, $name, $quantity, $min_stock_threshold, $price, $cost, $category, $supplier, $storage_location);
    
    if ($stmt->execute()) {
        header('Location: ../items.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function edit_item() {
    global $conn;

    $item_id = $_GET['id'];
    $sku = $_POST['sku'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $min_stock_threshold = $_POST['min_stock_threshold'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $category = $_POST['category'];
    $supplier = $_POST['supplier'];
    $storage_location = $_POST['storage_location'];

    $sql = "UPDATE inventory_items SET sku = ?, name = ?, quantity = ?, min_stock_threshold = ?, price = ?, cost = ?, category = ?, supplier = ?, storage_location = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiddsssi", $sku, $name, $quantity, $min_stock_threshold, $price, $cost, $category, $supplier, $storage_location, $item_id);

    if ($stmt->execute()) {
        header('Location: ../items.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function delete_item() {
    global $conn;

    $item_id = $_GET['id'];

    $sql = "DELETE FROM inventory_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        header('Location: ../items.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function add_supplier() {
    global $conn;

    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    $name = $_POST['name'];
    $contact_person = $_POST['contact_person'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $contact_person, $email, $phone, $address);

    if ($stmt->execute()) {
        header('Location: ../suppliers.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function edit_supplier() {
    global $conn;

    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    $supplier_id = $_GET['id'];
    $name = $_POST['name'];
    $contact_person = $_POST['contact_person'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE suppliers SET name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $contact_person, $email, $phone, $address, $supplier_id);

    if ($stmt->execute()) {
        header('Location: ../suppliers.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function delete_supplier() {
    global $conn;

    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    $supplier_id = $_GET['id'];

    $sql = "DELETE FROM suppliers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $supplier_id);

    if ($stmt->execute()) {
        header('Location: ../suppliers.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function export_sales_report() {
    global $conn;

    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    $sql_sales = "SELECT * FROM sales WHERE 1=1";

    if (!empty($_GET['start_date'])) {
        $sql_sales .= " AND DATE(timestamp) >= '" . $_GET['start_date'] . "'";
    }
    if (!empty($_GET['end_date'])) {
        $sql_sales .= " AND DATE(timestamp) <= '" . $_GET['end_date'] . "'";
    }
    if (!empty($_GET['customer_name'])) {
        $sql_sales .= " AND customer_name LIKE '%" . $_GET['customer_name'] . "%'";
    }
    if (!empty($_GET['payment_method'])) {
        $sql_sales .= " AND payment_method = '" . $_GET['payment_method'] . "'";
    }

    $sql_sales .= " ORDER BY timestamp DESC";
    $result_sales = $conn->query($sql_sales);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sales_report.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Sale Number', 'Customer Name', 'Total Amount', 'Payment Method', 'Status', 'Timestamp'));

    while ($row = $result_sales->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
