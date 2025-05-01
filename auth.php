<?php

// $_SESSION['fullname'] = $fullname;

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check for inactivity timeout
$timeout = 60000000000000; // 10 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Log the timeout activity
    $currentDateTime = date("Y-m-d H:i:s"); // Get the current date and time
    $action_user_id = $_SESSION['user_id']; // Retrieve user_id from session
    $details = "User session timed out"; // Message for session timeout activity

    $insertLogSql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($insertLogSql);

    if ($stmt) {
        $stmt->bind_param("sss", $action_user_id, $action_user_id, $details); // Bind variables to the prepared statement
        $stmt->execute();
        $stmt->close();
    }

    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time(); // update last activity time stamp

// Display user information
$fullName = $_SESSION['fullname'];
$userName = $_SESSION['username'];
$userType = $_SESSION['user_type'];
$userFullname = isset($_SESSION['user_fullname']) ? $_SESSION['user_fullname'] : '';

// Fetch user_id based on the username and user_type
$user_id = null; // Initialize with a default value or handle it as needed

// Fetch user_id from the users table based on the logged-in user
$sql = "SELECT user_id FROM users WHERE username = ?"; // Assuming 'user_type' is not a column in the 'users' table
$stmt = $connection->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $stmt->bind_result($user_id);

    // Fetch the result
    if ($stmt->fetch()) {
        $_SESSION['id'] = $user_id; // Store user_id in the session for later use
    }

    $stmt->close();
} else {
    // Handle the error, e.g., display an error message or log the error
    echo "Error: " . $connection->error;
}
?>