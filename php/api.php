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
    case 'archive_item':
        archive_item();
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
    case 'export_movement_report':
        export_movement_report();
        break;
    // Add other cases for other API endpoints here
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
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
        $customer_contact = $_POST['customer_contact'];
        $user_id = $_SESSION['user_id'];

        foreach ($cart_items as $item) {
            $item_id = $item['id'];
            $quantity = $item['quantity'];

            // Check stock
            $sql = "SELECT current_quantity, selling_price FROM items WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $db_item = $result->fetch_assoc();

            if ($db_item['current_quantity'] < $quantity) {
                throw new Exception("Not enough stock for item ID: $item_id");
            }

            // Create transaction record
            $unit_price = $db_item['selling_price'];
            $sql = "INSERT INTO transactions (type, item_id, quantity, unit_price, user_id, customer_name, customer_contact) VALUES ('checkout', ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiidss", $item_id, $quantity, $unit_price, $user_id, $customer_name, $customer_contact);
            $stmt->execute();

            // Update item quantity
            $new_quantity = $db_item['current_quantity'] - $quantity;
            $sql = "UPDATE items SET current_quantity = ? WHERE id = ?";
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

    $sql = "SELECT id, sku, name, selling_price FROM items WHERE (sku LIKE ? OR name LIKE ?) AND status = 'active'";
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
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $unit = $_POST['unit'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $reorder_level = $_POST['reorder_level'];
    $current_quantity = $_POST['current_quantity'];

    $sql = "INSERT INTO items (sku, name, category_id, supplier_id, unit, cost_price, selling_price, reorder_level, current_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiisddii", $sku, $name, $category_id, $supplier_id, $unit, $cost_price, $selling_price, $reorder_level, $current_quantity);
    
    if ($stmt->execute()) {
        if ($current_quantity > 0) {
            $item_id = $stmt->insert_id;
            $user_id = $_SESSION['user_id'];
            $sql_trans = "INSERT INTO transactions (type, item_id, quantity, unit_price, user_id, notes) VALUES ('check-in', ?, ?, ?, ?, 'Initial stock')";
            $stmt_trans = $conn->prepare($sql_trans);
            $stmt_trans->bind_param("iidi", $item_id, $current_quantity, $cost_price, $user_id);
            $stmt_trans->execute();
            $stmt_trans->close();
        }
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
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $unit = $_POST['unit'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $reorder_level = $_POST['reorder_level'];
    $status = $_POST['status'];

    $sql = "UPDATE items SET sku = ?, name = ?, category_id = ?, supplier_id = ?, unit = ?, cost_price = ?, selling_price = ?, reorder_level = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiisddisi", $sku, $name, $category_id, $supplier_id, $unit, $cost_price, $selling_price, $reorder_level, $status, $item_id);

    if ($stmt->execute()) {
        header('Location: ../items.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

function archive_item() {
    global $conn;

    $item_id = $_GET['id'];

    $sql = "UPDATE items SET status = 'inactive' WHERE id = ?";
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

function export_movement_report() {
    global $conn;

    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    $sql_movement = "SELECT transactions.*, items.name as item_name, users.name as user_name FROM transactions
                     LEFT JOIN items ON transactions.item_id = items.id
                     LEFT JOIN users ON transactions.user_id = users.id WHERE 1=1";

    if (!empty($_GET['start_date'])) {
        $sql_movement .= " AND DATE(transactions.timestamp) >= '" . $_GET['start_date'] . "'";
    }
    if (!empty($_GET['end_date'])) {
        $sql_movement .= " AND DATE(transactions.timestamp) <= '" . $_GET['end_date'] . "'";
    }
    if (!empty($_GET['item_id'])) {
        $sql_movement .= " AND transactions.item_id = " . $_GET['item_id'];
    }
    if (!empty($_GET['user_id'])) {
        $sql_movement .= " AND transactions.user_id = " . $_GET['user_id'];
    }
    if (!empty($_GET['type'])) {
        $sql_movement .= " AND transactions.type = '" . $_GET['type'] . "'";
    }

    $sql_movement .= " ORDER BY transactions.timestamp DESC";
    $result_movement = $conn->query($sql_movement);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=movement_report.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Timestamp', 'Type', 'Item', 'Quantity', 'Unit Price', 'User', 'Customer'));

    while ($row = $result_movement->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
