<?php
include 'DBConnection.php';
include 'auth.php';

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Query the database to check if the username exists
    $query = "SELECT COUNT(*) as count FROM users WHERE username = '$username'";
    $result = $connection->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        $count = $row['count'];

        // Return a JSON response indicating the availability status
        if ($count == 0) {
            echo json_encode(array('available' => true));
        } else {
            echo json_encode(array('available' => false));
        }
    } else {
        // Handle database error
        echo json_encode(array('error' => 'Database error occurred.'));
    }
} else {
    // Handle missing username parameter
    echo json_encode(array('error' => 'Username parameter is missing.'));
}
?>