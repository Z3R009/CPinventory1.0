<?php
include 'DBConnection.php';
include 'role_admin.php';

$host = 'localhost';
$user = 'root';
$password = ''; // Database password (if any)
$database = 'cpinventory'; // Replace with your database name
$backupFile = 'backups/' . $database . '_backup_' . date('Y-m-d_H-i-s') . '.sql'; // Define backup file location

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check database connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if the form submitted the backup action
if (isset($_POST['backup'])) {
    // Use the full path to mysqldump
    $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" --host=$host --user=$user --password=$password $database > $backupFile 2>&1";
    exec($command, $output, $return_var); // Execute the command

    // Check the return status and output for errors
    if ($return_var == 0) {
        // Successful backup - output the script for confirmation
        echo "<script>
                alert('Database Backup Successfully! File saved to: $backupFile');
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "'; // Redirect back to the referring page
              </script>";

        // Prepare to log the backup action
        $currentDateTime = date("Y-m-d H:i:s");
        $details = "Database Backup Created: $backupFile";

        // Check if user ID is set in session
        if (!isset($_SESSION['user_id'])) {
            die("User ID is not set in session.");
        }
        $action_user_id = $_SESSION['user_id']; // Get the user ID from the session

        // Insert the backup action into the logs
        $insertLogSql = "INSERT INTO logs (action_user_id, updated_user_id, details, created_at) VALUES (?, ?, ?, ?)";
        if ($stmt = $connection->prepare($insertLogSql)) {
            $stmt->bind_param("ssss", $action_user_id, $action_user_id, $details, $currentDateTime);

            // Execute the statement
            if (!$stmt->execute()) {
                error_log("Log insertion failed: " . $stmt->error);
                echo "Failed to log backup action: " . $stmt->error; // Debug output for troubleshooting
            }

            $stmt->close();
        } else {
            // Handle log insertion preparation error
            error_log("Failed to prepare log insertion: " . $connection->error);
            echo "Failed to prepare log insertion: " . $connection->error; // Debug output for troubleshooting
        }

    } else {
        // Show detailed error message for backup failure
        echo "Backup failed! Error: " . implode("\n", $output);
    }
}
?>