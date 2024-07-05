<?php

// Connect to the database (replace with your credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tasktool";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Escape user input to prevent SQL injection
$email = mysqli_real_escape_string($conn, $_POST['email']);
$full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
$password = $_POST['password']; // Don't escape password for hashing
$confirm_password = $_POST['confirm-password'];

// Validate user input (optional, add checks for email format, username length, etc.)
if (empty($email) || empty($full_name) || empty($username) || empty($password) || empty($confirm_password)) {
  die("Please fill in all required fields.");
}

// Check password confirmation
if ($password !== $confirm_password) {
  die("Passwords do not match. Please re-enter your password.");
}

// Hash the password securely using password_hash()
$password_hash = password_hash($password, PASSWORD_BCRYPT);  // Use a strong hashing algorithm

// Check if users table exists (optional, but recommended)
$check_table_sql = "SHOW TABLES LIKE 'users'";
$result = $conn->query($check_table_sql);

if ($result->num_rows == 0) {
  // Create users table if it doesn't exist
  $create_table_sql = "CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20)
  )";
  if ($conn->query($create_table_sql) === FALSE) {
    echo "Error creating users table: " . $conn->error;
    exit;
  }
}

// Prepare SQL statement (prevents SQL injection)
$sql = "INSERT INTO users (email, full_name, username, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Bind parameters to the statement
$stmt->bind_param("ssss", $email, $full_name, $username, $password_hash);

if ($stmt->execute()) {
  echo "Registration successful! You can now log in.";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();

?>
