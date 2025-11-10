<?php
// Include the configuration file
@include 'config.php';

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
  echo "Database created successfully or already exists<br>";
} else {
  echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db(DB_NAME);

// Read the SQL file
$sql = file_get_contents('../database.sql');

if ($conn->multi_query($sql)) {
  echo "Tables created successfully<br>";
  // To avoid errors, we need to advance to the next result set
  while ($conn->next_result()) {;}
} else {
  echo "Error creating tables: " . $conn->error . "<br>";
}

// Check if admin user exists
$sql = "SELECT id FROM users WHERE email = 'admin@example.com'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  // Insert admin user
  $name = 'Admin';
  $role = 'admin';
  $email = 'admin@example.com';
  $password = password_hash('password', PASSWORD_DEFAULT);

  $sql = "INSERT INTO users (name, role, email, password) VALUES ('$name', '$role', '$email', '$password')";

  if ($conn->query($sql) === TRUE) {
    echo "Admin user created successfully<br>";
  } else {
    echo "Error creating admin user: " . $conn->error . "<br>";
  }
} else {
    echo "Admin user already exists<br>";
}

$conn->close();

// Redirect to the login page
header('Location: ../index.php');
exit;
?>