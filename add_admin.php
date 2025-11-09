<?php
// Include the database connection
require_once 'php/db_connect.php';

// Admin user data
$name = 'Yash Naik Gaonkar';
$email = 'yash1506@gamai.com';
$password = 'Yng@1506';
$role = 'admin';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare and execute the SQL statement
$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo "Admin user 'Yash Naik Gaonkar' created successfully.";
} else {
    echo "Error creating admin user: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Delete this script after execution
unlink(__FILE__);
?>
