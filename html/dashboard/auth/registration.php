<?php
session_start();

// Connect to the database
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

// Validate and sanitize input
function validate_input($data) {
  return htmlspecialchars(stripslashes(trim($data)));
}

$full_name = validate_input($_POST['full_name']);
$username = validate_input($_POST['username']);
$email = validate_input($_POST['email']);
$phone_number = validate_input($_POST['phone_number']);
$password = $_POST['password']; // Password should not be escaped for hashing
$confirm_password = $_POST['confirm_password'];

// Check if passwords match
if ($password !== $confirm_password) {
  echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
  exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(["status" => "error", "message" => "Invalid email format (eg: user@email.com)"]);
  exit();
}

// Validate phone number
if (!preg_match("/^[0-9]{10}$/", $phone_number)) {
  echo json_encode(["status" => "error", "message" => "Invalid phone number format (minimum 10 digits)"]);
  exit();
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
    echo json_encode(["status" => "error", "message" => "Error creating users table: " . $conn->error]);
    exit();
  }
}

// Check if email already exists
$email_check_sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($email_check_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  echo json_encode(["status" => "error", "message" => "Email already exists"]);
  exit();
}

// Check if username already exists
$username_check_sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($username_check_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  echo json_encode(["status" => "error", "message" => "Username already exists"]);
  exit();
}

// Insert user data into the database
$insert_sql = "INSERT INTO users (email, full_name, username, password, phone_number) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("sssss", $email, $full_name, $username, $password_hash, $phone_number);

if ($stmt->execute()) {
  echo json_encode(["status" => "success", "message" => "Registration successful"]);
} else {
  echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
