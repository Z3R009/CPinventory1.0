<?php
// check_typing_speed.php

// Assuming you have included DBconnection.php for database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receive typing speed data from client-side
    $typingSpeed = json_decode(file_get_contents('php://input'), true)['typingSpeed'];

    // Define threshold typing speed (1 character per second)
    $thresholdTypingSpeed = 1; // Characters per second

    // Perform validation
    if ($typingSpeed > $thresholdTypingSpeed) {
        // Typing speed exceeds threshold, consider it as fast typing
        echo json_encode(['status' => 'error', 'message' => 'Typing too fast. Please type slower.']);
        exit;
    }

    // Typing speed is within acceptable range
    echo json_encode(['status' => 'success', 'message' => 'Typing speed is acceptable.']);
}
?>