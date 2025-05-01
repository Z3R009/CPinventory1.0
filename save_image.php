<?php
include 'DBConnection.php';

// Check if image data is received
if (isset($_POST['image']) && isset($_POST['username'])) {
    $imageData = $_POST['image'];
    $username = $_POST['username'];

    // Extract the base64 data (remove the prefix)
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    // Prepare the image data for saving
    $imageName = $username . '_photo_' . time() . '.png';

    // Specify the directory where images will be saved (this can be adjusted as needed)
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Save the image to the specified directory
    $imagePath = $uploadDir . $imageName;
    file_put_contents($imagePath, $imageData);

    // Insert the image path into the database
    $sql = "INSERT INTO failed_attempts (username, image_path) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ss", $username, $imagePath);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Image saved successfully.";
    } else {
        echo "Error saving the image.";
    }

    $stmt->close();
    $connection->close();
} else {
    echo "No image or username data received.";
}
?>