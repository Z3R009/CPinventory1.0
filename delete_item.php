<?php
include 'DBConnection.php';

if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    $sql = "SELECT item_name FROM items WHERE item_id = $item_id";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    $item_name = $row['item_name'];

    // Check if user confirmed the deletion
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $sql = "DELETE FROM items where item_id=$item_id";
        $result = mysqli_query($connection, $sql);

        if ($connection->query($sql)) {
            // Log the action
            $action_user_id = $_SESSION['user_id'];
            $details = "Item Deleted: $item_name";
            insertLog($action_user_id, $user_id, $details);

            header('Location: manage_items.php');
        } else {
            // Handle error
        }
        exit();
    } else {
        echo "<script>
                if(confirm('Are you sure you want to delete this item?')) {
                    window.location.href = 'delete_item.php?item_id=$item_id&confirm=yes';
                } else {
                    window.location.href = 'manage_items.php';
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