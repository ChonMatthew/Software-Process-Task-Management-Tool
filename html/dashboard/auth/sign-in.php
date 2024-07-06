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

  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $password = $_POST['password'];
  $remember_me = isset($_POST['remember_me']);

  // Check if email exists
  $sql = "SELECT id, password FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found"]);
    exit();
  }

  $stmt->bind_result($user_id, $hashed_password);
  $stmt->fetch();

  // Verify password
  if (!password_verify($password, $hashed_password)) {
    echo json_encode(["status" => "error", "message" => "Incorrect password"]);
    exit();
  }

  // Set session variables
  $_SESSION['user_id'] = $user_id;
  $_SESSION['email'] = $email;

  // Set remember me cookie
  if ($remember_me) {
    setcookie('email', $email, time() + (86400 * 30), "/"); // 30 days
  } else {
    setcookie('email', '', time() - 3600, "/"); // Clear the cookie
  }

  echo json_encode(["status" => "success"]);

?>
