<?php
include 'DBConnection.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the full name of the user
    $sql = "SELECT fullname FROM users WHERE user_id = $user_id";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    $fullname = $row['fullname'];

    // Check if user confirmed the deletion
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $sql = "DELETE FROM users WHERE user_id = $user_id";
        if ($connection->query($sql)) {
            // Log the action
            $action_user_id = $_SESSION['user_id'];
            $details = "Account Deleted: $fullname";
            insertLog($action_user_id, $user_id, $details);

            header('Location: manage_users.php');
        } else {
            // Handle error
        }
        exit();
    } else {
        echo "<script>
                if(confirm('Are you sure you want to delete this user?')) {
                    window.location.href = 'delete_user.php?user_id=$user_id&confirm=yes';
                } else {
                    window.location.href = 'manage_users.php';
                }
              </script>";
    }
}

function insertLog($action_user_id, $updated_user_id, $details)
{
    global $connection;

    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) 
            VALUES ('$action_user_id', '$updated_user_id', '$details')";

    $connection->query($sql);
}
?>