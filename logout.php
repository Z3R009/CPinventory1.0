<?php
include 'DBconnection.php'; // Assuming this file initializes $connection

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user_id from the session
$user_id = $_SESSION['user_id'];

$currentDateTime = date("Y-m-d H:i:s"); // Get the current date and time
$action_user_id = $_SESSION['user_id']; // Retrieve full name from session
$details = "User logged out"; // Message for logout activity

$insertLogSql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
$stmt = $connection->prepare($insertLogSql);

if ($stmt) {
    $stmt->bind_param("sss", $action_user_id, $user_id, $details); // Bind variables to the prepared statement
    $stmt->execute();
    $stmt->close();

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php");
    exit();
} else {
    echo "Error inserting logout log: " . $connection->error;
}
?>