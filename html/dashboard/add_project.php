<?php
session_start();
require 'db_connect.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('User not logged in');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $project_name = $_POST['projectName'];
    $description = $_POST['description'];

    // Insert the new project into the database
    $sql = "INSERT INTO projects (user_id, project_name, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $project_name, $description);

    if ($stmt->execute()) {
        echo "Project added successfully.";
    } else {
        echo "Error adding project: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
